<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use ThreeLeaf\ValidationEngine\Providers\ValidationServiceProvider;

abstract class TestCase extends OrchestraTestCase
{

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpRoutes();

        if (DB::connection()->getDriverName() === 'sqlite') {
            /* Enable foreign key constraints for SQLite */
            DB::statement('PRAGMA foreign_keys=ON;');
        }
    }

    /**
     * Define the routes required for testing.
     */
    protected function setUpRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->group(__DIR__ . '/../routes/api.php');
    }

    /**
     * Get package providers.
     *
     * @param Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app): array
    {
        return [
            ValidationServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        /* Use SQLite in-memory database for testing. */
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
