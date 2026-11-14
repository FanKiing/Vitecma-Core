<?php

namespace App\Http\Controllers;

use App\Events\DashboardChanged;
use App\Models\BrandingSetting;
use App\Support\Branding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class BrandingController extends Controller
{
    public function edit()
    {
        return to_route('inspections.index')->with('open-branding', true);
    }

    public function update(Request $request, Branding $branding)
    {
        $validated = $request->validateWithBag('branding', [
            'name' => ['required', 'string', 'max:80'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048', 'dimensions:max_width=2000,max_height=2000'],
            'remove_logo' => ['sometimes', 'boolean'],
        ]);

        $settings = BrandingSetting::find(1) ?? new BrandingSetting;
        $settings->id = 1;
        $previousLogo = $settings->logo_path;
        $newLogo = null;
        $settings->name = $validated['name'];

        if ($request->boolean('remove_logo')) {
            $settings->logo_path = null;
        }

        if ($request->hasFile('logo')) {
            $newLogo = $request->file('logo')->store('branding', 'local');
            if (! $newLogo) {
                throw ValidationException::withMessages(['logo' => 'Impossible d’enregistrer le logo. Réessayez.'])->errorBag('branding');
            }
            $settings->logo_path = $newLogo;
        }

        try {
            $settings->save();
        } catch (\Throwable $exception) {
            if ($newLogo) {
                Storage::disk('local')->delete($newLogo);
            }
            throw $exception;
        }

        if ($previousLogo && $previousLogo !== $settings->logo_path) {
            Storage::disk('local')->delete($previousLogo);
        }
        $branding->refresh();
        broadcast(new DashboardChanged)->toOthers();

        return redirect()->back(fallback: route('inspections.index'))->with('status', 'branding-updated');
    }

    public function logo()
    {
        $path = BrandingSetting::find(1)?->logo_path;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path), [
            'Cache-Control' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
