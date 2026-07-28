<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->settings()
            ->get(['setting_key', 'setting_value', 'value_type'])
            ->mapWithKeys(function ($setting) {
                return [$setting->setting_key => $this->castValue($setting->setting_value, $setting->value_type)];
            });
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string|max:255',
            'settings.*.value' => 'nullable',
            'settings.*.type' => 'nullable|string|in:string,integer,boolean,json',
        ]);

        foreach ($validated['settings'] as $item) {
            $type = $item['type'] ?? $this->detectType($item['value']);
            $value = is_array($item['value']) || is_object($item['value']) ? json_encode($item['value']) : (string) $item['value'];

            $request->user()->settings()->updateOrCreate(
                ['setting_key' => $item['key']],
                ['setting_value' => $value, 'value_type' => $type]
            );
        }

        return $this->index($request);
    }

    private function castValue(string $value, ?string $type)
    {
        return match ($type) {
            'integer' => (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    private function detectType(mixed $value): string
    {
        return match (true) {
            is_int($value) => 'integer',
            is_bool($value) => 'boolean',
            is_array($value), is_object($value) => 'json',
            default => 'string',
        };
    }
}
