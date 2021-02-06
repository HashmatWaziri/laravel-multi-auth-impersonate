<?php

namespace HashmatWaziri\LaravelMultiAuthImpersonate\Tests;

use HashmatWaziri\LaravelMultiAuthImpersonate\LaravelMultiAuthImpersonateServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'HashmatWaziri\\LaravelMultiAuthImpersonate\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelMultiAuthImpersonateServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        /*
        include_once __DIR__.'/../database/migrations/create_laravel_multi_auth_impersonate_table.php.stub';
        (new \CreatePackageTable())->up();
        */
    }
}
