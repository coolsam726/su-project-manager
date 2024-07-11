<?php

namespace Modules\Core\Filament\Clusters\SystemAdministration\Resources\RoleResource\Pages;

use Filament\Actions;
use Modules\Core\Filament\Clusters\SystemAdministration\Resources\RoleResource;

class EditRole extends \BezhanSalleh\FilamentShield\Resources\RoleResource\Pages\EditRole
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
