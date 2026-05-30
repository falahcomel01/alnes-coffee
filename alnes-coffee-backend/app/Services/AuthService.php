<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function login(string $email, string $password, string $deviceName = 'web'): array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw new AuthenticationException('Email atau password salah.');
        }

        if (!$user->is_active) {
            throw new AuthenticationException('Akun Anda tidak aktif. Hubungi administrator.');
        }

        $user->tokens()->where('name', $deviceName)->delete();

        $token = $user->createToken(
            name: $deviceName,
            abilities: $user->role->permissions()
        );

        return [
            'user'  => $user,
            'token' => $token->plainTextToken,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function logoutAll(User $user): void
    {
        $user->tokens()->delete();
    }
}