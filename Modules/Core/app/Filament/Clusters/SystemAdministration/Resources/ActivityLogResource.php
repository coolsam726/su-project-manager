<?php

namespace Modules\Core\Filament\Clusters\SystemAdministration\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Core\Filament\Clusters\SystemAdministration;
use Modules\Core\Filament\Clusters\SystemAdministration\Resources\ActivityLogResource\Pages;
use Modules\Core\Models\ActivityLog;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = SystemAdministration::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('log_name')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('subject_type')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('event')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('subject_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\TextInput::make('causer_type')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('causer_id')
                    ->numeric()
                    ->default(null),
                Forms\Components\Textarea::make('properties')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('batch_uuid'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->searchable()->sortable()->label('Log ID'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Log Date')
                    ->sortable(),
                Tables\Columns\TextColumn::make('log_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('event')
                    ->searchable(),
                Tables\Columns\TextColumn::make('causer.name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('subject.id')->searchable()->sortable()->getStateUsing(
                    fn ($record) => str($record->subject_type)
                        ->afterLast('\\')
                        ->singular()
                        ->kebab()
                        ->title()
                        ->replace(['-', '_'], ' ')
                        ->append(' -> ')
                        ->append(
                            $record->subject?->getAttribute('name')
                                ?: $record->subject?->getAttribute('title')
                                ?: $record->subject?->getAttribute('code')
                                ?: $record->subject?->getAttribute('id')
                            ?: "#$record->subject_id"
                        )
                ),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->defaultSort('id', 'desc')
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
            'index' => Pages\ManageActivityLogs::route('/'),
        ];
    }
}
