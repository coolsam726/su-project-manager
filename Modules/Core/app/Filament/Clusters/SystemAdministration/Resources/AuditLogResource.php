<?php

namespace Modules\Core\Filament\Clusters\SystemAdministration\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Core\Filament\Clusters\SystemAdministration;
use Modules\Core\Filament\Clusters\SystemAdministration\Resources\AuditLogResource\Pages;
use Modules\Core\Models\AuditLog;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static ?int $navigationSort = 1000;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = SystemAdministration::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->default('NULL'),
                Forms\Components\TextInput::make('table_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('action')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('record_id')
                    ->required()
                    ->numeric(),
                Forms\Components\Textarea::make('changed_values')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('old_values')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('new_values')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('ip_address')
                    ->maxLength(255)
                    ->default('NULL'),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Fieldset::make('Log Details')->columns(3)->schema([
                TextEntry::make('user.name')->label('User'),
                TextEntry::make('action'),
                TextEntry::make('table_name'),
                TextEntry::make('record_id')->label('Record ID'),
                //                TextEntry::make('ip_address')->label('IP Address'),
                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('updated_at')->dateTime(),
            ]),
            KeyValueEntry::make('changed_values')
                ->columnSpanFull()
                ->visible(fn ($state) => $state && count($state))
                ->getStateUsing(fn (AuditLog $record) => $record->getChangedValues()),
            KeyValueEntry::make('old_values')
                ->columnSpanFull()
                ->visible(fn ($state) => $state && count($state))
                ->getStateUsing(fn (AuditLog $record) => $record->getOldValues()),
            KeyValueEntry::make('new_values')
                ->columnSpanFull()
                ->visible(fn ($state) => $state && count($state))
                ->getStateUsing(fn (AuditLog $record) => $record->getNewValues()),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->searchable()
                    ->sortable()->label('ID'),
                Tables\Columns\TextColumn::make('action')
                    ->searchable(),
                Tables\Columns\TextColumn::make('table_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Responsible User')
                    ->sortable(),
                Tables\Columns\TextColumn::make('record_id')
                    ->numeric()
                    ->label('Record ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->searchable()->label('IP Address'),
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
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
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
            'index' => Pages\ManageAuditLogs::route('/'),
        ];
    }
}
