<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FortifySecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_login_uses_fortify_and_accepts_existing_username_only_accounts(): void
    {
        $this->assertStringStartsWith('Laravel\\Fortify\\', app('router')->getRoutes()->getByName('login.store')->getActionName());
        $user = User::factory()->create(['username' => 'legacy', 'email' => null]);

        $this->postJson('/login', ['username' => ' LEGACY ', 'password' => 'password'])->assertOk();
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_regenerates_the_session_and_remember_me_is_supported(): void
    {
        $user = User::factory()->create(['username' => 'admin']);
        $this->get('/login')->assertOk();
        $before = session()->getId();

        $response = $this->post('/login', ['username' => 'admin', 'password' => 'password', 'remember' => '1']);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($before, session()->getId());
        $response->assertCookie(auth()->guard()->getRecallerName());
    }

    public function test_invalid_logins_do_not_disclose_whether_an_account_exists(): void
    {
        User::factory()->create(['username' => 'existing']);
        $known = $this->postJson('/login', ['username' => 'existing', 'password' => 'incorrect'])->assertUnprocessable();
        $unknown = $this->postJson('/login', ['username' => 'missing', 'password' => 'incorrect'])->assertUnprocessable();

        $this->assertSame($known->json('errors'), $unknown->json('errors'));
        $this->assertGuest();
    }

    public function test_login_is_throttled_after_five_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/login', ['username' => 'missing', 'password' => 'incorrect'])->assertUnprocessable();
        }

        $this->postJson('/login', ['username' => 'missing', 'password' => 'incorrect'])->assertTooManyRequests()->assertHeader('Retry-After');
        $this->assertGuest();
    }

    public function test_rotating_usernames_does_not_bypass_the_ip_limit(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $this->postJson('/login', ['username' => 'missing'.$i, 'password' => 'incorrect'])->assertUnprocessable();
        }

        $this->postJson('/login', ['username' => 'another', 'password' => 'incorrect'])->assertTooManyRequests();
    }

    public function test_malformed_and_oversized_credentials_are_rejected(): void
    {
        $this->postJson('/login', ['username' => ['admin'], 'password' => 'password'])->assertUnprocessable()->assertJsonValidationErrors('username');
        $this->postJson('/login', ['username' => str_repeat('a', 65), 'password' => 'password'])->assertUnprocessable()->assertJsonValidationErrors('username');
        $this->postJson('/login', ['username' => 'admin', 'password' => str_repeat('a', 4097)])->assertUnprocessable()->assertJsonValidationErrors('password');
    }

    public function test_registration_cannot_grant_administrator_privileges(): void
    {
        $this->post('/register', [
            'name' => 'Operator', 'username' => 'operator', 'email' => 'operator@example.test',
            'password' => 'valid-password', 'password_confirmation' => 'valid-password',
            'role' => 'admin', 'is_admin' => true,
        ])->assertRedirect('/dashboard');

        $user = User::where('username', 'operator')->firstOrFail();
        $this->assertSame('user', $user->role);
        $this->assertFalse((bool) $user->is_admin);
        $this->assertTrue(Hash::check('valid-password', $user->password));
    }

    public function test_duplicate_username_and_email_are_rejected(): void
    {
        $user = User::factory()->create();
        $this->postJson('/register', [
            'name' => 'Duplicate', 'username' => $user->username, 'email' => $user->email,
            'password' => 'valid-password', 'password_confirmation' => 'valid-password',
        ])->assertUnprocessable()->assertJsonValidationErrors(['username', 'email']);
    }

    public function test_standard_user_cannot_perform_administrator_operations(): void
    {
        $this->actingAs(User::factory()->create());
        $this->postJson('/inspections', [])->assertForbidden();
        $this->deleteJson('/inspections/1')->assertForbidden();
        $this->postJson('/maintenance/toggle')->assertForbidden();
    }

    public function test_invalid_password_reset_token_does_not_change_password(): void
    {
        $user = User::factory()->create();
        $this->post('/reset-password', [
            'email' => $user->email, 'token' => 'invalid-token',
            'password' => 'changed-password', 'password_confirmation' => 'changed-password',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_seeding_is_repeatable_and_preserves_existing_accounts(): void
    {
        config(['seeding.admin_password' => 'test-only-admin-password', 'seeding.screen_password' => 'test-only-screen-password']);
        $existing = User::factory()->create(['username' => 'admin']);
        $hash = $existing->password;
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame($hash, $existing->fresh()->password);
        $this->assertSame('user', $existing->fresh()->role);
        $this->assertSame(1, User::where('username', 'screen')->count());
        $this->assertSame(2, User::count());
    }

    public function test_seeding_without_configured_passwords_does_not_create_default_accounts(): void
    {
        config(['seeding.admin_password' => null, 'seeding.screen_password' => null]);
        $this->seed(DatabaseSeeder::class);
        $this->assertDatabaseCount('users', 0);
    }
}
