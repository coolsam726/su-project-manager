<?php

namespace Modules\Projects\Filament\Clusters\Projects\Pages;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Modules\Projects\Filament\Clusters\Projects;
use Modules\Projects\Models\Department;
use Modules\Projects\Models\Project;
use Modules\Projects\Support\Enums\ProjectStatus;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;

class ProjectsOverview extends KanbanBoard implements HasActions
{
    use InteractsWithActions;

//    protected static ?string $cluster = Projects::class;
    protected static string $model = Project::class;
    protected static string $statusEnum = ProjectStatus::class;

    protected string $editModalWidth = '2xl';

    protected string $editModalSaveButtonLabel = 'Save';

    protected string $editModalCancelButtonLabel = 'Cancel';

    protected static string $view = 'projects::projects.kanban-board';

    protected static string $headerView = 'projects::projects.kanban-header';

    protected static string $recordView = 'projects::projects.kanban-record';

    protected static string $statusView = 'projects::projects.kanban-status';

    protected bool $editModalSlideOver = true;
    public bool $disableEditModal = false;

    public array $filters = [
        'department_id' => null,
        'include_descendants' => false,
    ];

    public function __construct()
    {
        $this->filters['department_id'] = $this->getDepartmentFilter();
        $this->filters['include_descendants'] = $this->getIncludeDescendantsFilter();
    }

    protected function setDepartmentFilter(int|null $value): void
    {
        session(['project_filters_department' => $value]);
    }

    protected function setIncludeDescendantsFilter(bool|null $value): void
    {
        session(['project_filters_include_descendants' => $value]);
    }

    protected function getDepartmentFilter()
    {
        return session('project_filters_department') ?? null;
    }

    protected function getIncludeDescendantsFilter(): bool
    {
        return (bool) session('project_filters_include_descendants') ?? false;
    }

    protected function records(): Collection
    {
        $query = $this->getEloquentQuery()
            ->when(method_exists(static::$model, 'scopeOrdered'), fn($query) => $query->ordered());
        if ($this->filters['department_id']) {
            $query->where(function ($q) {
                $q->where('department_id', '=', $this->filters['department_id']);
                if ($this->filters['include_descendants']) {
                    $q->orWhereIn('department_id',
                        Department::query()->descendantsOf($this->filters['department_id'])->pluck('id'));
                }
            });
        }
        return $query->when(method_exists(static::$model, 'scopeOrdered'), fn ($query) => $query->ordered())->get();
    }

    protected function getForms(): array
    {
        return [
            'form',
            'filtersForm',
        ];
    }

    public function filtersForm(Form $form): Form
    {
        return $form;
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->newProjectAction(),
            $this->departmentFilterAction()
        ];
    }
    public function getHeading(): string|Htmlable
    {
        return new HtmlString(Blade::render(
            <<<blade
            <h1 class="text-xl text-gray-900">Projects: <strong>{$this->getDepartmentPageHeading()}</strong></h1>
            blade
        ));
    }

    public function getSubheading(): string|Htmlable|null
    {
        if ($this->filters['department_id'] && $this->filters['include_descendants']) {
            return new HtmlString(Blade::render(
                <<<blade
                <p class="mt-1 text-sm text-gray-600">Including Sub-Departments/Sub-Sections</p>
                blade
            ));
        }
        return parent::getSubheading();
    }

    public function newProjectAction()
    {
        return CreateAction::make('newProject')
            ->label('New Project')
            ->model(Project::class)
            ->form([
                TextInput::make('title')->required(),
                RichEditor::make('description')->nullable(),
                Select::make('department_id')->label('Department')->default($this->filters['department_id'] ?: null)->options(
                    Department::query()->whereIsActive(true)->pluck('name', 'id')->toArray()
                )->searchable(),
                Select::make('status')
                    ->searchable()
                    ->default(ProjectStatus::Backlog->value)
                    ->options(collect(ProjectStatus::cases())->mapWithKeys(fn(ProjectStatus $status
                    ) => [$status->value => $status->name])),
            ])
            ->using(function ($data) {
                $record = new Project();
                $record->title = $data['title'];
                $record->description = $data['description'];
                $record->department_id = $data['department_id'];
                $record->status = $data['status'];
                $record->start_date = now()->toDate();
                $record->due_date = now()->addMonth()->toDate();
                $record->saveOrFail();
                return $record;
            });
    }

    public function departmentFilterAction()
    {
        return Action::make('departmentFilter')
            ->label('Filter by Departments')
            ->form([
                Select::make('department_id')
                    ->label('Department')
                    ->placeholder('Select Department')
                    ->helperText('clear this if you would like to see all projects under the current team.')
                    ->default($this->filters['department_id'] ?: null)
                    ->options(Department::query()
                        ->whereIsActive(true)
                        ->pluck('name', 'id')
                        ->toArray())->searchable(),
                Toggle::make('include_descendants')
                    ->helperText('Check this if you would like to view projects from the sections/sub-departments of the selected department/division.')
                    ->label('Include Sub-Departments as Well?')
                    ->default($this->filters['include_descendants']),
            ])->action(function ($data) {
                $this->filters['department_id'] = $data['department_id'];
                $this->filters['include_descendants'] = $data['include_descendants'];

                $this->setDepartmentFilter(intval($data['department_id']) ?? null);
                $this->setIncludeDescendantsFilter(boolval($data['include_descendants']) ?? false);
            });
    }

    public function getDepartmentPageHeading()
    {
        if (!$this->filters['department_id']) {
            return __('All Departments');
        }
        return Department::find($this->filters['department_id'])?->name ?? __('All Departments');
    }

    protected function getEditModalFormSchema(?int $recordId): array
    {
        return [
            TextInput::make('title')->required(),
            RichEditor::make('description')->nullable(),
            Select::make('department_id')->label('Department')->default($this->filters['department_id'] ?: null)->options(
                Department::query()->whereIsActive(true)->pluck('name', 'id')->toArray()
            )->searchable(),
            TextInput::make('progress')->minValue(0)->maxValue(100)->default(0),
            Select::make('status')
                ->searchable()
                ->default(ProjectStatus::Backlog->value)
                ->options(collect(ProjectStatus::cases())->mapWithKeys(fn(ProjectStatus $status
                ) => [$status->value => $status->name])),

        ];
    }
}
