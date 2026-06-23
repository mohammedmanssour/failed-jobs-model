<?php

namespace MohammedManssour\FailedJobsModel\Commands;

use Illuminate\Console\Command;
use MohammedManssour\FailedJobsModel\FailedJob;

class FailedJobsStatsCommand extends Command
{
    public $signature = 'failed-jobs:stats {--top=5 : Number of most-failing jobs to display}';

    public $description = 'Show stats about the most frequently failing jobs';

    public function handle(): int
    {
        $top = (int) $this->option('top');

        if ($top < 1) {
            $this->error('The --top option must be a positive integer.');

            return self::INVALID;
        }

        $stats = FailedJob::query()
            ->withoutLatestFailed()
            ->selectRaw('display_name, count(*) as failures')
            ->groupBy('display_name')
            ->orderByDesc('failures')
            ->limit($top)
            ->get();

        if ($stats->isEmpty()) {
            $this->info('No failed jobs found.');

            return self::SUCCESS;
        }

        $this->table(
            ['Job', 'Failures'],
            $stats->map(fn ($row) => [$row->display_name ?? '(unknown)', $row->failures])->all()
        );

        return self::SUCCESS;
    }
}
