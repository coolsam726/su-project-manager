<?php

namespace Modules\Projects\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Kalnoy\Nestedset\NodeTrait;
use Modules\Core\Concerns\HasAudit;
use Modules\Core\Concerns\HasCode;
use Modules\Core\Concerns\HasTeam;
use Modules\Core\Models\User;
use Modules\Projects\Database\Factories\TaskFactory;

class Task extends Model
{
    use HasFactory, HasAudit, HasCode, NodeTrait;

    protected $casts = [
        'labels' => 'array',
        'start_date' => 'date',
        'due_date' => 'date',
        'todos' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];

    protected static function newFactory(): TaskFactory
    {
        return TaskFactory::new();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class,'milestone_id');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class,'task_assignees')->withTimestamps();
    }

    public function getDurationAttribute()
    {
        return $this->start_date->diffInDays($this->due_date);
    }
}
