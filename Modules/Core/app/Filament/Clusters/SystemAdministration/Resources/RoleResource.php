<?php

namespace Modules\Core\Filament\Clusters\SystemAdministration\Resources;

use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Modules\Core\Filament\Clusters\SystemAdministration;
use Modules\Core\Filament\Clusters\SystemAdministration\Resources\RoleResource\Pages;
use Modules\Core\Models\Role;

class RoleResource extends \BezhanSalleh\FilamentShield\Resources\RoleResource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = SystemAdministration::class;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('name')
                            ->searchable()
                            ->sortable()
                            ->formatStateUsing(fn ($state) => Str::of($state)->title()->replace('_', ' '))
                            ->extraAttributes(['class' => 'font-black']),
                        Tables\Columns\TextColumn::make('guard_name')
                            ->prefix('Guard: ')
                            ->searchable()->sortable()->extraAttributes(['class' => 'text-sm']),
                    ]),
                ]),
                Tables\Columns\Layout\Panel::make([
                    Tables\Columns\TextColumn::make('name')->searchable()->sortable()->prefix('Name: '),
                    Tables\Columns\TextColumn::make('guard_name')->searchable()->sortable()->prefix('Guard Name: '),
                    Tables\Columns\TextColumn::make('created_at')->dateTime()->prefix('Created At: ')->sortable()->toggleable(isToggledHiddenByDefault: true),
                    Tables\Columns\TextColumn::make('updated_at')->dateTime()->prefix('Updated At: ')->sortable()->toggleable(isToggledHiddenByDefault: true),
                ])->collapsible()->collapsed(),
            ])->contentGrid(['lg' => 2, 'xl' => 3])
            ->filters([
                //
            ])
            ->actions([
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('filament-shield::filament-shield.resource.label.role');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-shield::filament-shield.resource.label.roles');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Access Control';
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-shield::filament-shield.nav.role.label');
    }

    public static function getNavigationIcon(): string
    {
        return __('filament-shield::filament-shield.nav.role.icon');
    }

    public static function getNavigationSort(): ?int
    {
        return Utils::getResourceNavigationSort();
    }

    public static function getSlug(): string
    {
        return Utils::getResourceSlug();
    }
}
