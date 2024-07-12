<?php

namespace Modules\Projects\Filament\Clusters\Projects\Resources\ProjectResource\Pages;

use Modules\Projects\Filament\Clusters\Projects\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProject extends CreateRecord
{
    protected static string $resource = ProjectResource::class;
}
