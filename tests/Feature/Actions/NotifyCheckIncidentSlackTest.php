<?php

declare(strict_types=1);

use App\Actions\NotifyCheckIncident;
use App\Enums\CheckHistoryType;
use App\Models\Check;
use App\Models\CheckHistory;
use App\Models\User;
use App\Notifications\CheckIncidentNotification;
use Illuminate\Support\Facades\Notification;

test('sends slack notification when webhook url is configured', function () {
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
});

test('does not send slack notification when webhook url is not configured', function () {
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
});

test('sends both email and slack notifications when both configured', function () {
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
});

test('slack notification contains correct check information', function () {
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
});

test('slack webhook url routes to correct webhook', function () {
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
});
