<?php

namespace Modules\Core\Console;

use Illuminate\Console\Command;

use function Laravel\Prompts\select;
use function Modules\Core\Support\utils;

class ParentTriggerInstall extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'core:parent:trigger {table?}';

    /**
     * The console command description.
     */
    protected $description = 'Install Trigger to validate the parent column on a descendant table.';

    public function handle(): int
    {
        $table = $this->argument('table') ?? select('Select the table to install the trigger on',
            collect($this->getDescendants())->mapWithKeys(fn ($v, $k) => [$k => $k])
                ->prepend('All Tables', 'all')->toArray(),
            default: 'all', required: true
        );
        if ($table === 'all') {
            $this->alert('Installing Parent integrity check triggers on all tables.');
            foreach ($this->getDescendants() as $table => $descendant) {
                [$parent,$fk] = $descendant;
                utils()->parentStatusCheckTriggers($table, $parent, $fk);
                $this->info("Parent integrity check trigger installed on $table");
            }
            $this->alert('DONE.');

            return self::SUCCESS;
        } else {
            [$parent,$fk] = $this->getDescendants()[$table];
            $this->alert("Installing trigger on $table");
            utils()->parentStatusCheckTriggers($table, $parent, $fk);
            $this->alert("Trigger installed on $table");
        }

        return self::SUCCESS;
    }

    public function getDescendants(): array
    {
        return [
            // 'table_name' => ['parent_table', 'foreign_key'],
            'user_profiles' => ['users', 'user_id'],
        ];
    }
}
