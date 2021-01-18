<?php

namespace HashmatWaziri\LaravelMultiAuthImpersonate\Services;

use Exception;
use HashmatWaziri\LaravelMultiAuthImpersonate\Models\Impersonate;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Routing\Route;
use HashmatWaziri\LaravelMultiAuthImpersonate\Events\LeaveImpersonation;
use HashmatWaziri\LaravelMultiAuthImpersonate\Events\TakeImpersonation;
use HashmatWaziri\LaravelMultiAuthImpersonate\Exceptions\InvalidUserProvider;
use HashmatWaziri\LaravelMultiAuthImpersonate\Exceptions\MissingUserProvider;

class ImpersonateManager
{
    const REMEMBER_PREFIX = 'remember_web';

    /** @var Application $app */
    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param int $id
     * @return \Illuminate\Contracts\Auth\Authenticatable
     * @throws MissingUserProvider
     * @throws InvalidUserProvider
     * @throws ModelNotFoundException
     */
    public function findUserById($id, $guardName = null)
    {

        if (empty($guardName)) {
            $guardName = $this->app['config']->get('auth.default.guard', 'web');
        }

        $providerName = $this->app['config']->get("auth.guards.$guardName.provider");

        if (empty($providerName)) {
            throw new MissingUserProvider($guardName);
        }

        try {
            /** @var UserProvider $userProvider */
            $userProvider = $this->app['auth']->createUserProvider($providerName);
        } catch (\InvalidArgumentException $e) {
            throw new InvalidUserProvider($guardName);
        }

        if (!($modelInstance = $userProvider->retrieveById($id))) {
            $model = $this->app['config']->get("auth.providers.$providerName.model");

            throw (new ModelNotFoundException())->setModel(
                $model,
                $id
            );
        }

        return $modelInstance;
    }

    public function isImpersonating(): bool
    {
        return session()->has($this->getSessionKey());
    }

    /**
     * @return  int|null
     */
    public function getImpersonatorId()
    {
        return session($this->getSessionKey(), null);
    }

    /**
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function getImpersonator()
    {
        $id = session($this->getSessionKey(), null);

        return is_null($id) ? null : $this->findUserById($id, $this->getImpersonatorGuardName());
    }

    /**
     * @return string|null
     */
    public function getImpersonatorGuardName()
    {
        return session($this->getSessionGuard(), null);
    }

    /**
     * @return string|null
     */
    public function getImpersonatedGuardName()
    {
        return session($this->getImpersonatedGuard(), null);
    }

    /**
     * @return string|null
     */
    public function getImpersonatorGuardUsingName()
    {
        return session($this->getSessionGuardUsing(), null);
    }

    /**
     * @param \Illuminate\Contracts\Auth\Authenticatable $from
     * @param \Illuminate\Contracts\Auth\Authenticatable $to
     * @param string|null                         $guardName
     * @return bool
     */
    public function take($from, $to, $guardName = null)
    {
        $this->saveAuthCookieInSession();

        try {
            $currentGuard = $this->getCurrentAuthGuardName();
            session()->put($this->getSessionKey(), $from->getAuthIdentifier());
            session()->put($this->getImpersonatedId(), $to->getAuthIdentifier());
            session()->put($this->getSessionGuard(), $currentGuard);
            session()->put($this->getImpersonatedGuard(), $guardName);
            session()->put($this->getSessionGuardUsing(), $guardName);


            $this->app['auth']->guard($guardName)->quietLogin($to);

        } catch (\Exception $e) {
            unset($e);
            return false;
        }

        $this->app['events']->dispatch(new TakeImpersonation($from, $to));

        return true;
    }

    public function leave(): bool
    {
        try {
            $impersonated = $this->app['auth']->guard($this->getImpersonatorGuardUsingName())->user();

            $impersonator = $this->findUserById($this->getImpersonatorId(), $this->getImpersonatorGuardName());
            // take for example, if an employee (guard) is impersonating another employee(guard), then do not logout else if an employee (guard) is impersonating any other guard, then logout the user
            if(!($this->getImpersonatorGuardName() == $this->getImpersonatorGuardUsingName())){

                $this->app['auth']->guard($this->getImpersonatorGuardUsingName())->quietLogout();

            }
            $this->app['auth']->guard($this->getImpersonatorGuardName())->quietLogin($impersonator);

            $this->extractAuthCookieFromSession();

            $this->clear();

        } catch (\Exception $e) {
            unset($e);
            return false;
        }

        $this->app['events']->dispatch(new LeaveImpersonation($impersonator, $impersonated));

        return true;
    }

    public function clear()
    {
        session()->forget($this->getSessionKey());
        session()->forget($this->getSessionGuard());
        session()->forget($this->getSessionGuardUsing());
        session()->forget($this->getImpersonatedId());
    }

    public function getSessionKey(): string
    {
        return config('laravel-multi-auth-impersonate.session_key');
    }
    public function getImpersonatedId(): string
    {
        // return impersonated_id
        return config('laravel-multi-auth-impersonate.impersonated_key');
    }

    public function getSessionGuard(): string
    {
        return config('laravel-multi-auth-impersonate.session_guard');
    }
    public function getImpersonatedGuard(): string
    {
        return config('laravel-multi-auth-impersonate.impersonated_guard');
    }

    public function getSessionGuardUsing(): string
    {
        return config('laravel-multi-auth-impersonate.session_guard_using');
    }

    public function getDefaultSessionGuard(): string
    {
        return  session()->get('guardName') ?? config('laravel-multi-auth-impersonate.default_impersonator_guard');
    }

    public function getTakeRedirectTo($userToImpersonate): string
    {


        try {

            $uri = $userToImpersonate::takeRedirectTo();
        } catch (\InvalidArgumentException $e) {
            $uri = $userToImpersonate::takeRedirectTo();
        }

        return $uri;
    }

    public function getLeaveRedirectTo(): string
    {
        try {

            $impersonator = $this->findUserById($this->getImpersonatorId(), $this->getImpersonatorGuardName());


            $uri = $impersonator::leaveRedirectTo();

        } catch (\InvalidArgumentException $e) {
            $uri = $impersonator::leaveRedirectTo();
        }

        return $uri;
    }

    /**
     * @return array|null
     */
    public function getCurrentAuthGuardName()
    {
        $guards = array_keys(config('auth.guards'));

        foreach ($guards as $guard) {
            if ($this->app['auth']->guard($guard)->check()) {
                return $guard;
            }
        }

        return null;
    }




    protected function saveAuthCookieInSession(): void
    {
        $cookie = $this->findByKeyInArray($this->app['request']->cookies->all(), static::REMEMBER_PREFIX);
        $key = $cookie->keys()->first();
        $val = $cookie->values()->first();

        if (!$key || !$val) {
            return;
        }

        session()->put(static::REMEMBER_PREFIX, [
            $key,
            $val,
        ]);
    }

    protected function extractAuthCookieFromSession(): void
    {
        if (!$session = $this->findByKeyInArray(session()->all(), static::REMEMBER_PREFIX)->first()) {
            return;
        }

        $this->app['cookie']->queue($session[0], $session[1]);
        session()->forget($session);
    }

    /**
     * @param array  $values
     * @param string $search
     * @return \Illuminate\Support\Collection
     */
    protected function findByKeyInArray(array $values, string $search)
    {
        return collect($values ?? session()->all())
            ->filter(function ($val, $key) use ($search) {
                return strpos($key, $search) !== false;
            });
    }
}
