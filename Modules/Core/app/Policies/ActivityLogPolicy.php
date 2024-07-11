<?php

namespace Modules\Core\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Core\Concerns\StandardPolicy;
use Modules\Core\Filament\Clusters\SystemAdministration\Resources\ActivityLogResource;

class ActivityLogPolicy
{
    use HandlesAuthorization, StandardPolicy;

    public function getResourceClass(): string
    {
        return ActivityLogResource::class;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user): bool
    {
        return false;
    }

    public function delete(User $user): bool
    {
        return false;
    }

    public function restore(User $user): bool
    {
        return false;
    }

    public function forceDelete(User $user): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }
}
