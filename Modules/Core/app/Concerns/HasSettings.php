<?php

namespace Modules\Core\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
trait HasSettings
{
    public function initHasSettings(): void
    {
        $this->mergeCasts([
            'settings' => 'array',
        ]);
    }

    public function retrieveSettings(): array
    {
        return $this->settings ?? [];
    }

    public function setting(string $key)
    {
        // retrieve the setting of a dotted string key
        $settings = $this->retrieveSettings();

        return data_get($settings, $key);
    }

    public function updateSetting(string $key, mixed $value)
    {
        $settings = $this->retrieveSettings();
        $settings[$key] = $value;
        $this->settings = $settings;
        $this->save();

        return $this->settings;
    }
}
