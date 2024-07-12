<?php

namespace Modules\Projects\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Concerns\HasAudit;
use Modules\Core\Concerns\HasCode;
use Modules\Projects\Database\Factories\MilestoneFactory;
use Modules\Projects\Filament\Clusters\Projects;

class Milestone extends Model
{
    use HasFactory, HasAudit, HasCode;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];

    protected static function newFactory(): MilestoneFactory
    {
        return MilestoneFactory::new();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class,'project_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class,'milestone_id');
    }
}
