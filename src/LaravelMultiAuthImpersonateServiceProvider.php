<?php

namespace HashmatWaziri\LaravelMultiAuthImpersonate;

use Illuminate\Auth\AuthManager;
use HashmatWaziri\LaravelMultiAuthImpersonate\Guard\SessionGuard;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use HashmatWaziri\LaravelMultiAuthImpersonate\Commands\LaravelMultiAuthImpersonateCommand;
use Illuminate\View\Compilers\BladeCompiler;
use HashmatWaziri\LaravelMultiAuthImpersonate\Services\ImpersonateManager;
use HashmatWaziri\LaravelMultiAuthImpersonate\Middleware\ProtectFromImpersonation;

class LaravelMultiAuthImpersonateServiceProvider extends ServiceProvider
{
    /** @var string $configName */
    protected $configName = 'laravel-multi-auth-impersonate';


    public function register()
    {
        $this->mergeConfig();
        $this->app->bind(ImpersonateManager::class, ImpersonateManager::class);

        $this->app->singleton(ImpersonateManager::class, function ($app) {
            return new ImpersonateManager($app);
        });
        $this->app->alias(ImpersonateManager::class, 'impersonate');
        $this->registerRoutesMacro();
        $this->registerBladeDirectives();
        $this->registerMiddleware();
        $this->registerAuthDriver();

    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/' . $this->configName . '.php' => config_path($this->configName . '.php'),
            ], 'multiAuthImpersonate');


        }


//            $this->publishConfig();

        // We want to remove data from storage on real login and logout
        Event::listen(Login::class, function ($event) {
            app('impersonate')->clear();
        });
        Event::listen(Logout::class, function ($event) {
            app('impersonate')->clear();
        });


        $this->commands([
            LaravelMultiAuthImpersonateCommand::class,
        ]);

    }

    /**
     * Register plugin blade directives.
     *
     * @param void
     * @return  void
     */
    protected function registerBladeDirectives()
    {
        $this->app->afterResolving('blade.compiler', function (BladeCompiler $bladeCompiler) {
            $bladeCompiler->directive('impersonating', function ($guard = null) {
                return "<?php if (is_impersonating({$guard})) : ?>";
            });

            $bladeCompiler->directive('endImpersonating', function () {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('canImpersonate', function ($guard = null) {
                return "<?php if (can_impersonate({$guard})) : ?>";
            });

            $bladeCompiler->directive('endCanImpersonate', function () {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('canBeImpersonated', function ($expression) {
                $args = preg_split("/,(\s+)?/", $expression);
                $guard = $args[1] ?? null;

                return "<?php if (can_be_impersonated({$args[0]}, {$guard})) : ?>";
            });

            $bladeCompiler->directive('endCanBeImpersonated', function () {
                return '<?php endif; ?>';
            });
        });

    }

    /**
     * Register routes macro.
     *
     * @param void
     * @return  void
     */
    protected function registerRoutesMacro()
    {


        Route::macro('multiAuthImpersonate', function (string $prefix){
            Route::prefix($prefix)->group(function ()  {
                Route::get('/take/{id}/{guardName?}',
                    '\HashmatWaziri\LaravelMultiAuthImpersonate\Http\Controllers\ImpersonateController@take')->name('multiAuthImpersonate');
                Route::get('/leave',
                    '\HashmatWaziri\LaravelMultiAuthImpersonate\Http\Controllers\ImpersonateController@leave')->name('multiAuthImpersonate.leave');
            });

        });

    }

    /**
     * @param void
     * @return  void
     */
    protected function registerAuthDriver()
    {
        /** @var AuthManager $auth */
        $auth = $this->app['auth'];

        $auth->extend('session', function (Application $app, $name, array $config) use ($auth) {
            $provider = $auth->createUserProvider($config['provider']);

            $guard = new SessionGuard($name, $provider, $app['session.store']);

            if (method_exists($guard, 'setCookieJar')) {
                $guard->setCookieJar($app['cookie']);
            }

            if (method_exists($guard, 'setDispatcher')) {
                $guard->setDispatcher($app['events']);
            }

            if (method_exists($guard, 'setRequest')) {
                $guard->setRequest($app->refresh('request', $guard, 'setRequest'));
            }

            return $guard;
        });
    }

    /**
     * Register plugin middleware.
     *
     * @param void
     * @return  void
     */
    public function registerMiddleware()
    {
        $this->app['router']->aliasMiddleware('impersonate.protect', ProtectFromImpersonation::class);

    }

    /**
     * Merge config file.
     *
     * @param void
     * @return  void
     */
    protected function mergeConfig()
    {
        $configPath = __DIR__ . '/../config/' . $this->configName . '.php';

        $this->mergeConfigFrom($configPath, $this->configName);
    }


    /**
     * Publish config file.
     *
     * @param void
     * @return  void
     */
    protected function publishConfig()
    {
        $configPath = __DIR__ . '/../config/' . $this->configName . '.php';

        $this->publishes([$configPath => config_path($this->configName . '.php')], 'impersonate');
    }
}
