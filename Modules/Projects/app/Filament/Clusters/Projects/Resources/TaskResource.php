<?php

namespace Modules\Projects\Filament\Clusters\Projects\Resources;

use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Modules\Projects\Filament\Clusters\Projects;
use Modules\Projects\Filament\Clusters\Projects\Resources\TaskResource\Pages;
use Modules\Projects\Filament\Clusters\Projects\Resources\TaskResource\RelationManagers;
use Modules\Projects\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

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
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\RichEditor::make('description')
                    ->columnSpanFull(),
                Forms\Components\Select::make('project_id')
                    ->relationship('project', 'title')
                    ->required()->live(onBlur: true)->afterStateUpdated(function ($state, Forms\Set $set) {
                        $set('milestone_id', null);
                        $set('parent_id', null);
                    }),
                Forms\Components\Select::make('milestone_id')
                    ->relationship('milestone', 'title', function (Builder $query, Forms\Get $get) {
                        $query->where('project_id', '=', $get('project_id'));
                    })
                    ->preload()
                    ->default(null),
                Forms\Components\DatePicker::make('start_date')->native(false),
                Forms\Components\DatePicker::make('due_date')->native(false),
                Forms\Components\TagsInput::make('labels')
                    ->columnSpanFull(),
                TableRepeater::make('todos')
                    ->headers([
                        Header::make('To-Do Item')->width('3/4'),
                        Header::make('Done?'),
                    ])
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required(),
                        Forms\Components\Checkbox::make('is_done')
                            ->extraAttributes(['class' => 'p-4'])
                            ->default(false),
                    ])
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->required()->default(true),
                Forms\Components\TextInput::make('progress')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Select::make('parent_id')
                    ->relationship('parent', 'name', function (Builder $query, Forms\Get $get) {
                        $query->where('project_id', '=', $get('project_id'));
                    })
                    ->default(null),
                Forms\Components\Select::make('priority')
                    ->required()->options([
                        'URGENT' => 'Urgent',
                        'HIGH' => 'High',
                        'MEDIUM' => 'Medium',
                        'LOW' => 'Low',
                    ])->default('MEDIUM'),
                Forms\Components\Select::make('assignees')->label('Assign')
                    ->label('Start typing to select a user...')
                    ->searchable(['username','email','name'])
                    ->relationship('assignees', 'name', function (Builder $query, $state) {
                        //
                })->multiple()->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('project.title')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('milestone.title')
                    ->numeric()
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
            'index' => Pages\ManageTasks::route('/'),
        ];
    }
}
