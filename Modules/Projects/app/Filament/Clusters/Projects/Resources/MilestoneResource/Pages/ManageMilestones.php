<?php

namespace Modules\Projects\Filament\Clusters\Projects\Resources\MilestoneResource\Pages;

use Modules\Projects\Filament\Clusters\Projects\Resources\MilestoneResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageMilestones extends ManageRecords
{
    protected static string $resource = MilestoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
