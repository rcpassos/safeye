<?php

declare(strict_types=1);

namespace Tests\Feature\Actions;

use App\Actions\NotifyCheckIncident;
use App\Enums\CheckHistoryType;
use App\Models\Check;
use App\Models\CheckHistory;
use App\Models\User;
use App\Notifications\CheckIncidentNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use stdClass;
use Tests\TestCase;

final class NotifyCheckIncidentSlackTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_slack_notification_when_webhook_url_is_configured(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $check = Check::factory()
            ->withSlack()
            ->for($user)
            ->create();

        $checkHistory = CheckHistory::factory()
            ->for($check)
            ->create([
                'type' => CheckHistoryType::ERROR,
                'root_cause' => [
                    'type' => 'response_code',
                    'sign' => '==',
                    'value' => '200',
                ],
            ]);

        $action = app(NotifyCheckIncident::class);
        $action->handle([], $checkHistory);

        Notification::assertSentOnDemand(
            CheckIncidentNotification::class,
            function ($notification, $channels) {
                return in_array('slack', $channels, true);
            }
        );
    }

    public function test_does_not_send_slack_notification_when_webhook_url_is_not_configured(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $check = Check::factory()
            ->for($user)
            ->create(['slack_webhook_url' => null]);

        $checkHistory = CheckHistory::factory()
            ->for($check)
            ->create([
                'type' => CheckHistoryType::ERROR,
                'root_cause' => [
                    'type' => 'response_code',
                    'sign' => '==',
                    'value' => '200',
                ],
            ]);

        $action = app(NotifyCheckIncident::class);
        $action->handle([], $checkHistory);

        // Assert that user notification was sent (with mail and database channels)
        Notification::assertSentTo(
            $user,
            CheckIncidentNotification::class,
            function ($notification, $channels) {
                return in_array('mail', $channels, true)
                    && in_array('database', $channels, true)
                    && ! in_array('slack', $channels, true);
            }
        );
    }

    public function test_sends_both_email_and_slack_notifications_when_both_configured(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $check = Check::factory()
            ->withSlack()
            ->for($user)
            ->create([
                'notify_emails' => 'test@example.com',
            ]);

        $checkHistory = CheckHistory::factory()
            ->for($check)
            ->create([
                'type' => CheckHistoryType::ERROR,
                'root_cause' => [
                    'type' => 'response_code',
                    'sign' => '==',
                    'value' => '200',
                ],
            ]);

        $action = app(NotifyCheckIncident::class);
        $action->handle(['test@example.com'], $checkHistory);

        // Assert email notification sent
        Notification::assertSentOnDemand(
            CheckIncidentNotification::class,
            function ($notification, $channels) {
                return in_array('mail', $channels, true);
            }
        );

        // Assert Slack notification sent
        Notification::assertSentOnDemand(
            CheckIncidentNotification::class,
            function ($notification, $channels) {
                return in_array('slack', $channels, true);
            }
        );
    }

    public function test_slack_notification_contains_correct_check_information(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $check = Check::factory()
            ->withSlack()
            ->for($user)
            ->create(['name' => 'Test API Check']);

        $checkHistory = CheckHistory::factory()
            ->for($check)
            ->create([
                'type' => CheckHistoryType::ERROR,
                'root_cause' => [
                    'type' => 'response_code',
                    'sign' => '==',
                    'value' => '200',
                ],
            ]);

        $action = app(NotifyCheckIncident::class);
        $action->handle([], $checkHistory);

        Notification::assertSentOnDemand(
            CheckIncidentNotification::class,
            function ($notification, $channels, $notifiable) use ($check): bool {
                if (! in_array('slack', $channels, true)) {
                    return false;
                }

                // Verify the notification has the correct check
                return $notification->checkHistory->check->id === $check->id
                    && $notification->checkHistory->check->name === 'Test API Check';
            }
        );
    }

    public function test_slack_webhook_url_routes_to_correct_webhook(): void
    {
        Notification::fake();

        $webhookUrl = 'https://hooks.slack.com/services/TEST123/WEBHOOK456';
        $user = User::factory()->create();
        $check = Check::factory()
            ->for($user)
            ->create(['slack_webhook_url' => $webhookUrl]);

        $checkHistory = CheckHistory::factory()
            ->for($check)
            ->create([
                'type' => CheckHistoryType::ERROR,
            ]);

        $action = app(NotifyCheckIncident::class);
        $action->handle([], $checkHistory);

        Notification::assertSentOnDemand(
            CheckIncidentNotification::class,
            function ($notification) use ($webhookUrl): bool {
                $route = $notification->routeNotificationForSlack(new stdClass);

                return $route === $webhookUrl;
            }
        );
    }
}
