<?php

namespace App\Filament\Pages\Tenancy;

use App\Mail\TeamInvitationMail;
use App\Models\TeamInvitation;
use App\Models\User;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Tenancy\EditTenantProfile;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class EditTeamProfile extends EditTenantProfile implements HasTable
{
    use InteractsWithTable;

    /**
     * @var view-string
     */
    protected static string $view = 'filament.pages.team.profile';

    public static function getLabel(): string
    {
        return Filament::getTenant()->name;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('slug')
                            ->required()
                            ->disabled()
                            ->unique(ignoreRecord: true)
                            ->helperText('This slug will be used in the URL of your team. It can not be changed.')
                            ->maxLength(255),
                    ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table->query($this->tenant->members()->getQuery())
            ->emptyStateHeading('No team members.')
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('email'),
            ])->actions([
                Action::make('Remove')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->hidden($this->tenant->owner_id === Auth::id())
                    ->action(function (User $record) {
                        $this->tenant->members()->detach($record);

                        Notification::make()
                            ->title($record->name . ' user removed from team')
                            ->success()
                            ->send();
                    })
            ])->headerActions([
                Action::make('Invite User')
                    ->icon('heroicon-o-plus')
                    ->hidden($this->tenant->owner_id !== Auth::id())
                    ->form([
                        TextInput::make('email')
                            ->unique('team_invitations', 'email')
                            ->required()
                            ->email(),
                    ])->action(function (array $data) {
                        $invitation = TeamInvitation::create([
                            'email' => $data['email'],
                            'team_id' => $this->tenant->id,
                        ]);

                        Mail::to($invitation->email)->send(new TeamInvitationMail($invitation));

                        Notification::make()
                            ->title('User invited successfully')
                            ->success()
                            ->send();
                    })
            ]);
    }
}
