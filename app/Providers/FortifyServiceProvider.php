<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\LoginRequest;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LoginRequest::class, \App\Http\Requests\Auth\LoginRequest::class);
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);

        Fortify::loginView(fn () => view('auth.login'));
        Fortify::registerView(fn () => view('auth.register'));
        Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
        Fortify::resetPasswordView(fn (Request $request) => view('auth.reset-password', compact('request')));
        Fortify::confirmPasswordView(fn () => view('auth.confirm-password'));
        Fortify::verifyEmailView(fn () => view('auth.verify-email'));

        RateLimiter::for('auth-forms', function (Request $request) {
            if ($request->isMethod('POST') && in_array($request->route()->getName(), [
                'register.store', 'password.email', 'password.update', 'password.confirm.store',
            ], true)) {
                return Limit::perMinute(5)->by($request->route()->getName().'|'.$request->ip());
            }

            return Limit::none();
        });

        RateLimiter::for('login', function (Request $request) {
            $input = $request->input('username');
            $username = is_string($input) ? Str::lower(trim($input)) : '';

            return [
                Limit::perMinute(5)->by('login-account:'.hash('sha256', $username.'|'.$request->ip())),
                Limit::perMinute(20)->by('login-ip:'.$request->ip()),
            ];
        });
    }
}
