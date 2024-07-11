<?php

namespace Modules\Core\Filament\Clusters\SystemAdministration\Resources\TeamResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Modules\Core\Filament\Clusters\SystemAdministration\Resources\TeamResource;

class ManageTeams extends ManageRecords
{
    protected static string $resource = TeamResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
