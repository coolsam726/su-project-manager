<?php
namespace Coolsam\Modules\Support;

use Modules\Projects\Support\Helpers;

if (!function_exists('projects')) {
    function projects(): Helpers
    {
        return app(Helpers::class);
    }
}