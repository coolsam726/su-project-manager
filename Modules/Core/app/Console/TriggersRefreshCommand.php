<?php

namespace Modules\Core\Console;

use Illuminate\Console\Command;

use function Modules\Core\Support\core;

class TriggersRefreshCommand extends Command
{
    protected $name = 'core:triggers:refresh';

    protected $description = 'Re-install Database Triggers that matter.';

    public function handle(): void
    {
        $tables = core()->getTablesList();
        // First, store all columns in cache
        foreach ($tables as $table) {
            core()->getColumns($table);
        }
        //        exit(0);
        //        $tables = ['users','teams','roles','permissions','audit_logs'];
        collect($tables)->values()->filter(fn (string $col) => ! in_array($col, [
            'cache', 'cache_locks', 'migrations', 'sessions', 'failed_jobs', 'jobs', 'password_resets',
            'personal_access_tokens', 'job_batches',
        ]))->map(fn ($table) => $this->call('core:triggers:install', ['table' => $table]));

        // Install Parent Triggers
        //        $this->call('core:parent:trigger', ['table' => 'all']);
    }

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
}
