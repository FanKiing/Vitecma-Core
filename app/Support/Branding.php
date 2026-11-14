<?php

namespace App\Support;

use App\Models\BrandingSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class Branding
{
    private ?array $data = null;

    public function data(): array
    {
        if ($this->data !== null) {
            return $this->data;
        }

        $settings = Schema::hasTable('branding_settings') ? BrandingSetting::find(1) : null;
        $hasLogo = $settings?->logo_path && Storage::disk('local')->exists($settings->logo_path);
        $logo = $hasLogo
            ? route('branding.logo', ['v' => basename($settings->logo_path)])
            : asset('images/logo.png');

        return $this->data = [
            'siteName' => $settings?->name ?? 'Vitecma',
            'siteLogoUrl' => $logo,
            'siteIconUrl' => $hasLogo ? $logo : asset('images/uplogo.png'),
            'hasCustomLogo' => (bool) $hasLogo,
        ];
    }

    public function refresh(): void
    {
        $this->data = null;
    }
}
