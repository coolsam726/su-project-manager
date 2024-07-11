<?php

namespace Modules\Core\Livewire;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Livewire\Component;

class ErrorModal extends Component
{
    public $content = '';

    public string $message = 'An error occurred. Please contact system admin or check logs for more details';

    public $title = 'Error';

    public $status = '';

    protected $listeners = [
        'showErrorModal',
    ];

    public function render(): string
    {
        return <<<'blade'
            <x-filament::modal
                icon="heroicon-o-exclamation-triangle"
                icon-color="danger"
             id="display-livewire-errors">
                <p>ERROR!</p>
            </x-filament::modal>
        blade;
    }

    public function showErrorModal($payload): void
    {
        $payload = collect($payload);
        Log::info($payload);
        $this->status = $payload->get('status', 'Unknown');
        $this->title = $payload->get('title', 'Error');
        $this->message = $payload->get('message', 'An error occurred. Please contact system admin or check logs for more details');
        $this->content = $payload->get('content', '');
        Notification::make('server-error')
            ->title(fn () => new HtmlString('<h2><span>'.$this->status.' </span>'.$this->title.'</h2>'))
            ->danger()
            ->body($this->message)
            ->persistent()
            ->send();
    }
}
