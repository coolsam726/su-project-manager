<?php

namespace Modules\Core\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Core\Concerns\StandardPolicy;
use Modules\Core\Filament\Clusters\SystemAdministration\Resources\UserResource;

class UserPolicy
{
    use HandlesAuthorization, StandardPolicy;

    public function getResourceClass(): string
    {
        return UserResource::class;
    }
}
