<?php

namespace Modules\Core\Filament\Clusters\SystemAdministration\Resources\UserResource\Pages;

use Filament\Actions;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Pages\Concerns\HasSubNavigation;
use Filament\Resources\Pages\ViewRecord;
use Modules\Core\Filament\Clusters\SystemAdministration\Resources\UserResource;

class ViewUser extends ViewRecord
{
    use HasSubNavigation;

    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Tabs::make()->schema([
                Tabs\Tab::make('Account Details')->schema([
                    TextEntry::make('code'),
                    TextEntry::make('username'),
                    TextEntry::make('email'),
                    TextEntry::make('name'),
                    TextEntry::make('domain')->label('LDAP Domain'),
                    TextEntry::make('uac')->label('LDAP User Account Control')->formatStateUsing(fn ($state) => $state ?: '-'),
                    TextEntry::make('email_verified_at')->formatStateUsing(fn ($state) => $state ?: '-'),
                    IconEntry::make('is_active')->boolean(),
                    IconEntry::make('is_immutable')->boolean(),
                ]),
            ])->columns(3)->persistTabInQueryString(),
        ])->columns(1);
    }
}
