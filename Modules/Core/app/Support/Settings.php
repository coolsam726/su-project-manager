<?php

namespace Modules\Core\Support;

use Modules\Core\Settings\WebserviceSettings;

class Settings
{
    public function webservice(): WebserviceSettings
    {
        return new WebserviceSettings();
    }
}
