<?php

use MohammedManssour\FailedJobsModel\FailedJob;

it('prunes jobs older than the given hours option', function () {
    $old = createFailedJob(['failed_at' => now()->subDays(3)]);
    $recent = createFailedJob(['failed_at' => now()->subHour()]);

    $this->artisan('failed-jobs:prune', ['--hours' => 24])->assertSuccessful();

    $remaining = FailedJob::query()->withoutLatestFailed()->pluck('uuid');

    expect($remaining)->toHaveCount(1)
        ->and($remaining->first())->toBe($recent->uuid);
});

it('falls back to the configured hours when no option is given', function () {
    config()->set('failed-jobs-model.pruning.hours', 24);

    $old = createFailedJob(['failed_at' => now()->subDays(3)]);
    $recent = createFailedJob(['failed_at' => now()->subHour()]);

    $this->artisan('failed-jobs:prune')->assertSuccessful();

    $remaining = FailedJob::query()->withoutLatestFailed()->pluck('uuid');

    expect($remaining)->toHaveCount(1)
        ->and($remaining->first())->toBe($recent->uuid);
});
