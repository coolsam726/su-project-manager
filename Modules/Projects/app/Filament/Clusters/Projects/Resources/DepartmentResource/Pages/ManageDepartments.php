<?php

namespace Modules\Projects\Filament\Clusters\Projects\Resources\DepartmentResource\Pages;

use Modules\Projects\Filament\Clusters\Projects\Resources\DepartmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageDepartments extends ManageRecords
{
    protected static string $resource = DepartmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
