<?php

namespace Modules\Projects\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Kalnoy\Nestedset\NodeTrait;
use Modules\Core\Concerns\HasAudit;
use Modules\Core\Concerns\HasCode;
use Modules\Core\Concerns\HasTeam;
use Modules\Core\Models\User;
use Modules\Projects\Database\Factories\ProjectFactory;

class Project extends Model
{
    use HasFactory, HasCode, HasAudit, HasTeam, NodeTrait;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];

    protected static function newFactory(): ProjectFactory
    {
        return ProjectFactory::new();
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class,'department_id');
    }

    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(User::class,'project_manager_id');
    }
}
