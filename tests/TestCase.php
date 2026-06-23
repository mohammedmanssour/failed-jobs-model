<?php

namespace MohammedManssour\FailedJobsModel\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;
use MohammedManssour\FailedJobsModel\FailedJobsModelServiceProvider;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'MohammedManssour\\FailedJobsModel\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            FailedJobsModelServiceProvider::class,
        ];
    }

    /**
     * Run the stock failed_jobs table migration before the package migration
     * (which is registered by the service provider and only alters the table).
     */
    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }

    public function getEnvironmentSetUp($app)
    {
        // Defaults to the in-memory sqlite "testing" connection. Set DB_CONNECTION
        // (plus DB_HOST/DB_PORT/DB_DATABASE/DB_USERNAME/DB_PASSWORD) to run the
        // suite against MySQL or Postgres instead.
        $connection = env('DB_CONNECTION', 'testing');

        config()->set('database.default', $connection);

        if (in_array($connection, ['mysql', 'pgsql'], true)) {
            config()->set("database.connections.{$connection}", [
                'driver' => $connection,
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', $connection === 'pgsql' ? '5432' : '3306'),
                'database' => env('DB_DATABASE', 'testing'),
                'username' => env('DB_USERNAME', $connection === 'pgsql' ? 'postgres' : 'root'),
                'password' => env('DB_PASSWORD', ''),
                'prefix' => '',
            ]);
        }

        // queue:prune-failed resolves its own connection from this config; point
        // it at the same connection the tests use.
        config()->set('queue.failed.driver', 'database-uuids');
        config()->set('queue.failed.database', $connection);
        config()->set('queue.failed.table', 'failed_jobs');
    }
}
