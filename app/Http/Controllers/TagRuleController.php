<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TagRuleController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->tagRules()->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'keyword' => 'required|string|max:255',
            'type' => 'required|in:include,exclude,alias',
            'target_tag' => 'nullable|string|max:255',
        ]);

        $rule = $request->user()->tagRules()->create($validated);

        return response()->json($rule, 201);
    }

    public function destroy(Request $request, $ruleId)
    {
        $deleted = $request->user()->tagRules()->where('id', $ruleId)->delete();

        if (! $deleted) {
            return response()->json(['message' => 'Tag rule not found'], 404);
        }

        return response()->noContent();
    }
}
