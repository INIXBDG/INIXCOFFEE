<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $currentDb = \Illuminate\Support\Facades\Config::get('database.connections.' . \Illuminate\Support\Facades\Config::get('database.default') . '.database');

        // Safety guard: NEVER allow tests to run on the main production/development database 'inixcoffee'
        if ($currentDb === 'inixcoffee') {
            throw new \RuntimeException(
                "FATAL SAFETY GUARD: Tests are trying to run on the main database ('inixcoffee')! " .
                "Execution aborted to prevent database wiping (migrate:fresh). " .
                "Please configure phpunit.xml to use 'inixcoffee_test'."
            );
        }

        // Auto-create testing database in MySQL if it doesn't exist
        if (\Illuminate\Support\Facades\Config::get('database.default') === 'mysql' && $currentDb === 'inixcoffee_test') {
            $this->ensureTestDatabaseExists();
        }
    }

    private function ensureTestDatabaseExists(): void
    {
        try {
            $host = \Illuminate\Support\Facades\Config::get('database.connections.mysql.host', '127.0.0.1');
            $port = \Illuminate\Support\Facades\Config::get('database.connections.mysql.port', '3306');
            $user = \Illuminate\Support\Facades\Config::get('database.connections.mysql.username', 'root');
            $pass = \Illuminate\Support\Facades\Config::get('database.connections.mysql.password', '');

            $pdo = new \PDO("mysql:host={$host};port={$port}", $user, $pass);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `inixcoffee_test` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        } catch (\Throwable $e) {
            // Log or ignore if database creation fails due to existing DB/permissions
        }
    }
}
