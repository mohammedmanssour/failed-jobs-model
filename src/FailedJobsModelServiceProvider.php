<?php

namespace MohammedManssour\FailedJobsModel;

use Illuminate\Console\Scheduling\Schedule;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use MohammedManssour\FailedJobsModel\Commands\FailedJobsPruneCommand;
use MohammedManssour\FailedJobsModel\Commands\FailedJobsSizeCommand;
use MohammedManssour\FailedJobsModel\Commands\FailedJobsStatsCommand;

class FailedJobsModelServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('failed-jobs-model')
            ->hasConfigFile()
            ->hasMigration('index_failed_jobs_table')
            ->runsMigrations()
            ->hasCommands([
                FailedJobsStatsCommand::class,
                FailedJobsSizeCommand::class,
                FailedJobsPruneCommand::class,
            ]);
    }

    public function packageBooted(): void
    {
        $this->app->booted(function () {
            $config = config('failed-jobs-model.pruning');

            if (! ($config['enabled'] ?? false)) {
                return;
            }

            $schedule = $this->app->make(Schedule::class);

            $event = $schedule->command('queue:prune-failed', [
                '--hours' => $config['hours'],
            ])->dailyAt($config['at']);

            if (! empty($config['environments'])) {
                $event->environments($config['environments']);
            }
        });
    }
}
