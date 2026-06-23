<?php

namespace MohammedManssour\FailedJobsModel;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;

/**
 * @property-read int $id
 * @property-read string $uuid
 * @property-read string $connection
 * @property-read string $queue
 * @property-read object $payload
 * @property-read ?string $display_name
 * @property-read \Carbon\CarbonInterface $failed_at
 */
class FailedJob extends Model
{
    public $timestamps = false;

    /*-----------------------------------------------------
    * Boot
    -----------------------------------------------------*/
    protected static function booted(): void
    {
        static::addGlobalScope('latestFailed', function (Builder $query) {
            $query->orderByDesc('failed_at');
        });
    }

    /*-----------------------------------------------------
    * Attributes
    -----------------------------------------------------*/
    protected function payload(): Attribute
    {
        return Attribute::get(fn($value) => json_decode($value))->shouldCache();
    }

    protected function exception(): Attribute
    {
        return Attribute::get(fn($value) => mb_convert_encoding($value, 'UTF-8'))->shouldCache();
    }

    protected function failedAt(): Attribute
    {
        return Attribute::get(fn($value) => Date::parse($value))->shouldCache();
    }

    protected function command(): Attribute
    {
        return Attribute::get(fn() => unserialize($this->payload->data->command))->shouldCache();
    }

    /*-----------------------------------------------------
    * Methods
    -----------------------------------------------------*/
    public static function findByJobId(string $uuid): ?static
    {
        return FailedJob::query()->where('uuid', $uuid)->first();
    }

    public static function findByDisplayName(string $name): Collection
    {
        return FailedJob::query()->whereDisplayName($name)->get();
    }

    /*-----------------------------------------------------
    * scope
    -----------------------------------------------------*/
    public function scopeWhereDisplayName(Builder $query, string $name): Builder
    {
        return $query->where('display_name', $name);
    }

    public function scopeWhereExceptionContains(Builder $query, string $needle): Builder
    {
        // Postgres LIKE is case-sensitive; use ILIKE so "contains" stays
        // case-insensitive like MySQL's default collation.
        $operator = $query->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        return $query->where('exception', $operator, '%'.$needle.'%');
    }

    public function scopeWithoutLatestFailed(Builder $query): Builder
    {
        return $query->withoutGlobalScope('latestFailed');
    }
}
