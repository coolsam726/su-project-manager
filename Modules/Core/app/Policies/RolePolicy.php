<?php

namespace Modules\Core\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Core\Concerns\StandardPolicy;
use Modules\Core\Filament\Clusters\SystemAdministration\Resources\RoleResource;

class RolePolicy
{
    use HandlesAuthorization, StandardPolicy;

    public function getResourceClass(): string
    {
        return RoleResource::class;
    }
}
