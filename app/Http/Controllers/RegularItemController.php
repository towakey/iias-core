<?php

namespace App\Http\Controllers;

use App\Models\RegularItem;
use Illuminate\Http\Request;

class RegularItemController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->regularItems()->orderBy('sort_order')->orderBy('id')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'nullable|integer|min:0',
            'memo' => 'nullable|string',
            'image_path' => 'nullable|string|max:2048',
            'sort_order' => 'nullable|integer',
        ]);

        $item = $request->user()->regularItems()->create($validated);
        return response()->json($item, 201);
    }

    public function show(Request $request, RegularItem $regularItem)
    {
        $this->authorizeAccess($request, $regularItem);
        return $regularItem;
    }

    public function update(Request $request, RegularItem $regularItem)
    {
        $this->authorizeAccess($request, $regularItem);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|nullable|integer|min:0',
            'memo' => 'sometimes|nullable|string',
            'image_path' => 'sometimes|nullable|string|max:2048',
            'sort_order' => 'sometimes|nullable|integer',
        ]);

        $regularItem->update($validated);
        return $regularItem;
    }

    public function destroy(Request $request, RegularItem $regularItem)
    {
        $this->authorizeAccess($request, $regularItem);
        $regularItem->delete();
        return response()->noContent();
    }

    public function addToShopping(Request $request, RegularItem $regularItem)
    {
        $this->authorizeAccess($request, $regularItem);

        $shoppingItem = $request->user()->shoppingItems()->create([
            'name' => $regularItem->name,
            'price' => $regularItem->price,
            'memo' => $regularItem->memo,
            'image_path' => $regularItem->image_path,
            'status' => 'active',
            'sort_order' => 0,
        ]);

        return response()->json($shoppingItem, 201);
    }

    private function authorizeAccess(Request $request, RegularItem $regularItem)
    {
        if ($regularItem->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
