<?php

it('reports the row count and table size', function () {
    createFailedJob();
    createFailedJob();

    $this->artisan('failed-jobs:size')
        ->expectsOutputToContain('failed_jobs')
        ->expectsOutputToContain('Total size')
        ->assertSuccessful();
});
