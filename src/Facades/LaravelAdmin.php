<?php

namespace Samik\LaravelAdmin\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Samik\LaravelAdmin\Skeleton\SkeletonClass
 */
class LaravelAdmin extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-admin';
    }
}
