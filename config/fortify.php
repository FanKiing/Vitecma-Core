<?php

use Laravel\Fortify\Features;

return [
    'guard' => 'web',
    'passwords' => 'users',
    'username' => 'username',
    'email' => 'email',
    'lowercase_usernames' => true,
    'home' => '/dashboard',
    'prefix' => '',
    'domain' => null,
    'middleware' => ['web', 'throttle:auth-forms'],
    'auth_middleware' => 'auth',
    'views' => true,
    'limiters' => ['login' => 'login'],
    'paths' => [
        'password' => ['confirm' => '/confirm-password'],
        'user-password' => ['update' => '/password'],
        'verification' => ['notice' => '/verify-email'],
    ],
    'redirects' => ['logout' => '/', 'email-verification' => '/home'],
    'features' => [
        Features::registration(),
        Features::resetPasswords(),
        Features::emailVerification(),
        Features::updatePasswords(),
    ],
];
