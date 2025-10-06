<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Filament\App\Resources\CheckResource;
use App\Models\CheckHistory;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class CheckIncidentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public CheckHistory $checkHistory)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * This notification is queued for better performance. Make sure you have a queue worker running:
     * - Development: php artisan queue:work
     * - Production: Use Supervisor or Laravel Horizon to manage queue workers
     *
     * To add Slack notifications:
     * 1. Install: composer require laravel/slack-notification-channel
     * 2. Add 'slack' to the via() return array
     * 3. Implement toSlack() method (see example in docs)
     * 4. Configure SLACK_WEBHOOK_URL in .env
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // If notifiable is a User model, send both mail and database notifications
        // If it's an on-demand notification, only send mail
        if ($notifiable instanceof \App\Models\User) {
            return ['mail', 'database'];
        }

        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $check = $this->checkHistory->check;
        $checkUrl = CheckResource::getUrl('view', ['record' => $check], panel: 'app');

        return (new MailMessage)
            ->subject(__('notifications.check_incident.subject', ['check_name' => $check->name]))
            ->line(__('notifications.check_incident.incident_found'))
            ->line(__('notifications.check_incident.check_name').': '.$check->name)
            ->line('**'.__('notifications.check_incident.assertion_failed').':**')
            ->line($this->checkHistory->root_cause['type'].' '.$this->checkHistory->root_cause['sign'].' '.$this->checkHistory->root_cause['value'])
            ->action(__('notifications.check_incident.open_in_safeye'), $checkUrl)
            ->line(__('notifications.check_incident.thanks'));
    }

    /**
     * Get the database representation of the notification (for Filament UI).
     */
    public function toDatabase(object $notifiable): array
    {
        $check = $this->checkHistory->check;

        return FilamentNotification::make()
            ->title(__('notifications.check_incident.subject', ['check_name' => $check->name]))
            ->body(__('notifications.check_incident.assertion_failed').': '.$this->checkHistory->root_cause['type'].' '.$this->checkHistory->root_cause['sign'].' '.$this->checkHistory->root_cause['value'])
            ->danger()
            ->icon('heroicon-o-exclamation-triangle')
            ->actions([
                Action::make('view')
                    ->label(__('notifications.check_incident.open_in_safeye'))
                    ->url(CheckResource::getUrl('view', ['record' => $check], panel: 'app'))
                    ->markAsRead(),
            ])
            ->getDatabaseMessage();
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $check = $this->checkHistory->check;
        $checkUrl = CheckResource::getUrl('view', ['record' => $check], panel: 'app');

        return [
            'check_id' => $check->id,
            'check_name' => $check->name,
            'check_history_id' => $this->checkHistory->id,
            'root_cause' => $this->checkHistory->root_cause,
            'check_url' => $checkUrl,
        ];
    }
}
