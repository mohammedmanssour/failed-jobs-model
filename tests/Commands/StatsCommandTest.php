<?php

it('lists the most frequently failing jobs', function () {
    foreach (range(1, 3) as $i) {
        createFailedJob(['displayName' => 'App\\Jobs\\HeavyJob']);
    }
    foreach (range(1, 2) as $i) {
        createFailedJob(['displayName' => 'App\\Jobs\\LightJob']);
    }
    createFailedJob(['displayName' => 'App\\Jobs\\RareJob']);

    $this->artisan('failed-jobs:stats', ['--top' => 2])
        ->expectsTable(
            ['Job', 'Failures'],
            [
                ['App\\Jobs\\HeavyJob', 3],
                ['App\\Jobs\\LightJob', 2],
            ],
        )
        ->assertSuccessful();
});

it('limits the results to the requested top count', function () {
    createFailedJob(['displayName' => 'App\\Jobs\\HeavyJob']);
    createFailedJob(['displayName' => 'App\\Jobs\\LightJob']);
    createFailedJob(['displayName' => 'App\\Jobs\\RareJob']);

    $this->artisan('failed-jobs:stats', ['--top' => 1])
        ->doesntExpectOutputToContain('App\\Jobs\\RareJob')
        ->assertSuccessful();
});

it('reports when there are no failed jobs', function () {
    $this->artisan('failed-jobs:stats')
        ->expectsOutputToContain('No failed jobs found.')
        ->assertSuccessful();
});

it('rejects a non-positive top option', function () {
    $this->artisan('failed-jobs:stats', ['--top' => 0])
        ->expectsOutputToContain('The --top option must be a positive integer.')
        ->assertFailed();
});
