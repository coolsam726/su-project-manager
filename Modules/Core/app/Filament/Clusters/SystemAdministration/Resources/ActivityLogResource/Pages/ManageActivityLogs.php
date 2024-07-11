<?php

namespace Modules\Core\Filament\Clusters\SystemAdministration\Resources\ActivityLogResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Modules\Core\Filament\Clusters\SystemAdministration\Resources\ActivityLogResource;

class ManageActivityLogs extends ManageRecords
{
    protected static string $resource = ActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
