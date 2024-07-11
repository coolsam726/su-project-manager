<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Team;

class TeamsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //create default team
        Team::query()->firstOrCreate(['code' => 'DEFAULT'], [
            'name' => 'DEFAULT TEAM',
            'description' => 'Default Team',
            'is_immutable' => true,
            'is_active' => true,
        ]);
    }
}
