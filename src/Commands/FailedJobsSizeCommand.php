<?php

namespace MohammedManssour\FailedJobsModel\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Number;
use MohammedManssour\FailedJobsModel\FailedJob;
use Throwable;

class FailedJobsSizeCommand extends Command
{
    public $signature = 'failed-jobs:size';

    public $description = 'Show the total size and row count of the failed_jobs table';

    public function handle(): int
    {
        $model = new FailedJob;
        $connection = $model->getConnection();
        $table = $model->getTable();

        $totalRows = $model->newQuery()->count();
        $bytes = $this->tableSizeInBytes($connection, $table);

        $this->table(
            ['Table', 'Total rows', 'Total size'],
            [[
                $table,
                Number::format($totalRows),
                $bytes === null ? 'n/a' : Number::fileSize($bytes, precision: 2),
            ]]
        );

        return self::SUCCESS;
    }

    private function tableSizeInBytes(Connection $connection, string $table): ?int
    {
        return match ($connection->getDriverName()) {
            'mysql' => $this->mysqlSize($connection, $table),
            'pgsql' => $this->pgsqlSize($connection, $table),
            'sqlite' => $this->sqliteSize($connection, $table),
            default => null,
        };
    }

    private function mysqlSize(Connection $connection, string $table): ?int
    {
        $result = $connection->selectOne(
            'SELECT data_length + index_length AS bytes
             FROM information_schema.TABLES
             WHERE table_schema = DATABASE() AND table_name = ?',
            [$table]
        );

        return isset($result->bytes) ? (int) $result->bytes : null;
    }

    private function pgsqlSize(Connection $connection, string $table): ?int
    {
        $result = $connection->selectOne(
            'SELECT pg_total_relation_size(?) AS bytes',
            [$table]
        );

        return isset($result->bytes) ? (int) $result->bytes : null;
    }

    private function sqliteSize(Connection $connection, string $table): ?int
    {
        // dbstat is only available when SQLite was compiled with
        // SQLITE_ENABLE_DBSTAT_VTAB; degrade gracefully when it isn't.
        try {
            $result = $connection->selectOne(
                'SELECT SUM(pgsize) AS bytes FROM dbstat WHERE name = ?',
                [$table]
            );
        } catch (Throwable) {
            return null;
        }

        return isset($result->bytes) ? (int) $result->bytes : null;
    }
}
