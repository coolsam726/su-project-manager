<?php

namespace Modules\Core\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Modules\Core\Support\Triggers
 */
class Triggers extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Modules\Core\Support\Triggers::class;
    }
}
