<?php

namespace Modules\Projects\Filament\Clusters\Projects\Resources;

use Modules\Projects\Filament\Clusters\Projects;
use Modules\Projects\Filament\Clusters\Projects\Resources\DepartmentResource\Pages;
use Modules\Projects\Filament\Clusters\Projects\Resources\DepartmentResource\RelationManagers;
use Modules\Projects\Models\Department;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Projects::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->maxLength(30)
                    ->readOnly()
                    ->visible(fn($record) => $record)
                    ->default(null),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)->live(onBlur: true)->afterStateUpdated(function ($state, Forms\Set $set) {
                        $set('short_name', \Coolsam\Modules\Support\projects()->makeShortName($state));
                    }),
                Forms\Components\TextInput::make('short_name')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Toggle::make('is_active')
                    ->required()->default(true),
                Forms\Components\Select::make('parent_id')
                    ->relationship('parent','name')
                    ->default(null)->searchable()->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid(['sm' => 1, 'md' => 1, 'lg' => 2, 'xl' => 3])
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\TextColumn::make('code')
                            ->searchable(),
                        Tables\Columns\TextColumn::make('short_name')
                            ->searchable(),
                        Tables\Columns\TextColumn::make('name')
                            ->searchable(),
                        Tables\Columns\IconColumn::make('is_active')
                            ->boolean(),
                        Tables\Columns\TextColumn::make('created_at')
                            ->dateTime()
                            ->sortable()
                            ->toggleable(isToggledHiddenByDefault: true),
                        Tables\Columns\TextColumn::make('updated_at')
                            ->dateTime()
                            ->sortable()
                            ->toggleable(isToggledHiddenByDefault: true),
                    ])
                ])
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDepartments::route('/'),
        ];
    }
}
