<?php

namespace App\Http\Controllers;

use App\Events\MaintenanceModeChanged;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MaintenanceController extends Controller
{
    private const CACHE_KEY_ENABLED = 'maintenance_mode_enabled';
    private const CACHE_KEY_MESSAGE = 'maintenance_mode_message';

    private function checkAdmin(): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized');
        }
    }

    /**
     * État actuel du mode maintenance (utilisé par la vue et en secours par le JS).
     */
    public static function isEnabled(): bool
    {
        return (bool) Cache::get(self::CACHE_KEY_ENABLED, false);
    }

    public static function getMessage(): ?string
    {
        return Cache::get(self::CACHE_KEY_MESSAGE);
    }

    /**
     * Active ou désactive le mode maintenance pour les utilisateurs non-admin
     * (écran) et diffuse le changement en temps réel via Reverb.
     */
    public function toggle(Request $request): JsonResponse
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'enabled' => 'required|boolean',
            'message' => 'nullable|string|max:255',
        ]);

        Cache::forever(self::CACHE_KEY_ENABLED, $validated['enabled']);
        Cache::forever(self::CACHE_KEY_MESSAGE, $validated['message'] ?? null);

        broadcast(new MaintenanceModeChanged($validated['enabled'], $validated['message'] ?? null))->toOthers();

        return response()->json([
            'success' => true,
            'enabled' => $validated['enabled'],
            'message' => $validated['message'] ?? null,
        ]);
    }
}