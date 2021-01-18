<?php

namespace HashmatWaziri\LaravelMultiAuthImpersonate;

use HashmatWaziri\LaravelMultiAuthImpersonate\Services\ImpersonateManager;
use Illuminate\Support\Facades\Facade;

class LaravelMultiAuthImpersonate extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return ImpersonateManager::class;
    }
}
