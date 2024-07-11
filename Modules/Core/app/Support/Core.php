<?php

namespace Modules\Core\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Concerns\HasRecordStatus;
use Modules\Core\Facades\Triggers as Tr;
use Modules\Core\Models\Team;

class Core
{
    const TEAM_COLUMN = 'team_id';

    const RECORD_STATUS = 'record_status';

    public function replaceInFile($file, $search, $replace): void
    {
        $content = file_get_contents($file);
        $content = str_replace($search, $replace, $content);
        file_put_contents($file, $content);
        exec('./vendor/bin/pint '.$file);
    }

    public function getTablesList()
    {
        $seconds = 60;

        return Cache::remember('db_tables', $seconds, callback: function () {
            return Schema::getTableListing();
        });
    }

    public function getColumns(string $table)
    {
        $ttl = 60;

        return Cache::remember('db_columns_'.$table, $ttl, callback: function () use ($table) {
            return Schema::getColumnListing($table);
        });
    }

    public function generatePrefix(string $tableName): string
    {
        $prefix = '';
        $parts = explode('_', $tableName);
        foreach ($parts as $part) {
            $prefix .= $part[0];
        }

        return strtoupper($prefix);
    }

    public function getCurrentIp(): array|string|null
    {
        return request()->header('X-FORWARDED-FOR', request()->ip());
    }

    public function installAuditLogTriggers(string $table): void
    {
        if ($table === 'audit_logs') {
            return;
        }
        ($q = Tr::auditLogInsertTrigger($table)) && DB::unprepared($q);
        ($q = Tr::auditLogUpdateTrigger($table)) && DB::unprepared($q);
        ($q = Tr::auditLogDeleteTrigger($table)) && DB::unprepared($q);
        ($q = Tr::immutableUpdateTrigger($table)) && DB::unprepared($q);
        ($q = Tr::immutableDeleteTrigger($table)) && DB::unprepared($q);
    }

    public function dropAuditLogTriggers(string $table): void
    {
        ($q = Tr::dropAuditLogInsertTrigger($table)) && DB::unprepared($q);
        ($q = Tr::dropAuditLogUpdateTrigger($table)) && DB::unprepared($q);
        ($q = Tr::dropAuditLogDeleteTrigger($table)) && DB::unprepared($q);
        ($q = Tr::immutableUpdateTrigger($table, drop: true)) && DB::unprepared($q);
        ($q = Tr::immutableDeleteTrigger($table, drop: true)) && DB::unprepared($q);
    }

    public function installCodeTriggers(string $table, int $padLength = 4): void
    {
        $q = Tr::codeTrigger($table, $padLength);
        $q && DB::unprepared($q);
    }

    public function dropCodeTriggers(string $table): void
    {
        $q = Tr::dropCodeTrigger($table);
        $q && DB::unprepared($q);
    }

    public function installImmutableCheckTrigger(array|string $tables = [], bool $drop = false): void
    {
        if ($tables && is_string($tables)) {
            $table = $tables;
            ($q = Tr::immutableUpdateTrigger($table, $drop)) && DB::unprepared($q);
            ($q = Tr::immutableDeleteTrigger($table, $drop)) && DB::unprepared($q);
        } else {
            if (! count($tables)) {
                $tables = core()->getTablesList();
            }
            foreach ($tables as $table) {
                $table = (array) $table;
                $table = array_values($table)[0];
                ($q = Tr::immutableUpdateTrigger($table, $drop)) && DB::getPdo()->exec($q);
                ($q = Tr::immutableDeleteTrigger($table, $drop)) && DB::getPdo()->exec($q);
            }
        }
    }

    public function installUserProfileTriggers(): void
    {
        ($q = Tr::usersAutoCreateProfile()) && DB::unprepared($q);
        ($q = Tr::usersUpdateOrCreateCreateProfile()) && DB::unprepared($q);
    }

    public function dropUserProfileTriggers(): void
    {
        ($q = Tr::usersAutoCreateProfile(drop: true)) && DB::unprepared($q);
        ($q = Tr::usersUpdateOrCreateCreateProfile(drop: true)) && DB::unprepared($q);
    }

    public function parentStatusCheckTriggers(string $table, string $parent_table, string $fk = 'parent_id', bool $drop = false): void
    {
        ($q = Tr::parentCheckBeforeInsertTrigger($table, $parent_table, $fk, $drop)) && DB::unprepared($q);
        ($q = Tr::parentCheckBeforeUpdateTrigger($table, $parent_table, $fk, $drop)) && DB::unprepared($q);
        ($q = Tr::parentCheckBeforeDeleteTrigger($table, $parent_table, $fk, $drop)) && DB::unprepared($q);
    }

    public function getSharedModels()
    {
        return config('core.multitenancy.shared_models', []);
    }

    public function makeTeamScope(Builder $query): void
    {
        if (! config('core.multitenancy.enabled', false)) {
            return;
        }
        if (in_array($query->getModel()->getMorphClass(), $this->getSharedModels())) {
            return;
        }
        if (Schema::hasColumn($query->getModel()->getTable(), core()::TEAM_COLUMN)) {
            $user = auth()->user();
            if (auth()->check() && $user->{core()::TEAM_COLUMN}) {
                $query->whereBelongsTo($user->team);
            } else {
                $query->whereNull(core()::TEAM_COLUMN);
            }
        }
    }

    public function default_team(): ?Team
    {
        return Team::whereCode('DEFAULT')->first();
    }

    public function current_team(): ?Team
    {
        return auth()->check() ? auth()->user()->team : null;
    }

    public function sysbot(): User|Model|null
    {
        return User::query()->where('username', '=', 'sysbot')->first();
    }

    public static function class_has_trait(mixed $class, string $trait): bool
    {
        $traits = class_uses_recursive($class);

        return in_array($trait, $traits);
    }

    public function model_has_record_status(Model|string $model): bool
    {
        return $this->class_has_trait($model, HasRecordStatus::class);
    }

    public function useGridView(): bool
    {
        // Check in local storage
        return boolval(Auth::user()?->setting('grid-view') ?? false);
    }

    public function setGridView(bool $value): void
    {
        Auth::user()?->updateSetting('grid-view', $value);
    }

    public function modelHasColumn(Model|string $model, string $column): bool
    {
        $table = $model instanceof Model ? $model->getTable() : $model::getModel()->getTable();

        return Schema::hasColumn($table, $column);
    }
}
