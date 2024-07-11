<?php

namespace Modules\Core\Filament\Clusters\SystemAdministration\Resources\AuditLogResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Modules\Core\Filament\Clusters\SystemAdministration\Resources\AuditLogResource;

class ManageAuditLogs extends ManageRecords
{
    protected static string $resource = AuditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
