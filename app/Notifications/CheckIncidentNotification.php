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
use Illuminate\Notifications\Slack\BlockKit\Blocks\ContextBlock;
use Illuminate\Notifications\Slack\BlockKit\Blocks\SectionBlock;
use Illuminate\Notifications\Slack\SlackMessage;

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
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // If notifiable is a User model, send both mail and database notifications
        if ($notifiable instanceof \App\Models\User) {
            return ['mail', 'database'];
        }

        // For on-demand notifications (anonymous notifiable), determine the channel
        // by checking the routes that were configured
        $channels = [];

        if (method_exists($notifiable, 'routeNotificationFor')) {
            if ($notifiable->routeNotificationFor('mail')) {
                $channels[] = 'mail';
            }
            if ($notifiable->routeNotificationFor('slack')) {
                $channels[] = 'slack';
            }
        }

        // Fallback to mail if no specific route is detected
        return $channels !== [] ? $channels : ['mail'];
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
     * Get the Slack representation of the notification.
     */
    public function toSlack(object $notifiable): SlackMessage
    {
        $check = $this->checkHistory->check;
        $checkUrl = CheckResource::getUrl('view', ['record' => $check], panel: 'app');

        return (new SlackMessage)
            ->text(__('notifications.check_incident.subject', ['check_name' => $check->name]))
            ->headerBlock(__('notifications.check_incident.incident_found'))
            ->contextBlock(function (ContextBlock $block) use ($check): void {
                $block->text(__('notifications.check_incident.check_name').': '.$check->name);
            })
            ->sectionBlock(function (SectionBlock $block): void {
                $block->text('*'.__('notifications.check_incident.assertion_failed').'*');
                $block->field("*Type:*\n".$this->checkHistory->root_cause['type'])->markdown();
                $block->field("*Condition:*\n".$this->checkHistory->root_cause['sign'].' '.$this->checkHistory->root_cause['value'])->markdown();
            })
            ->dividerBlock()
            ->sectionBlock(function (SectionBlock $block) use ($checkUrl): void {
                $block->text('<'.$checkUrl.'|'.__('notifications.check_incident.open_in_safeye').'>');
            });
    }

    /**
     * Route notification for the Slack channel.
     */
    public function routeNotificationForSlack(object $notifiable): ?string
    {
        return $this->checkHistory->check->slack_webhook_url;
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
