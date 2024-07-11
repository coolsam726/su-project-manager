<?php

namespace Modules\Core\Filament;

use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Coolsam\Modules\Concerns\ModuleFilamentPlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Assets\Js;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Vite;
use Modules\Core\Filament\Clusters\SystemAdministration\Pages\LdapLogin;

class CorePlugin implements Plugin
{
    use ModuleFilamentPlugin;

    public function getModuleName(): string
    {
        return 'Core';
    }

    public function getId(): string
    {
        return 'core';
    }

    public function afterRegister(Panel $panel): void
    {
        $panel
            ->maxContentWidth('full')
            ->colors([
                //                'primary' => Color::Indigo,
                //                'primary' => '#02338D',
                'primary' => Color::rgb('rgb(2, 51, 160)'),
                'info' => Color::rgb('rgb(204, 156, 74)'),
            ])
            ->plugin(FilamentShieldPlugin::make())
            ->viteTheme('resources/css/filament/theme.css', 'build-core')
            ->assets([
                Js::make('app', Vite::asset('resources/assets/js/app.js', 'build-core')),
            ], '')
            ->renderHook(PanelsRenderHook::BODY_END, fn (): string => Blade::render('<livewire:modules.core.livewire.error-modal />'));

        $this->registerLogin($panel);

    }

    public function boot(Panel $panel): void
    {
    }

    private function registerLogin(Panel $panel)
    {
        if (config('core.ldap.enabled', false)) {
            $panel->login(LdapLogin::class);
        } else {
            $panel->login();
        }
    }
}
