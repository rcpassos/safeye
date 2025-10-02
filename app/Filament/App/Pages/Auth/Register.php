<?php

declare(strict_types=1);

namespace App\Filament\App\Pages\Auth;

use Filament\Forms\Components\Hidden;
use Filament\Pages\Auth\Register as BaseRegister;

final class Register extends BaseRegister
{
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                        Hidden::make('timezone')
                            ->default(config('app.timezone'))
                            ->extraAttributes(['x-init' => '$el.value = Intl.DateTimeFormat().resolvedOptions().timeZone || \''.config('app.timezone').'\'']),
                    ])
                    ->statePath('data'),
            ),
        ];
    }
}
