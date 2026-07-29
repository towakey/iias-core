<?php

namespace App\Console\Commands;

use App\Models\Archive;
use App\Services\TagExtractor;
use Illuminate\Console\Command;

class RetagArchives extends Command
{
    protected $signature = 'iias:retag-archives {--user= : target user_id}';

    protected $description = 'Rebuild tags for existing archives using current TagExtractor rules';

    public function handle(): int
    {
        $query = Archive::query();

        if ($this->option('user')) {
            $query->where('user_id', $this->option('user'));
        }

        $count = 0;
        $query->with('user')->chunk(100, function ($archives) use (&$count) {
            foreach ($archives as $archive) {
                $archive->tags()->detach();
                TagExtractor::extract($archive);
                $count++;
            }
        });

        $this->info("Retagged {$count} archives.");

        return self::SUCCESS;
    }
}
