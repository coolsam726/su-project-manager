<?php

namespace Modules\Core\Support;

use Modules\Core\Settings\WebserviceSettings;

class Ws
{
    public static function settings(): WebserviceSettings
    {
        return new WebserviceSettings();
    }
}
