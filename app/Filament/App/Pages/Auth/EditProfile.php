<?php

declare(strict_types=1);

namespace App\Filament\App\Pages\Auth;

use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Illuminate\Support\Facades\Auth;

final class EditProfile extends BaseEditProfile
{
    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'name' => Auth::user()->name,
            'email' => Auth::user()->email,
            'timezone' => Auth::user()->timezone ?? config('app.timezone'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                Select::make('timezone')
                    ->label(__('common.timezone'))
                    ->options($this->getTimezoneOptions())
                    ->searchable()
                    ->required()
                    ->default(config('app.timezone'))
                    ->helperText(__('common.timezone_helper'))
                    ->extraAttributes(['x-init' => '$el.value = Intl.DateTimeFormat().resolvedOptions().timeZone || \''.config('app.timezone').'\''])
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state): void {
                        if ($state) {
                            config(['app.timezone' => $state]);
                            date_default_timezone_set($state);
                        }
                    }),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }

    private function getTimezoneOptions(): array
    {
        $timezones = timezone_identifiers_list();
        $timezoneOptions = [];

        foreach ($timezones as $timezone) {
            $timezoneOptions[$timezone] = $timezone;
        }

        return $timezoneOptions;
    }
}
