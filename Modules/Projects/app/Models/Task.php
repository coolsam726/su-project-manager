<?php

namespace Modules\Projects\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Kalnoy\Nestedset\NodeTrait;
use Modules\Core\Concerns\HasAudit;
use Modules\Core\Concerns\HasCode;
use Modules\Core\Concerns\HasTeam;
use Modules\Projects\Database\Factories\TaskFactory;

class Task extends Model
{
    use HasFactory, HasAudit, HasCode, NodeTrait;

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
}
