<?php

namespace Modules\Core\Console;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

use function Laravel\Prompts\confirm;
use function Modules\Core\Support\core;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $name = 'core:install';

    /**
     * The console command description.
     */
    protected $description = 'Install the Core Module.';

    public function handle(): void
    {
        // Check if the api route exists
        if (! file_exists(base_path('routes/api.php'))) {
            $this->error('The Laravel API feature does not appear to be enabled. Attempting to activate it...');
            $this->call('install:api');
        }

        // Enable API
        // optionally publish config files
        if (confirm('Do you want to publish the config files?', false)) {
            $this->call('vendor:publish', [
                '--provider' => "Modules\Core\Providers\CoreServiceProvider",
                '--tag' => 'config',
            ]);
        }

        // Change the Authenticatable import in App\Models\User
        if (confirm('Do you want to change the Authenticatable import in App\Models\User?', false)) {
            core()->replaceInFile(
                app_path('Models/User.php'),
                'Illuminate\Foundation\Auth\User as Authenticatable',
                'Modules\Core\Models\User as Authenticatable'
            );
            $this->info('Authenticatable import changed in App\Models\User.');
        }

        // Run migrations for the module
        if (confirm('Do you want to run the migrations for the module?', true)) {
            $this->call('module:migrate', ['module' => $this->getModuleName()]);
        }

        // Refresh triggers
        if (confirm('Do you want to refresh triggers for all tables?', true)) {
            $this->call('core:triggers:refresh');
        }

        // Run Seeders
        if (confirm('Do you want to seed initial data for the module?', true)) {
            $this->call('module:seed', ['module' => $this->getModuleName()]);
        }

        // Give sysadmin super admin role
        if (confirm('Do you want to give the sysadmin user the super admin role?', true)) {
            $this->call('shield:super-admin', ['--user' => User::query()->whereUsername('sysadmin')->first()?->id]);
        }

        // Generate all permissions
        if (confirm('Do you want to generate all permissions?', true)) {
            $this->call('shield:generate', ['--all' => true, '--option' => 'permissions']);
        }
    }

    /**
     * Get the console command arguments.
     */
    protected function getArguments(): array
    {
        return [];
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [];
    }

    private function getModuleName(): string
    {
        return 'Core';
    }
}
