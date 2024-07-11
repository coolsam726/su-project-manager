<?php

namespace Modules\Core\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\Team;
use Modules\Core\Support\Core;

use function Modules\Core\Support\core;

/**
 * @mixin Model
 */
trait HasTeam
{
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, Core::TEAM_COLUMN);
    }

    public function company(): BelongsTo
    {
        return $this->team();
    }

    public static function bootHasTeam(): void
    {
        static::creating(function (Model $model) {
            if (auth()->check() && ! $model->getAttribute(Core::TEAM_COLUMN)) {
                $model->setAttribute(Core::TEAM_COLUMN, auth()->user()->{Core::TEAM_COLUMN});
            }
        });

        // Add Global Scope
        if (auth()->check()) {
            static::addGlobalScope('team', function ($query) {
                core()->makeTeamScope($query);
            });
        }
    }
}
