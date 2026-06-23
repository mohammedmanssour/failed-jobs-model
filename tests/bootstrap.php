<?php

require __DIR__.'/../vendor/autoload.php';

// Load a local .env (if present) so the test database connection can be
// configured without committing credentials. Real environment variables
// (e.g. from CI) always take precedence over the file.
if (file_exists(__DIR__.'/../.env')) {
    Dotenv\Dotenv::createImmutable(__DIR__.'/..')->safeLoad();
}
