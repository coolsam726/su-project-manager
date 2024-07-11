<?php

namespace App\Livewire\Forms;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class PasswordChangeForm extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data;

    public function mount(): void
    {
        $this->data = [
            'current_password' => '',
            'password' => '',
            'password_confirmation' => '',
        ];
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('current_password')->required(),
            TextInput::make('password')->required()->confirmed(),
            TextInput::make('password_confirmation')->required()->same('password'),
        ]);
    }

    public function render(): string
    {
        return <<<'HTML'
        <form wire:submit.prevent="submit">
            {{$this->form}}
        </form>
        HTML;
    }

    public function submit(): void
    {
        $this->validate();
        $data = $this->form->getState();
        $collect = collect($data);

        abort_unless(Hash::check($collect->get('current_password'), Auth::user()->password), 403, 'Current password is incorrect');

        Auth::user()->update([
            'password' => Hash::make($collect->get('password')),
        ]);

        $this->notify('Password changed successfully');
    }
}
