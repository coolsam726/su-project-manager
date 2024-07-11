<?php

namespace Modules\Core\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Modules\Core\Models\ActivityLog;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

use function Modules\Core\Support\core;

/**
 * @mixin Model
 */
trait HasAudit
{
    use LogsActivity;

    public function creator(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'creator_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'updater_id');
    }

    public static function booted(): void
    {
        static::creating(function (Model $model) {
            if (core()->modelHasColumn($model, 'updater_id')) {
                $model->updater_id = auth()->check() ? auth()->id() : null;
            }
            if (core()->modelHasColumn($model, 'creator_id')) {
                $model->creator_id = auth()->check() ? auth()->id() : null;
            }
            if (core()->modelHasColumn($model, 'owner_id')) {
                $model->owner_id = auth()->check() ? auth()->id() : null;
            }

            if (core()->modelHasColumn($model, 'ip_address')) {
                $model->ip_address = core()->getCurrentIp();
            }
        });

        static::updating(function (Model $model) {
            if (core()->modelHasColumn($model, 'updater_id')) {
                $model->updater_id = auth()->check() ? auth()->id() : null;
            }
            if (core()->modelHasColumn($model, 'ip_address')) {
                $model->ip_address = core()->getCurrentIp();
            }
        });
    }

    public function tapActivity(ActivityLog|Activity $activity, string $eventName): void
    {
        if (auth()->check()) {
            $activity->{core()::TEAM_COLUMN} = \auth()->user()->getAttribute(core()::TEAM_COLUMN);
            Log::info($activity);
        }
    }

    public function getLogSubjectDetailsAttribute(): string
    {
        return str($this->getMorphClass())
            ->afterLast('\\')
            ->singular()
            ->kebab()
            ->title()
            ->replace(['-', '_'], ' ')
            ->append(' -> ')
            ->append(
                $this?->getAttribute('name')
                    ?: $this->getAttribute('title')
                    ?: $this?->getAttribute('code')
                        ?: $this?->getAttribute('id')
                            ?: '#'.$this->getKey()
            )->toString();
    }

    public function getActivitylogOptions(): LogOptions
    {
        $auth = core()->sysbot();
        if (auth()->check()) {
            $auth = auth()->user();
        }

        return LogOptions::defaults()
            ->logAll()->logOnlyDirty()
            ->logExcept(['created_at', 'updated_at'])
            ->useLogName($this->log_subject_details)
            ->setDescriptionForEvent(fn (string $eventName) => "{$auth?->username} {$eventName} {$this->log_subject_details}");
        // Chain fluent methods for configuration options
    }
}
