<?php

namespace Modules\Projects\Filament\Clusters\Projects\Resources;

use Modules\Projects\Filament\Clusters\Projects;
use Modules\Projects\Filament\Clusters\Projects\Resources\ProjectResource\Pages;
use Modules\Projects\Filament\Clusters\Projects\Resources\ProjectResource\RelationManagers;
use Modules\Projects\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $cluster = Projects::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->placeholder('Will be generated automatically.')
                    ->maxLength(30)->disabled(fn($record) => !$record)->readOnly()
                    ->default(null),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\RichEditor::make('description')
                    ->columnSpanFull(),
                Forms\Components\Select::make('department_id')
                    ->relationship('department', 'name')
                    ->default(null)->columnSpanFull(),
                Forms\Components\DatePicker::make('start_date')->native(false),
                Forms\Components\DatePicker::make('end_date')->native(false),
                Forms\Components\TextInput::make('color')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\Select::make('parent_id')
                    ->relationship('parent','title')
                    ->default(null),
                Forms\Components\Select::make('project_manager_id')
                    ->placeholder('Start typing to select a user...')
                    ->relationship('projectManager','name')
                    ->default(null),
                Forms\Components\FileUpload::make('image')
                    ->image(),
                Forms\Components\Toggle::make('is_active')
                    ->required()->default(true)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid(['md' => 1, 'lg' => 2, 'xl' => 3])
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\Layout\Stack::make([
                        Tables\Columns\ImageColumn::make('image'),
                        Tables\Columns\TextColumn::make('code')
                            ->searchable(),
                        Tables\Columns\TextColumn::make('title')
                            ->searchable(),
                        Tables\Columns\TextColumn::make('department.short_name')
                            ->numeric()
                            ->sortable(),
                        Tables\Columns\TextColumn::make('progress')
                            ->numeric()->numeric()->formatStateUsing(fn($state) => $state . '%')
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
            'index' => Pages\ManageProjects::route('/'),
        ];
    }
}
