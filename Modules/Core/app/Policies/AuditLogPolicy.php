<?php

namespace Modules\Core\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Core\Concerns\StandardPolicy;
use Modules\Core\Filament\Clusters\SystemAdministration\Resources\AuditLogResource;

class AuditLogPolicy
{
    use HandlesAuthorization, StandardPolicy;

    public function getResourceClass(): string
    {
        return AuditLogResource::class;
    }

    public function create(User $user): bool
    {
        return false;
    }
}
