<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    private const ACCESS_TOKEN_TTL_MINUTES = 60;
    private const REFRESH_TOKEN_TTL_DAYS = 7;

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $accessToken = $this->createAccessToken($user);

        return response()->json([
            'user' => $user,
            'token' => $accessToken,
            'access_token' => $accessToken,
            'refresh_token' => $this->createRefreshToken($user),
            'expires_in' => self::ACCESS_TOKEN_TTL_MINUTES * 60,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $this->revokeUserTokensByType($user, 'access');

        $accessToken = $this->createAccessToken($user);

        return response()->json([
            'user' => $user,
            'token' => $accessToken,
            'access_token' => $accessToken,
            'refresh_token' => $this->createRefreshToken($user),
            'expires_in' => self::ACCESS_TOKEN_TTL_MINUTES * 60,
        ]);
    }

    public function refresh(Request $request)
    {
        $bearer = $request->bearerToken();
        if (! $bearer) {
            return response()->json(['message' => 'Refresh token required'], 401);
        }

        $token = PersonalAccessToken::findToken($bearer);

        if (! $token || $token->type !== 'refresh' || $token->expires_at?->isPast()) {
            return response()->json(['message' => 'Invalid or expired refresh token'], 401);
        }

        $user = $token->tokenable;

        $token->delete();
        $this->revokeUserTokensByType($user, 'access');

        $accessToken = $this->createAccessToken($user);

        return response()->json([
            'token' => $accessToken,
            'access_token' => $accessToken,
            'refresh_token' => $this->createRefreshToken($user),
            'expires_in' => self::ACCESS_TOKEN_TTL_MINUTES * 60,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out']);
    }

    private function createAccessToken(User $user): string
    {
        return $this->createToken($user, 'access', now()->addMinutes(self::ACCESS_TOKEN_TTL_MINUTES));
    }

    private function createRefreshToken(User $user): string
    {
        return $this->createToken($user, 'refresh', now()->addDays(self::REFRESH_TOKEN_TTL_DAYS));
    }

    private function createToken(User $user, string $type, Carbon $expiresAt): string
    {
        $newToken = $user->createToken($type, ['*'], $expiresAt);
        $newToken->accessToken->type = $type;
        $newToken->accessToken->save();

        return $newToken->plainTextToken;
    }

    private function revokeUserTokensByType(User $user, string $type): void
    {
        $user->tokens()->where('type', $type)->delete();
    }
}
