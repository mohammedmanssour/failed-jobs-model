<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $driver = Schema::getConnection()->getDriverName();

        // MySQL needs json_unquote to strip the quotes json_extract keeps around
        // strings; SQLite's json_extract already returns the unquoted scalar;
        // Postgres has no json_extract, uses the ->> operator and needs the
        // text payload cast to jsonb first.
        $expression = match ($driver) {
            'mysql' => "json_unquote(json_extract(`payload`, '$.displayName'))",
            'pgsql' => "(payload::jsonb ->> 'displayName')",
            'sqlite' => "json_extract(payload, '$.displayName')",
            default => throw new RuntimeException("Unsupported database driver [{$driver}] for failed_jobs display_name index."),
        };

        Schema::table('failed_jobs', function (Blueprint $table) use ($expression, $driver) {
            $column = $table->string('display_name')->after('payload');

            // Postgres only supports STORED generated columns (< v18); MySQL and
            // SQLite can keep it VIRTUAL since the value lives in the index anyway.
            $driver === 'pgsql'
                ? $column->storedAs($expression)
                : $column->virtualAs($expression);

            // Compound index: display_name filters, failed_at serves the
            // latestFailed global scope's "order by failed_at desc" from the
            // same index, so display_name lookups avoid a filesort entirely.
            $table->index(['display_name', 'failed_at']);
        });
    }

    public function down()
    {
        Schema::table('failed_jobs', function (Blueprint $table) {
            $table->dropIndex(['display_name', 'failed_at']);
            $table->dropColumn('display_name');
        });
    }
};
