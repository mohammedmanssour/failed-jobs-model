<?php

use Illuminate\Support\Facades\DB;
use MohammedManssour\FailedJobsModel\FailedJob;

it('generates the display_name column from the payload', function () {
    createFailedJob(['displayName' => 'App\\Jobs\\SendEmail']);

    $job = FailedJob::query()->first();

    expect($job->display_name)->toBe('App\\Jobs\\SendEmail');
});

it('unserializes the command out of the payload', function () {
    $command = new stdClass;
    $command->foo = 'bar';

    createFailedJob(['command' => $command]);

    $job = FailedJob::query()->first();

    expect($job->command->foo)->toBe('bar');
});

describe('whereDisplayName scope', function () {
    it('filters by the generated display_name column', function () {
        createFailedJob(['displayName' => 'App\\Jobs\\SendEmail']);
        createFailedJob(['displayName' => 'App\\Jobs\\ChargeCard']);

        $jobs = FailedJob::query()->whereDisplayName('App\\Jobs\\SendEmail')->get();

        expect($jobs)->toHaveCount(1)
            ->and($jobs->first()->display_name)->toBe('App\\Jobs\\SendEmail');
    });

    it('is exposed through findByDisplayName ordered by latest failure', function () {
        $older = createFailedJob(['displayName' => 'App\\Jobs\\SendEmail', 'failed_at' => now()->subDay()]);
        $newer = createFailedJob(['displayName' => 'App\\Jobs\\SendEmail', 'failed_at' => now()]);
        createFailedJob(['displayName' => 'App\\Jobs\\ChargeCard']);

        $jobs = FailedJob::findByDisplayName('App\\Jobs\\SendEmail');

        expect($jobs->pluck('uuid')->all())->toBe([$newer->uuid, $older->uuid]);
    });
});

describe('whereExceptionContains scope', function () {
    it('matches a substring of the exception', function () {
        createFailedJob(['exception' => 'RuntimeException: connection timed out']);
        createFailedJob(['exception' => 'LogicException: bad state']);

        $jobs = FailedJob::query()->whereExceptionContains('timed out')->get();

        expect($jobs)->toHaveCount(1);
    });

    it('matches case-insensitively', function () {
        createFailedJob(['exception' => 'RuntimeException: Connection Timed Out']);

        $jobs = FailedJob::query()->whereExceptionContains('connection')->get();

        expect($jobs)->toHaveCount(1);
    })->skip(
        fn () => DB::connection()->getDriverName() === 'sqlite',
        'SQLite LIKE is only case-insensitive for ASCII and not worth asserting here.',
    );
});

describe('latestFailed global scope', function () {
    it('orders every query by failed_at descending by default', function () {
        $oldest = createFailedJob(['failed_at' => now()->subDays(2)]);
        $newest = createFailedJob(['failed_at' => now()]);
        $middle = createFailedJob(['failed_at' => now()->subDay()]);

        $uuids = FailedJob::query()->pluck('uuid')->all();

        expect($uuids)->toBe([$newest->uuid, $middle->uuid, $oldest->uuid]);
    });

    it('can be removed with the withoutLatestFailed scope', function () {
        createFailedJob(['failed_at' => now()->subDay()]);
        createFailedJob(['failed_at' => now()]);

        $query = FailedJob::query()->withoutLatestFailed()->toRawSql();

        expect($query)->not->toContain('order by');
    });
});
