<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Team;
use App\Models\TeamInvitation;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Forms\Set;
use Filament\Pages\Dashboard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RegisterTeam extends RegisterTenant
{
    public function mount(): void
    {
        parent::mount();

        $this->handleTeamInvitation();
    }

    public static function getLabel(): string
    {
        return 'Register team';
    }

    public function form(Form $form): Form
    {
        $defaultName = Filament::getUserName(Auth::user()) . '\'s Team';

        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->default($defaultName)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, $state) {
                        $set('slug', Str::slug($state));
                    }),
                TextInput::make('slug')
                    ->required()
                    ->unique()
                    ->default(Str::slug($defaultName))
                    ->maxLength(255),
            ]);
    }

    protected function handleRegistration(array $data): Team
    {
        $data['owner_id'] = Auth::user()->id;
        $team = Team::create($data);
        $team->members()->attach(Auth::user());

        return $team;
    }

    protected function handleTeamInvitation()
    {
        if ($invitation = TeamInvitation::where('email', Auth::user()->email)->first()) {
            $invitation->team->members()->attach(Auth::user());
            $invitation->delete();

            return redirect()->to(Dashboard::getUrl(tenant: $invitation->team))->with('banner', 'You have accepted the invitation to join ' . $invitation->team->name . '.');
        }
    }
}
