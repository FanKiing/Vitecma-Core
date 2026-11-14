<?php

namespace App\Http\Requests\Auth;

class LoginRequest extends \Laravel\Fortify\Http\Requests\LoginRequest
{
    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:64'],
            'password' => ['required', 'string', 'max:4096'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }
}
