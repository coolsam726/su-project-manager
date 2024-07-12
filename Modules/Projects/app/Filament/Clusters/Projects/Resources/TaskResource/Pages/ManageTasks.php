<?php

namespace Modules\Projects\Filament\Clusters\Projects\Resources\TaskResource\Pages;

use Modules\Projects\Filament\Clusters\Projects\Resources\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageTasks extends ManageRecords
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
