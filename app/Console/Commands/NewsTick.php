<?php

namespace App\Console\Commands;

use App\Support\News\NewsRunner;
use Illuminate\Console\Command;

/**
 * Scheduled every minute (routes/console.php): runs a requested or due
 * automatic-news run, if any (App\Support\News\NewsRunner::tick()).
 */
class NewsTick extends Command
{
    protected $signature = 'news:tick';

    protected $description = 'Run automatic news when a run is requested or due';

    public function handle(NewsRunner $runner): int
    {
        $run = $runner->tick();

        if ($run) {
            $this->line("News run #{$run->id}: {$run->status} ({$run->articles_count} articles)".($run->error ? " — {$run->error}" : ''));
        }

        return self::SUCCESS;
    }
}
