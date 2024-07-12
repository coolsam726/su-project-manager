<?php

namespace Modules\Projects\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Concerns\HasAudit;
use Modules\Projects\Database\Factories\TaskLinkFactory;

class TaskLink extends Model
{
    use HasFactory, HasAudit;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];

    protected static function newFactory(): TaskLinkFactory
    {
        return TaskLinkFactory::new();
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'source_id');
    }
    public function target(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'target_id');
    }
}
