<x-filament-panels::page :full-height="true">
    <style>
        .fi-main {
            padding: 0 0 0 0 !important;
        }
        .fi-body {
            background-color: white !important;
            padding-bottom: 8px;
        }
        .dark .fi-body {
            background-color: rgb({{\Filament\Support\Colors\Color::Gray[700]}}) !important;
        }
        .fi-header {
            margin: 1rem 2rem 1rem 2rem !important;
        }
    </style>
    <div class="bg-white overflow-x-auto relative h-full dark:bg-gray-700">
        <div x-data wire:ignore.self class="md:flex w-full max-h-[calc(100vh-170px)] overflow-x-auto md:overflow-y-hidden gap-4 pb-4">
            @foreach($statuses as $status)
                @include(static::$statusView)
            @endforeach

            <div wire:ignore>
                @include(static::$scriptsView)
            </div>
            @unless($disableEditModal)
                <x-filament-kanban::edit-record-modal/>
            @endunless
        </div>
    </div>
</x-filament-panels::page>