<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApiKeyController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->tokens()
            ->where('type', 'api-key')
            ->select(['id', 'name', 'type', 'last_used_at', 'expires_at', 'created_at'])
            ->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'expires_at' => 'nullable|date',
            'expires_in_days' => 'nullable|integer|min:1|max:365',
        ]);

        $expiresAt = ($validated['expires_at'] ?? null)
            ? Carbon::parse($validated['expires_at'])
            : (isset($validated['expires_in_days'])
                ? now()->addDays($validated['expires_in_days'])
                : null);

        $newToken = $request->user()->createToken($validated['name'], ['*'], $expiresAt);
        $newToken->accessToken->type = 'api-key';
        $newToken->accessToken->save();

        return response()->json([
            'api_key' => $newToken->plainTextToken,
            'name' => $validated['name'],
            'expires_at' => $expiresAt?->toISOString(),
        ], 201);
    }

    public function destroy(Request $request, $tokenId)
    {
        $deleted = $request->user()->tokens()
            ->where('type', 'api-key')
            ->where('id', $tokenId)
            ->delete();

        if (! $deleted) {
            return response()->json(['message' => 'API key not found'], 404);
        }

        return response()->noContent();
    }
}
