<?php

namespace HashmatWaziri\LaravelMultiAuthImpersonate;

use Illuminate\Support\ServiceProvider;
use HashmatWaziri\LaravelMultiAuthImpersonate\Commands\LaravelMultiAuthImpersonateCommand;

class LaravelMultiAuthImpersonateServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/laravel-multi-auth-impersonate.php' => config_path('laravel-multi-auth-impersonate.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../resources/views' => base_path('resources/views/vendor/laravel-multi-auth-impersonate'),
            ], 'views');

            $migrationFileName = 'create_laravel_multi_auth_impersonate_table.php';
            if (! $this->migrationFileExists($migrationFileName)) {
                $this->publishes([
                    __DIR__ . "/../database/migrations/{$migrationFileName}.stub" => database_path('migrations/' . date('Y_m_d_His', time()) . '_' . $migrationFileName),
                ], 'migrations');
            }

            $this->commands([
                LaravelMultiAuthImpersonateCommand::class,
            ]);
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'laravel-multi-auth-impersonate');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/laravel-multi-auth-impersonate.php', 'laravel-multi-auth-impersonate');
    }

    public static function migrationFileExists(string $migrationFileName): bool
    {
        $len = strlen($migrationFileName);
        foreach (glob(database_path("migrations/*.php")) as $filename) {
            if ((substr($filename, -$len) === $migrationFileName)) {
                return true;
            }
        }

        return false;
    }
}
