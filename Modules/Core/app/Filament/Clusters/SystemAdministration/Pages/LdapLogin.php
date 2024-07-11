<?php

namespace Modules\Core\Filament\Clusters\SystemAdministration\Pages;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use LdapRecord\Laravel\Auth\ListensForLdapBindFailure;
use Modules\Core\Support\Users;

class LdapLogin extends BaseLogin
{
    use ListensForLdapBindFailure;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    //    protected static string $view = 'vanadi-framework::pages.auth.login';

    public function getHeading(): string|Htmlable
    {
        return __('University Login');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('Use your university AD credentials to login');
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/login.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/login.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/login.notifications.throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]) : null)
                ->danger()
                ->send();

            return null;
        }

        $data = $this->form->getState();
        $provider = is_numeric($data['username']) ? 'students' : 'staff';
        // Ensure the user exists or synchronize the user
        $user = \App\Models\User::whereUsername($username = $data['username'])->first();
        if (! $user) {
            try {
                Log::info(__("User $username not found in our DB. Syncing from AD..."));
                $user = Users::make()->syncUser($username, $provider);
            } catch (\Throwable $exception) {
                Log::error($exception);
                Notification::make('error')->danger()->body($exception->getMessage())->title(__('User Sync Error'))->persistent()->send();
            }
        }

        try {
            abort_unless($user->is_active, 403, 'You are not authorized to login to this system. Please contact your administrator.');
            $res = Users::make()->ldapMasquerade($username = $user->username);
            if ($res) {
                Log::info('Masquerade as '.$username.' successful.');
                session()->regenerate();

                return app(LoginResponse::class);
            }
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'data.username' => $e->getMessage(),
            ]);
        }
        Config::set('auth.guards.web.provider', $provider);
        $userProvider = Auth::createUserProvider($provider);
        Auth::setProvider($userProvider);
        $this->listenForLdapBindFailure();
        $res = Auth::attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false);
        if (! $res) {
            throw ValidationException::withMessages([
                'data.username' => __('filament-panels::pages/auth/login.messages.failed'),
            ]);
        }
        Log::info('Login passed');
        session()->regenerate();

        return app(LoginResponse::class);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getUsernameFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getRememberFormComponent(),
            ])
            ->statePath('data');
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('filament-panels::pages/auth/login.form.password.label'))
//            ->hint(filament()->hasPasswordReset() ? new HtmlString(Blade::render('<x-filament::link :href="filament()->getRequestPasswordResetUrl()"> {{ __(\'filament-panels::pages/auth/login.actions.request_password_reset.label\') }}</x-filament::link>')) : null)
            ->password()
            ->required();
    }

    protected function getUsernameFormComponent(): Component
    {
        return TextInput::make('username')
            ->label(__('Username'))
            ->required()
            ->autocomplete('username')
            ->autofocus();
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'samaccountname' => $data['username'],
            'password' => $data['password'],
            /*'fallback' => [
                'username' => $data['username'],
                'password' => $data['password']
            ]*/
        ];
    }

    protected function throwLoginValidationException(string $message): void
    {
        throw ValidationException::withMessages([
            'data.username' => "$message",
        ]);
    }
}
