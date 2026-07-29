<?php

namespace App\Http\Controllers;

use App\Jobs\FetchArchiveBody;
use App\Models\Archive;
use App\Models\Service;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArchiveController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->archives()->with('tags')->latest('recorded_at');

        if ($request->filled('archive_type')) {
            $query->where('archive_type', $request->input('archive_type'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%")
                  ->orWhere('memo', 'like', "%{$search}%");
            });
        }

        return $query->paginate($request->input('per_page', 30));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'archive_type' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:2048',
            'body' => 'nullable|string',
            'memo' => 'nullable|string',
            'image_path' => 'nullable|string|max:2048',
            'source_data' => 'nullable|array',
            'recorded_at' => 'nullable|date',
            'visited_at' => 'nullable|date',
        ]);

        $recordedAt = Carbon::parse($validated['recorded_at'] ?? now());

        if (! empty($validated['url'])) {
            $existing = $request->user()->archives()
                ->where('url', $validated['url'])
                ->whereBetween('recorded_at', [
                    $recordedAt->copy()->subMinutes(5),
                    $recordedAt->copy()->addMinutes(5),
                ])
                ->first();

            if ($existing) {
                return response()->json($existing, 200);
            }
        }

        $serviceSlug = $request->header('X-Service') ?? $request->input('service_slug', 'iias-web');
        $service = Service::where('slug', $serviceSlug)->first();

        $archive = $request->user()->archives()->create([
            ...$validated,
            'service_id' => $service?->id,
            'recorded_at' => $recordedAt,
        ]);

        $this->attachTagsFromTitle($request->user(), $archive, $validated['title'] ?? '');

        if (($validated['archive_type'] ?? null) === 'history' && ! empty($validated['url'])) {
            FetchArchiveBody::dispatchAfterResponse($archive);
        }

        return response()->json($archive->load('tags'), 201);
    }

    public function show(Request $request, Archive $archive)
    {
        $this->authorizeAccess($request, $archive);
        $archive->load('tags', 'metadata');
        return $archive;
    }

    public function update(Request $request, Archive $archive)
    {
        $this->authorizeAccess($request, $archive);

        $validated = $request->validate([
            'archive_type' => 'sometimes|string|max:255',
            'title' => 'sometimes|nullable|string|max:255',
            'url' => 'sometimes|nullable|string|max:2048',
            'body' => 'sometimes|nullable|string',
            'memo' => 'sometimes|nullable|string',
            'image_path' => 'sometimes|nullable|string|max:2048',
            'source_data' => 'sometimes|nullable|array',
            'recorded_at' => 'sometimes|nullable|date',
            'visited_at' => 'sometimes|nullable|date',
        ]);

        $archive->update($validated);
        return $archive;
    }

    public function destroy(Request $request, Archive $archive)
    {
        $this->authorizeAccess($request, $archive);
        $archive->delete();
        return response()->noContent();
    }

    public function fetchBody(Request $request, Archive $archive)
    {
        $this->authorizeAccess($request, $archive);
        FetchArchiveBody::dispatchSync($archive);
        $archive->refresh();

        return response()->json($archive->load('tags'));
    }

    private function authorizeAccess(Request $request, Archive $archive)
    {
        if ($archive->user_id !== $request->user()->id) {
            abort(403);
        }
    }

    private function attachTagsFromTitle($user, Archive $archive, string $title)
    {
        if (empty($title)) {
            return;
        }

        preg_match_all('/[a-zA-Z0-9]{3,}/', strtolower($title), $matches);
        $keywords = array_unique($matches[0] ?? []);

        if (empty($keywords)) {
            return;
        }

        $tagIds = [];
        foreach ($keywords as $keyword) {
            $name = strtolower($keyword);
            $slug = Str::slug($name);
            if (empty($slug)) {
                continue;
            }
            $tag = Tag::firstOrCreate(
                ['user_id' => $user->id, 'slug' => $slug],
                ['name' => $name, 'color' => null]
            );
            $tagIds[] = $tag->id;
        }

        if (! empty($tagIds)) {
            $archive->tags()->syncWithoutDetaching($tagIds);
        }
    }
}
