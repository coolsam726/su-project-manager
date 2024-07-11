<?php

namespace Modules\Core\Filament\Clusters\SystemAdministration\Resources\UserResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Core\Filament\Clusters\SystemAdministration\Resources\UserResource;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
