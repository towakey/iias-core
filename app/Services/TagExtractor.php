<?php

namespace App\Services;

use App\Models\Archive;
use App\Models\Tag;
use Illuminate\Support\Str;

class TagExtractor
{
    private const DEFAULT_STOP_WORDS = [
        'http', 'https', 'www', 'com', 'net', 'org', 'co', 'jp', 'go', 'ne',
        'html', 'htm', 'php', 'asp', 'aspx', 'jsp', 'json', 'xml',
    ];

    public static function extract(Archive $archive): void
    {
        $user = $archive->user;
        if (! $user) {
            return;
        }

        $text = mb_strtolower(
            ($archive->title ?? '') . ' ' .
            ($archive->body ?? '') . ' ' .
            ($archive->url ?? '') . ' ' .
            ($archive->memo ?? '')
        );

        if (trim($text) === '') {
            return;
        }

        $rules = $user->tagRules;
        $exclude = self::DEFAULT_STOP_WORDS;
        $aliases = [];
        $includes = [];

        foreach ($rules as $rule) {
            $keyword = mb_strtolower($rule->keyword);
            if ($rule->type === 'exclude') {
                $exclude[] = $keyword;
            } elseif ($rule->type === 'alias') {
                $aliases[$keyword] = mb_strtolower($rule->target_tag ?: $rule->keyword);
            } elseif ($rule->type === 'include') {
                $includes[] = [
                    'keyword' => $keyword,
                    'target' => mb_strtolower($rule->target_tag ?: $rule->keyword),
                ];
            }
        }

        preg_match_all('/[a-z0-9]{3,}|[\x{3040}-\x{309F}\x{30A0}-\x{30FF}\x{4E00}-\x{9FFF}]{2,}/u', $text, $matches);
        $words = array_unique($matches[0] ?? []);

        $tagNames = [];
        foreach ($words as $word) {
            $w = mb_strtolower($word);
            if (in_array($w, $exclude, true)) {
                continue;
            }
            $tagNames[] = $aliases[$w] ?? $w;
        }

        foreach ($includes as $include) {
            if (str_contains($text, $include['keyword'])) {
                $tagNames[] = $include['target'];
            }
        }

        $tagNames = array_unique($tagNames);
        if (empty($tagNames)) {
            return;
        }

        $tagIds = [];
        foreach ($tagNames as $name) {
            $slug = self::makeSlug($name);
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

    private static function makeSlug(string $name): string
    {
        $slug = Str::slug($name);
        if (empty($slug)) {
            $slug = md5($name);
        }
        return substr($slug, 0, 240);
    }
}
