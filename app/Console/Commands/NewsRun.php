<?php

namespace App\Console\Commands;

use App\Models\NewsRun as Run;
use App\Support\News\NewsRunner;
use Illuminate\Console\Command;

/** Runs automatic news once, now, regardless of the schedule (for testing). */
class NewsRun extends Command
{
    protected $signature = 'news:run';

    protected $description = 'Write automatic news now';

    public function handle(NewsRunner $runner): int
    {
        if (Run::query()->active()->exists()) {
            $this->error('Another news run is queued or running.');

            return self::FAILURE;
        }

        $this->info('Searching and writing… this can take a few minutes.');
        $run = $runner->execute(Run::create(['trigger' => 'manual', 'status' => Run::QUEUED]));

        if ($run->status !== Run::SUCCEEDED) {
            $this->error("Run #{$run->id} failed: {$run->error}");

            return self::FAILURE;
        }

        foreach ($run->articles as $article) {
            $this->line("- {$article->title} ({$article->status->value}) /articles/{$article->slug}");
        }
        $this->info(sprintf('%d tokens in, %d out, %d searches (~$%.2f).', $run->input_tokens, $run->output_tokens, $run->web_searches, $run->estimatedCost()));

        return self::SUCCESS;
    }
}
