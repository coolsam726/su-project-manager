<?php

namespace Modules\Core\Facades;

use Illuminate\Support\Facades\Facade;

class Core extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Modules\Core\Support\Core::class;
    }
}
