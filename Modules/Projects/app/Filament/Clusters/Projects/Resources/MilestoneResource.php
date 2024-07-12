<?php

namespace Modules\Projects\Filament\Clusters\Projects\Resources;

use Modules\Projects\Filament\Clusters\Projects;
use Modules\Projects\Filament\Clusters\Projects\Resources\MilestoneResource\Pages;
use Modules\Projects\Filament\Clusters\Projects\Resources\MilestoneResource\RelationManagers;
use Modules\Projects\Models\Milestone;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MilestoneResource extends Resource
{
    protected static ?string $model = Milestone::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Projects::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->maxLength(30)->disabled(fn($record) => !$record)
                    ->readOnly()
                    ->placeholder('will be auto-generated')
                    ->default(null),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('project_id')
                    ->relationship('project', 'title')
                    ->required()->columnSpanFull(),
                Forms\Components\RichEditor::make('description')
                    ->required()->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->required()->options([
                        'OPEN' => 'Open',
                        'CLOSED' => 'Closed',
                    ])->default('OPEN'),
                Forms\Components\DatePicker::make('start_date')->native(false),
                Forms\Components\DatePicker::make('due_date')->native(false),
                Forms\Components\TextInput::make('progress')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()->sortable(),
                Tables\Columns\TextColumn::make('project.title')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('progress')
                    ->numeric()->formatStateUsing(fn($state) => $state . '%')
                    ->sortable(),
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
            'index' => Pages\ManageMilestones::route('/'),
        ];
    }
}
