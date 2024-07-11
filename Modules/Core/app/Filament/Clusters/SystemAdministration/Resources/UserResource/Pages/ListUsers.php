<?php

namespace Modules\Core\Filament\Clusters\SystemAdministration\Resources\UserResource\Pages;

use Filament\Actions;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Modules\Core\Filament\Clusters\SystemAdministration\Resources\UserResource;
use Modules\Core\Models\User;
use Modules\Core\Support\Users;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('sync-user')->label('Sync from AD')
                ->color('success')->icon('heroicon-o-user-plus')
                ->form([
                    TextInput::make('username')->label('Username')->required()->autofocus()->required(),
                ])
                ->authorize('create', User::class)
                ->action(fn ($data) => static::syncUser($data['username'])),
            Actions\CreateAction::make(),
        ];
    }

    public static function syncUser(string $username): void
    {
        try {
            $user = Users::make()->syncUser($username, is_numeric($username) ? 'students' : 'staff');
            Notification::make('success')->title('Sync Success')->body("Sync Successful. Name: $user->name")->send();
        } catch (\Throwable $exception) {
            Notification::make('error')->danger()->title('Sync Error')->body($exception->getMessage())->send();
        }
    }
}
