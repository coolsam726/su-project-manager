<?php

namespace Modules\Core\Models;

use Modules\Core\Concerns\HasAudit;

class Role extends \Spatie\Permission\Models\Role
{
    use HasAudit;

    protected $guarded = ['id'];
}
