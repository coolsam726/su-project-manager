<?php

namespace Modules\Core\Support;

if (! function_exists('Modules\Core\Support\utils')) {
    /**
     * @deprecated Use Modules\Core\Support\core instead
     */
    function utils(): Core
    {
        return app(Core::class);
    }
}
if (! function_exists('Modules\Core\Support\core')) {
    function core(): Core
    {
        return app(Core::class);
    }
}

if (! function_exists('Modules\Core\Support\settings')) {
    function settings(): Settings
    {
        return app(Settings::class);
    }
}
