<?php

namespace Modules\Projects\Filament;

use Coolsam\Modules\Concerns\ModuleFilamentPlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;

class ProjectsPlugin implements Plugin
{
    use ModuleFilamentPlugin;

    public function getModuleName(): string
    {
        return 'Projects';
    }

    public function getId(): string
    {
        return 'projects';
    }

    public function boot(Panel $panel): void
    {
        // TODO: Implement boot() method.
    }
}
