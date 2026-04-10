<?php

namespace App\Services;

use App\Models\RefreshToken;
use App\Models\User;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use function Illuminate\Support\now;

class AuthService
{


    public function registerService($data)
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
            'access_token' => $accessToken,
            'refresh_token' => $refresh_token
        ];
    }


    public function loginService($data)
    {
        $user = User::where('email', $data['email'])->first();
        if (!$user) {
            throw new AuthorizationException('wrong credentials');
        }
        if (!Hash::check($data['password'], $user->password)) {
            throw new AuthorizationException('wrong credentials');
        }
        $accessToken = Auth::login($user);
        $refreshToken = $this->generateRefreshToken($user);
        return [
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken
        ];
    }
    public function logout()
    {
        $user = Auth::user();
        RefreshToken::where('user_id', $user->id)->delete();
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'logged out successfully'
        ], 200);
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
        $checked = RefreshToken::where('refresh_token', $hashed)->first();
        if (!$checked) {
            throw new \Illuminate\Auth\AuthenticationException('Unauthenticated');
        }
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
            'access_token' => $newAccessToken,
            'refresh_token' => $newRefreshToken ?? $plainToken
        ];
    }
}
