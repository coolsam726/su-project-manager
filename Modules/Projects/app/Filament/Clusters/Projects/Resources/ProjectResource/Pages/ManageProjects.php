<?php

namespace Modules\Projects\Filament\Clusters\Projects\Resources\ProjectResource\Pages;

use Modules\Projects\Filament\Clusters\Projects\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageProjects extends ManageRecords
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
