<?php

namespace App\Jobs;

use App\Models\Archive;
use App\Services\TagExtractor;
use fivefilters\Readability\Configuration;
use fivefilters\Readability\Readability;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchArchiveBody implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public const MAX_BODY_LENGTH = 20000;

    public function __construct(public Archive $archive)
    {
    }

    public function handle(): void
    {
        $url = $this->archive->url;
        if (! $url) {
            return;
        }

        try {
            $response = Http::timeout(15)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (IIAS-ArchiveBot/1.0)'])
                ->withOptions(['verify' => false])
                ->get($url);

            if (! $response->successful()) {
                return;
            }

            $contentType = $response->header('Content-Type');
            if ($contentType && ! str_contains(strtolower($contentType), 'text/html')) {
                return;
            }

            $html = $response->body();
            if (empty($html)) {
                return;
            }

            $readability = new Readability(new Configuration());
            $readability->parse($html);

            $content = $readability->getContent();
            if (! $content) {
                return;
            }

            $text = html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $text = preg_replace('/\s+/u', ' ', $text) ?? $text;
            $text = trim($text);

            if ($text === '') {
                return;
            }

            $this->archive->body = mb_substr($text, 0, self::MAX_BODY_LENGTH);
            $this->archive->save();
            TagExtractor::extract($this->archive->fresh());
        } catch (\Throwable $e) {
            Log::warning("FetchArchiveBody failed for {$url}: ".$e->getMessage());
        }
    }
}
