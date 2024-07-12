<x-filament-panels::page>
    <div class="flex items-center flex-wrap justify-between gap-2">
        <div>
            <h1 class="text-xl text-gray-900">Projects under <strong>{{$this->getDepartmentPageHeading()}}</strong></h1>
            @if($this->filters['department_id']  && $this->filters['include_descendants'])
                <p class="mt-1 text-sm text-gray-600">{{ __('Including Sub-Departments/Sub-Sections') }}</p>
            @endif
        </div>
        <div class="flex items-center flex-wrap gap-2">
            {{ $this->newProjectAction() }}
            {{ $this->departmentFilterAction() }}
        </div>
    </div>
    <div x-data wire:ignore.self class="md:flex min-h-full overflow-x-auto overflow-y-hidden gap-4 pb-4">
        @foreach($statuses as $status)
            @include(static::$statusView)
        @endforeach

        <div wire:ignore>
            @include(static::$scriptsView)
        </div>
    </div>

    @unless($disableEditModal)
        <x-filament-kanban::edit-record-modal/>
    @endunless
    <x-filament-actions::modals />
</x-filament-panels::page>