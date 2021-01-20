<?php

namespace HashmatWaziri\LaravelMultiAuthImpersonate\Middleware;

use Closure;
use HashmatWaziri\LaravelMultiAuthImpersonate\Services\ImpersonateManager;
use Illuminate\Support\Facades\Redirect;

class ProtectFromImpersonation
{
    /**
     * Handle an incoming request.
     *
     * @param   \Illuminate\Http\Request  $request
     * @param   \Closure  $next
     * @return  mixed
     */
    public function handle($request, Closure $next)
    {
        $impersonate_manager = app()->make(ImpersonateManager::class);

        if ($impersonate_manager->isImpersonating()) {
            return Redirect::back();
        }

        return $next($request);
    }
}
