@if(auth()->user()?->teams?->count() > 1)
    <x-filament::modal>
        <x-slot name="trigger">
            <x-filament::button icon="heroicon-s-arrows-right-left" icon-position="after">
                {{auth()->user()->team?->name ?? 'NO TEAM'}}
            </x-filament::button>
        </x-slot>

        <livewire:forms.switch-team/>
    </x-filament::modal>
@else
    <x-filament::button disabled color="gray" class="font-black text-sm">
        {{ auth()->user()->team?->name ?: 'No Team Selected' }}
    </x-filament::button>
@endif