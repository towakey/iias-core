<?php

namespace App\Http\Controllers;

use App\Models\QrLoginCode;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class QrLoginController extends Controller
{
    private const ACCESS_TOKEN_TTL_MINUTES = 60;
    private const REFRESH_TOKEN_TTL_DAYS = 7;
    private const QR_TOKEN_EXPIRES_DAYS = 365;

    public function show(Request $request)
    {
        $code = $request->user()->qrLoginCode;

        return response()->json([
            'token' => $code?->token,
            'expires_at' => $code?->expires_at?->toISOString(),
            'last_used_at' => $code?->last_used_at?->toISOString(),
        ]);
    }

    public function generate(Request $request)
    {
        $user = $request->user();
        $user->qrLoginCode()?->delete();

        $token = $this->generateUniqueToken();
        $code = $user->qrLoginCode()->create([
            'token' => $token,
            'expires_at' => now()->addDays(self::QR_TOKEN_EXPIRES_DAYS),
        ]);

        return response()->json([
            'token' => $code->token,
            'expires_at' => $code->expires_at?->toISOString(),
        ]);
    }

    public function destroy(Request $request)
    {
        $request->user()->qrLoginCode()?->delete();

        return response()->noContent();
    }

    public function login(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $code = QrLoginCode::where('token', $request->input('token'))->first();

        if (! $code || ($code->expires_at && $code->expires_at->isPast())) {
            throw ValidationException::withMessages([
                'token' => ['The provided QR code is invalid or expired.'],
            ]);
        }

        $this->revokeUserTokensByType($code->user, 'access');

        $accessToken = $this->createAccessToken($code->user);

        $code->last_used_at = now();
        $code->save();

        return response()->json([
            'user' => $code->user,
            'token' => $accessToken,
            'access_token' => $accessToken,
            'refresh_token' => $this->createRefreshToken($code->user),
            'expires_in' => self::ACCESS_TOKEN_TTL_MINUTES * 60,
        ]);
    }

    private function generateUniqueToken(): string
    {
        do {
            $token = Str::random(64);
        } while (QrLoginCode::where('token', $token)->exists());

        return $token;
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
