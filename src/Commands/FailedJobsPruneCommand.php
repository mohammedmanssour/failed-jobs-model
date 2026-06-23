<?php

namespace MohammedManssour\FailedJobsModel\Commands;

use Illuminate\Console\Command;

class FailedJobsPruneCommand extends Command
{
    public $signature = 'failed-jobs:prune {--hours= : Delete failed jobs older than this many hours (defaults to config)}';

    public $description = 'Prune the failed_jobs table (alias for queue:prune-failed)';

    public function handle(): int
    {
        $hours = $this->option('hours') ?? config('failed-jobs-model.pruning.hours');

        return $this->call('queue:prune-failed', [
            '--hours' => (int) $hours,
        ]);
    }
}
