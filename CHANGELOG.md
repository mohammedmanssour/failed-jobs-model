# Changelog

All notable changes to `failed-jobs-model` will be documented in this file.

## 1.1.0 - 2026-06-27

### Changed

- The `display_name` index is now a compound index over `[display_name, failed_at]`. Class-name lookups now satisfy the default `failed_at` descending order from the same index, avoiding a filesort. Tested against large tables for a substantial query-time improvement.

## 1.0.0 - 2026-06-23

Initial release.

- `FailedJob` Eloquent model for the `failed_jobs` table with `payload`, `command`, `exception`, and `failed_at` accessors.
- Indexed `display_name` generated column (derived from `payload->displayName`) for fast lookups and aggregations. Supports MySQL, PostgreSQL, and SQLite.
- Query scopes: `whereDisplayName`, `whereExceptionContains`, and `withoutLatestFailed`, plus a `latestFailed` global scope ordering by `failed_at` descending.
- Artisan commands: `failed-jobs:stats`, `failed-jobs:size`, and `failed-jobs:prune`.
- Scheduled pruning via `queue:prune-failed`, configurable through `config/failed-jobs-model.php`.
