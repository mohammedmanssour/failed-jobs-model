<?php

// config for MohammedManssour/FailedJobsModel
return [

    /*
    |--------------------------------------------------------------------------
    | Failed jobs pruning
    |--------------------------------------------------------------------------
    |
    | The package can schedule Laravel's `queue:prune-failed` command for you
    | to keep the failed_jobs table from growing forever.
    |
    */
    'pruning' => [

        /*
         * Whether the package should register the scheduled pruning command.
         */
        'enabled' => env('FAILED_JOBS_PRUNE_ENABLED', true),

        /*
         * Failed jobs older than this many hours will be pruned.
         */
        'hours' => (int) env('FAILED_JOBS_PRUNE_HOURS', 168),

        /*
         * The time of day the pruning runs, in 24-hour "HH:MM" format.
         */
        'at' => env('FAILED_JOBS_PRUNE_AT', '01:00'),

        /*
         * Restrict scheduling to these environments. Leave empty to run in all
         * environments. Provide a comma-separated list via the env variable.
         */
        'environments' => array_filter(
            explode(',', (string) env('FAILED_JOBS_PRUNE_ENVIRONMENTS', 'production'))
        ),

    ],

];
