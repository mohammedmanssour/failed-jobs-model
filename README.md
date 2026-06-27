# Failed Jobs Model

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mohammedmanssour/failed-jobs-model.svg?style=flat-square)](https://packagist.org/packages/mohammedmanssour/failed-jobs-model)
[![Tests](https://github.com/mohammedmanssour/failed-jobs-model/actions/workflows/run-tests.yml/badge.svg)](https://github.com/mohammedmanssour/failed-jobs-model/actions/workflows/run-tests.yml)

An Eloquent model, query scopes, and Artisan commands to inspect, analyze, and prune Laravel's `failed_jobs` table — with an indexed `display_name` for fast lookups and stats.

## Installation

```bash
composer require mohammedmanssour/failed-jobs-model
```

The package adds a `display_name` column (a generated column derived from `payload->displayName`) and a compound index on `[display_name, failed_at]` — so class-name lookups also satisfy the default `failed_at` ordering from the same index, with no filesort. The migration runs automatically with:

```bash
php artisan migrate
```

> Supports MySQL, PostgreSQL, and SQLite. On MySQL/SQLite the column is `VIRTUAL`; on PostgreSQL it is `STORED`.

Optionally publish the config and migration:

```bash
php artisan vendor:publish --tag="failed-jobs-model-config"
php artisan vendor:publish --tag="failed-jobs-model-migrations"
```

## The model

`MohammedManssour\FailedJobsModel\FailedJob` maps to the `failed_jobs` table.

```php
use MohammedManssour\FailedJobsModel\FailedJob;

$job = FailedJob::findByJobId($uuid);   // single job by uuid, or null

$job->display_name;   // e.g. "App\Jobs\SendEmail" (indexed)
$job->payload;        // decoded payload object
$job->command;        // the unserialized job command instance
$job->exception;      // exception text
$job->failed_at;      // Carbon instance
```

Every query is ordered by `failed_at` descending by default.

### Scopes

```php
// Jobs for a given class name (uses the indexed display_name column)
FailedJob::whereDisplayName('App\Jobs\SendEmail')->get();
FailedJob::findByDisplayName('App\Jobs\SendEmail'); // shortcut, latest first

// Search the exception text (case-insensitive)
FailedJob::whereExceptionContains('Connection timed out')->get();

// Drop the default failed_at ordering (e.g. for aggregations)
FailedJob::withoutLatestFailed()->get();
```

## Commands

```bash
# Most frequently failing jobs (defaults to top 5)
php artisan failed-jobs:stats --top=10

# Row count and total size of the failed_jobs table
php artisan failed-jobs:size

# Prune failed jobs older than N hours (falls back to config)
php artisan failed-jobs:prune --hours=24
```

## Scheduled pruning

The package schedules `queue:prune-failed` for you, configured via `config/failed-jobs-model.php`:

```php
'pruning' => [
    'enabled' => env('FAILED_JOBS_PRUNE_ENABLED', true),
    'hours' => (int) env('FAILED_JOBS_PRUNE_HOURS', 168),       // delete jobs older than 7 days
    'at' => env('FAILED_JOBS_PRUNE_AT', '01:00'),                // daily run time
    'environments' => [...],                                     // limit to these envs (empty = all)
],
```

## Testing

```bash
composer test
```

The suite runs on in-memory SQLite by default. To run it against MySQL or PostgreSQL, copy `.env.example` to `.env` and set the `DB_*` values.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
