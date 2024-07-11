<?php

namespace Modules\Core\Providers;

use Filament\Facades\Filament;
use Filament\Support\Facades\FilamentView;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Modules\Core\Console\InstallCommand;
use Modules\Core\Console\ParentTriggerInstall;
use Modules\Core\Console\TriggersInstallCommand;
use Modules\Core\Console\TriggersRefreshCommand;
use Modules\Core\Support\Core;

class CoreServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'Core';

    protected string $moduleNameLower = 'core';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
    }

    /**
     * Register the service provider.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->overrideConfigs();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'database/migrations'));
        $this->loadMigrationsFrom(module_path($this->moduleName, 'database/migrations/settings'));
        $this->registerTenantSwitcher();
        $this->registerMacros();
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        $this->commands([
            InstallCommand::class,
            TriggersRefreshCommand::class,
            TriggersInstallCommand::class,
            ParentTriggerInstall::class,
        ]);
    }

    /**
     * Register command Schedules.
     */
    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('inspire')->hourly();
        // });
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->moduleNameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->moduleName, 'lang'), $this->moduleNameLower);
            $this->loadJsonTranslationsFrom(module_path($this->moduleName, 'lang'));
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $this->publishes([module_path($this->moduleName, 'config/config.php') => config_path($this->moduleNameLower.'.php')], 'config');
        $this->mergeConfigFrom(module_path($this->moduleName, 'config/config.php'), $this->moduleNameLower);

        //        $this->publishes([module_path($this->moduleName, 'config/filament-shield.php') => config_path('filament-shield.php')], 'config');
        $this->mergeConfigFrom(module_path($this->moduleName, 'config/filament-shield.php'), 'core-filament-shield');

        //        $this->publishes([module_path($this->moduleName, 'config/settings.php') => config_path('settings.php')], 'config');
        $this->mergeConfigFrom(module_path($this->moduleName, 'config/settings.php'), 'core-settings');

        //        $this->publishes([module_path($this->moduleName, 'config/permission.php') => config_path('permission.php')], 'config');
        $this->mergeConfigFrom(module_path($this->moduleName, 'config/permission.php'), 'core-permission');

        //        $this->publishes([module_path($this->moduleName, 'config/ldap.php') => config_path('ldap.php')], 'config');
        $this->mergeConfigFrom(module_path($this->moduleName, 'config/ldap.php'), 'ldap');

        $this->mergeConfigFrom(module_path($this->moduleName, 'config/activitylog.php'), 'activitylog');
    }

    public function overrideConfigs(): void
    {
        Log::info('Overriding configs');
        if (\config('core.overrides.permission')) {
            Config::set('permission', require module_path($this->moduleName, 'config/permission.php'));
        }

        if (\config('core.overrides.filament-shield')) {
            Config::set('filament-shield', require module_path($this->moduleName, 'config/filament-shield.php'));
        }

        if (\config('core.overrides.settings')) {
            Config::set('settings', require module_path($this->moduleName, 'config/settings.php'));
        }

        if (\config('core.overrides.ldap')) {
            Config::set('ldap', require module_path($this->moduleName, 'config/ldap.php'));
            // merge user providers
            $providers = Config::get('core.ldap.providers', []);
            Config::set('auth.providers', array_merge(Config::get('auth.providers', []), $providers));
        }
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->moduleNameLower);
        $sourcePath = module_path($this->moduleName, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->moduleNameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);

        $componentNamespace = str_replace('/', '\\', config('modules.namespace').'\\'.$this->moduleName.'\\'.ltrim(config('modules.paths.generator.component-class.path'), config('modules.paths.app_folder', '')));
        Blade::componentNamespace($componentNamespace, $this->moduleNameLower);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array<string>
     */
    public function provides(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->moduleNameLower)) {
                $paths[] = $path.'/modules/'.$this->moduleNameLower;
            }
        }

        return $paths;
    }

    private function registerMacros(): void
    {
        Blueprint::macro('code', function ($length = 30, bool $uniquePerTeam = false) {
            $return = $this->string('code', $length)->nullable();
            if ($uniquePerTeam) {
                $this->unique(['code', Core::TEAM_COLUMN]);
            } else {
                $return->unique();
            }

            return $return;
        });

        Blueprint::macro('team', function (?string $after = null) {
            $def = $this->foreignId(Core::TEAM_COLUMN)->nullable();
            if ($after) {
                $def = $def->after($after);
            }

            return $def->constrained('teams')->nullOnDelete();
        });

        Blueprint::macro('dropTeam', function () {
            return $this->dropConstrainedForeignId(Core::TEAM_COLUMN);
        });
        Blueprint::macro('recordStatus', function (array $statuses = []) {
            return $this->enum('record_status', array_merge(['draft', 'posted', 'closed', 'cancelled'], $statuses))->default('draft');
        });

        Blueprint::macro('creator', function () {
            return $this->foreignId('creator_id')->nullable()->constrained('users')->nullOnDelete();
        });

        Blueprint::macro('updater', function () {
            return $this->foreignId('updater_id')->nullable()->constrained('users')->nullOnDelete();
        });

        Blueprint::macro('active', function (bool $default = true) {
            return $this->boolean('is_active')->default($default);
        });

        Blueprint::macro('immutable', function (bool $default = false) {
            return $this->boolean('is_immutable')->default($default);
        });

        Blueprint::macro('audit', function () {
            $this->creator();
            $this->updater();
            $this->ipAddress()->nullable();
            $this->immutable();
        });

        Blueprint::macro('dropAudit', function () {
            $this->dropConstrainedForeignId('creator_id');
            $this->dropConstrainedForeignId('updater_id');
            $this->dropColumn(['ip_address', 'is_immutable']);
        });
    }

    private function registerTenantSwitcher()
    {
        if (config('core.multitenancy.enabled', false)) {
            // Render Team Switcher
            FilamentView::registerRenderHook(
                'panels::user-menu.before',
                fn () => view('core::filament.tenant-switcher')
            );
        }
    }
}
