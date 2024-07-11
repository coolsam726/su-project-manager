<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Modules\Core\Support\Core;

class SwitchTeam extends Component implements HasForms
{
    use InteractsWithFormActions;
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make(Core::TEAM_COLUMN)
                    ->label('Select your Team')
                    ->options(fn () => auth()->user()->teams()->pluck('name', 'id'))
                    ->searchable()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state) => $this->updateTeam($state))
                    ->required(),
            ])
            ->statePath('data');
    }

    public function updateTeam(?int $selectedTeam = null): void
    {
        $data = $this->form->getState();
        $team = $data[Core::TEAM_COLUMN];
        User::find(Auth::id())->update([Core::TEAM_COLUMN => $team]);

        $this->redirect(request()->header('Referer'));
    }

    public function render(): string
    {
        return <<<'blade'
            <div>
                    <form wire:submit="updateTeam">

                        {{ $this->form }}

                        <x-filament::button class="my-4 w-full" icon="heroicon-o-building-office-2" type="submit">
                            Change
                        </x-filament::button>
                    </form>
                    <x-filament-actions::modals />
            </div>
blade;
    }
}
