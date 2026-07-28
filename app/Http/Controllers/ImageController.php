<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:10240',
        ]);

        $path = $request->file('image')->store('images', 'public');

        return response()->json([
            'path' => $path,
            'url' => config('app.url') . Storage::url($path),
        ], 201);
    }
}
