<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * RefreshDatabase runs migrate:fresh, so a test run that picks up the dev
     * database (the container's real DB_* variables beat phpunit.xml's) would
     * wipe it. Refuse to boot unless it's the in-memory SQLite one - this
     * runs before anything touches the database.
     */
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        $default = $app['config']->get('database.default');
        $database = $app['config']->get("database.connections.{$default}.database");

        if ($default !== 'sqlite' || $database !== ':memory:') {
            throw new RuntimeException("Refusing to run tests against the [{$default}] database [{$database}]: they would wipe it. Tests must use in-memory SQLite.");
        }

        return $app;
    }
}
