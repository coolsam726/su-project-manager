<x-filament-panels::page>
    <div class="flex items-center justify-end gap-2">
        {{ $this->newProjectAction() }}
        {{ $this->departmentFilterAction() }}
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