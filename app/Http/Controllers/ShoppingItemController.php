<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ShoppingItem;
use Illuminate\Http\Request;

class ShoppingItemController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->shoppingItems();

        $status = $request->input('status', 'active');
        if (in_array($status, ['active', 'purchased', 'archived'])) {
            $query->where('status', $status);
        }

        return $query->orderBy('sort_order')->orderBy('id')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image_path' => 'nullable|string|max:2048',
            'memo' => 'nullable|string',
            'status' => 'nullable|string|in:active,purchased,archived',
            'sort_order' => 'nullable|integer',
        ]);

        $service = Service::where('slug', 'iias-web')->first();

        $item = $request->user()->shoppingItems()->create([
            ...$validated,
            'service_id' => $service?->id,
            'status' => $validated['status'] ?? 'active',
        ]);

        return response()->json($item, 201);
    }

    public function show(Request $request, ShoppingItem $shoppingItem)
    {
        $this->authorizeAccess($request, $shoppingItem);
        return $shoppingItem;
    }

    public function update(Request $request, ShoppingItem $shoppingItem)
    {
        $this->authorizeAccess($request, $shoppingItem);

        $validated = $request->validate([
            'name' => 'sometimes|nullable|string|max:255',
            'image_path' => 'sometimes|nullable|string|max:2048',
            'memo' => 'sometimes|nullable|string',
            'status' => 'sometimes|nullable|string|in:active,purchased,archived',
            'purchased_at' => 'sometimes|nullable|date',
            'archived_at' => 'sometimes|nullable|date',
            'sort_order' => 'sometimes|nullable|integer',
        ]);

        if (isset($validated['status'])) {
            $validated['purchased_at'] = $validated['status'] === 'purchased' ? now() : null;
            $validated['archived_at'] = $validated['status'] === 'archived' ? now() : null;
        }

        $shoppingItem->update($validated);
        return $shoppingItem;
    }

    public function destroy(Request $request, ShoppingItem $shoppingItem)
    {
        $this->authorizeAccess($request, $shoppingItem);
        $shoppingItem->delete();
        return response()->noContent();
    }

    public function restore(Request $request, $id)
    {
        $item = $request->user()->shoppingItems()->withTrashed()->findOrFail($id);
        $item->restore();
        $item->update(['status' => 'active', 'purchased_at' => null, 'archived_at' => null]);
        return $item;
    }

    private function authorizeAccess(Request $request, ShoppingItem $shoppingItem)
    {
        if ($shoppingItem->user_id !== $request->user()->id) {
            abort(403);
        }
    }
}
