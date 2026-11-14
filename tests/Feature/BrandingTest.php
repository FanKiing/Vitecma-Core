<?php

namespace Tests\Feature;

use App\Models\BrandingSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BrandingTest extends TestCase
{
    use RefreshDatabase;

    private function administrator(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_admin' => true]);
    }

    private function logo(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('logo.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+j4uoAAAAASUVORK5CYII='));
    }

    public function test_only_administrators_can_manage_branding(): void
    {
        $this->get('/settings/branding')->assertRedirect('/login');
        $this->put('/settings/branding', ['name' => 'Unauthorized'])->assertRedirect('/login');
        $this->actingAs(User::factory()->create());
        $this->get('/settings/branding')->assertForbidden();
        $this->put('/settings/branding', ['name' => 'Unauthorized'])->assertForbidden();
        $this->assertDatabaseCount('branding_settings', 0);
    }

    public function test_navbar_button_and_modal_are_available_only_to_administrators(): void
    {
        $this->actingAs(User::factory()->create())->get('/dashboard')->assertOk()->assertDontSee('id="branding-modal"', false);
        $this->actingAs($this->administrator());
        foreach (['/dashboard', '/profile', '/inspections/archive', '/inspections/trash'] as $path) {
            $this->get($path)->assertOk()->assertSee('id="branding-modal"', false)->assertSee('Nom et logo');
        }
    }

    public function test_administrator_can_change_name_and_it_is_visible_to_guests(): void
    {
        $this->actingAs($this->administrator())->from('/dashboard')->put('/settings/branding', ['name' => 'Centre Atlas'])->assertRedirect('/dashboard')->assertSessionHas('status', 'branding-updated');
        $this->assertDatabaseHas('branding_settings', ['id' => 1, 'name' => 'Centre Atlas']);
        $this->post('/logout');
        $this->get('/login')->assertOk()->assertSee('Centre Atlas');
    }

    public function test_uploaded_logo_is_served_publicly_without_a_storage_symlink(): void
    {
        Storage::fake('local');
        $this->actingAs($this->administrator())->put('/settings/branding', ['name' => 'Centre Atlas', 'logo' => $this->logo()])->assertSessionHasNoErrors();
        $path = BrandingSetting::findOrFail(1)->logo_path;
        Storage::disk('local')->assertExists($path);
        $this->post('/logout');
        $this->get('/branding/logo')->assertOk()->assertHeader('Content-Type', 'image/png')->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->get('/login')->assertSee('/branding/logo?v=', false);
    }

    public function test_invalid_images_and_oversized_uploads_are_rejected_without_changes(): void
    {
        Storage::fake('local');
        $this->actingAs($this->administrator());
        $this->put('/settings/branding', ['name' => 'Bad image', 'logo' => UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>')])->assertSessionHasErrorsIn('branding', 'logo');
        $this->put('/settings/branding', ['name' => 'Too big', 'logo' => $this->logo()->size(2049)])->assertSessionHasErrorsIn('branding', 'logo');
        $this->put('/settings/branding', ['name' => str_repeat('a', 81)])->assertSessionHasErrorsIn('branding', 'name');
        $this->assertDatabaseCount('branding_settings', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_replacement_and_removal_clean_up_old_logos(): void
    {
        Storage::fake('local');
        $this->actingAs($this->administrator());
        $this->put('/settings/branding', ['name' => 'Atlas', 'logo' => $this->logo()]);
        $first = BrandingSetting::findOrFail(1)->logo_path;
        $this->put('/settings/branding', ['name' => 'Atlas', 'logo' => $this->logo()]);
        $second = BrandingSetting::findOrFail(1)->logo_path;
        $this->assertNotSame($first, $second);
        Storage::disk('local')->assertMissing($first);
        Storage::disk('local')->assertExists($second);
        $this->put('/settings/branding', ['name' => 'Atlas', 'remove_logo' => '1'])->assertSessionHasNoErrors();
        $this->assertNull(BrandingSetting::findOrFail(1)->logo_path);
        Storage::disk('local')->assertMissing($second);
        $this->get('/branding/logo')->assertNotFound();
    }

    public function test_name_is_escaped_in_public_pages(): void
    {
        $name = '<script>alert(1)</script>';
        $this->actingAs($this->administrator())->put('/settings/branding', ['name' => $name]);
        $this->post('/logout');
        $this->get('/login')->assertSee($name)->assertDontSee($name, false);
    }
}
