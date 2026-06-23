<?php

use Illuminate\Support\Str;
use MohammedManssour\FailedJobsModel\FailedJob;
use MohammedManssour\FailedJobsModel\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

/**
 * Insert a failed job row.
 *
 * `displayName`, `data` and `command` all shape the JSON `payload` (the
 * display_name column is generated from it by the database). Any other key
 * — uuid, connection, queue, exception, failed_at — is written as a column.
 */
function createFailedJob(array $attributes = []): FailedJob
{
    $uuid = $attributes['uuid'] ?? (string) Str::uuid();

    // payload->data, including a serialized command if one is given.
    $data = $attributes['data'] ?? [];
    if (array_key_exists('command', $attributes)) {
        $data['command'] = is_string($attributes['command'])
            ? $attributes['command']
            : serialize($attributes['command']);
    }

    $payload = array_merge([
        'uuid' => $uuid,
        'displayName' => $attributes['displayName'] ?? 'App\\Jobs\\DefaultJob',
        'data' => $data,
    ], $attributes['payload'] ?? []);

    // Strip payload shapers so only real columns remain to be written.
    unset(
        $attributes['displayName'],
        $attributes['data'],
        $attributes['command'],
        $attributes['payload'],
    );

    return FailedJob::query()->forceCreate(array_merge([
        'uuid' => $uuid,
        'connection' => 'database',
        'queue' => 'default',
        'payload' => json_encode($payload),
        'exception' => 'Default exception message',
        'failed_at' => now(),
    ], $attributes));
}
