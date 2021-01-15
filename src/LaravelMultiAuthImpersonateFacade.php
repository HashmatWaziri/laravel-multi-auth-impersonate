<?php

namespace HashmatWaziri\LaravelMultiAuthImpersonate;

use Illuminate\Support\Facades\Facade;

/**
 * @see \HashmatWaziri\LaravelMultiAuthImpersonate\LaravelMultiAuthImpersonate
 */
class LaravelMultiAuthImpersonateFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'laravel-multi-auth-impersonate';
    }
}
