<?php

namespace App\Services;

use App\Models\RefreshToken;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use function Illuminate\Support\now;

class AuthService
{


    public function Register($data)
    {
        $createdUser = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);

        $accessToken = Auth::login($createdUser);

        $refresh_token = $this->generateRefreshToken($createdUser);

        return [
            'user' => $createdUser,
            'accessToken' => $accessToken,
            'refresh_token' => $refresh_token
        ];
    }
    public function generateRefreshToken(User $user)
    {
        RefreshToken::where('user_id', $user->id)->delete();

        $plainToken = Str::random(64);

        RefreshToken::create([
            'user_id' => $user->id,
            'refresh_token' => hash('sha256', $plainToken),
            'expires_at' => now()->addDays(30)
        ]);

        return $plainToken;
    }

    public function refreshAccessToken(string $plainToken)
    {
        $hashed = hash('sha256', $plainToken);
        $checked = RefreshToken::where('refresh_token', $hashed)->firstOrFail();
        if ($checked->expires_at->isPast()) {
            $checked->delete();
            throw new Exception('Refresh token expired');
        }
        if ($checked->expires_at < now()->addDays(7)) {
            $newRefreshToken = $this->generateRefreshToken($checked->user);
        }
        $newAccessToken = Auth::login($checked->user);

        return [
            'user' => $checked->user,
            'newAccessToken' => $newAccessToken,
            'newRefreshToken' => $newRefreshToken ?? $plainToken
        ];
    }
    
}
