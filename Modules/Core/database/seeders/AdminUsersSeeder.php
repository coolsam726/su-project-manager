<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Core\Models\User;

use function Modules\Core\Support\utils;

class AdminUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultTeam = utils()->default_team();
        $sysbot = User::query()->firstOrCreate(['username' => 'sysbot'], [
            'code' => 'SYSBOT',
            'email' => 'bot@system',
            'team_id' => $defaultTeam?->id,
            'name' => 'System Bot',
            'password' => bcrypt(Str::random(32)),
            'is_active' => false,
        ]);
        // Make the user immutable
        $sysbot->update(['is_immutable' => true]);
        Auth::login($sysbot);
        $admin = User::query()->firstOrCreate(['username' => 'sysadmin'], [
            'code' => 'SYSADMIN',
            'email' => 'admin@system',
            'team_id' => $defaultTeam?->id,
            'name' => 'System Admin',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        Auth::logout();
    }
}
