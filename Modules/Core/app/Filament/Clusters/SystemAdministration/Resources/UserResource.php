<?php

namespace Modules\Core\Filament\Clusters\SystemAdministration\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Core\Filament\Clusters\SystemAdministration;
use Modules\Core\Filament\Clusters\SystemAdministration\Resources\UserResource\Pages;
use Modules\Core\Filament\Clusters\SystemAdministration\Resources\UserResource\Pages\ListUsers;
use Modules\Core\Models\User;
use Modules\Core\Support\Core;
use Modules\Core\Support\Users;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Access Control';

    protected static ?int $navigationSort = 1;

    protected static ?string $cluster = SystemAdministration::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make()->schema([
                    Forms\Components\Tabs\Tab::make('Account Details')->schema([
                        Forms\Components\Section::make()->schema([
                            Forms\Components\Fieldset::make('Basic Details')->schema([
                                Forms\Components\TextInput::make('username')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Checkbox::make('is_active')->default(true),
                                Forms\Components\Radio::make(Core::TEAM_COLUMN)
                                    ->label('Current Team')
                                    ->inline()
                                    ->options(fn ($record) => $record?->teams->mapWithKeys(fn ($team) => [$team->id => $team->name])),
                            ])->columns(['lg' => 3, 'xl' => 4]),
                        ]),
                        Forms\Components\Section::make('Teams')->schema([
                            Forms\Components\CheckboxList::make('teams')
                                ->relationship('teams', 'name')
                                ->columns(['md' => 2, 'lg' => 4]),
                        ]),
                        Forms\Components\Section::make('Roles')->schema([
                            Forms\Components\CheckboxList::make('roles')
                                ->relationship('roles', 'name')
                                ->getOptionLabelFromRecordUsing(fn ($record) => Str::of($record->name)->title()->replace(['-', '_'], ' '))
                                ->columns(['md' => 2, 'lg' => 4]),
                        ]),
                    ]),
                ])->persistTabInQueryString()
                    ->id('user-form-tabs'),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('username')
                    ->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('team.name')->searchable()->sortable(),
                Tables\Columns\CheckboxColumn::make('is_active')->disabled(fn ($record) => auth()->user()->cant('update', $record)),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('sync')
                    ->color('success')
                    ->icon('heroicon-o-user-plus')
                    ->authorize('update')
                    ->action(fn ($record) => ListUsers::syncUser($record->getAttribute('username'))),
                Tables\Actions\Action::make('change-password')->icon('heroicon-o-key')->authorize('update')
                    ->form([
                        Forms\Components\TextInput::make('new_password')->required()->password()->confirmed(),
                        Forms\Components\TextInput::make('new_password_confirmation')->password(),
                    ])
                    ->action(function (User $record, array $data) {
                        $record->password = Hash::make($data['new_password']);
                        $record->saveOrFail();
                        Notification::make('success')->title('Password Updated')->body('The password has been updated successfully.')->send();
                    }),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                    Tables\Actions\BulkActionGroup::make([
                        Tables\Actions\DeleteBulkAction::make(),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    private function syncUser(string $username): void
    {
        try {
            $user = Users::make()->syncUser($username, is_numeric($username) ? 'students' : 'staff');
            Notification::make('success')->title('Sync Success')->body("Sync Successful. Name: $user->name")->send();
        } catch (\Throwable $exception) {
            Notification::make('error')->danger()->title('Sync Error')->body($exception->getMessage())->send();
        }
    }
}
