<?php

declare(strict_types=1);

use App\Actions\NotifyCheckIncident;
use App\Models\Check;
use App\Models\CheckHistory;
use App\Models\User;
use App\Notifications\CheckIncidentNotification;
use Illuminate\Support\Facades\Notification;

test('sends notification to single recipient', function () {
    Notification::fake();

    $user = User::factory()->create();
    $check = Check::factory()->create(['user_id' => $user->id]);
    $history = CheckHistory::factory()->create(['check_id' => $check->id]);

    $action = app(NotifyCheckIncident::class);
    $action->handle(['test@example.com'], $history);

    // Database notification sent to check owner
    Notification::assertSentTo($user, CheckIncidentNotification::class, function ($notification) use ($history) {
        return $notification->checkHistory->id === $history->id;
    });

    // Email notification sent to configured email
    Notification::assertSentOnDemand(CheckIncidentNotification::class, function ($notification, $channels, $notifiable) use ($history) {
        return isset($notifiable->routes['mail']) &&
               $notifiable->routes['mail'][0] === 'test@example.com' &&
               $notification->checkHistory->id === $history->id;
    });
});

test('sends notification to multiple recipients', function () {
    Notification::fake();

    $user = User::factory()->create();
    $check = Check::factory()->create(['user_id' => $user->id]);
    $history = CheckHistory::factory()->create(['check_id' => $check->id]);

    $emails = ['admin@example.com', 'dev@example.com', 'support@example.com'];

    $action = app(NotifyCheckIncident::class);
    $action->handle($emails, $history);

    // Database notification sent to check owner
    Notification::assertSentTo($user, CheckIncidentNotification::class);

    // Email notifications sent to all configured emails
    Notification::assertSentOnDemand(CheckIncidentNotification::class, function ($notification, $channels, $notifiable) use ($emails) {
        if (! isset($notifiable->routes['mail'])) {
            return false;
        }

        foreach ($emails as $email) {
            if (in_array($email, $notifiable->routes['mail'])) {
                return true;
            }
        }

        return false;
    });
});

test('sends via mail and database channels to user', function () {
    Notification::fake();

    $user = User::factory()->create();
    $check = Check::factory()->create(['user_id' => $user->id]);
    $history = CheckHistory::factory()->create(['check_id' => $check->id]);

    $action = app(NotifyCheckIncident::class);
    $action->handle(['test@example.com'], $history);

    // Check that user receives both mail and database notifications
    Notification::assertSentTo($user, CheckIncidentNotification::class, function ($notification, $channels) {
        return in_array('mail', $channels) && in_array('database', $channels);
    });
});

test('sends only mail for on demand notifications', function () {
    Notification::fake();

    $user = User::factory()->create();
    $check = Check::factory()->create(['user_id' => $user->id]);
    $history = CheckHistory::factory()->create(['check_id' => $check->id]);

    $action = app(NotifyCheckIncident::class);
    $action->handle(['external@example.com'], $history);

    // Check that on-demand notifications only use mail channel
    Notification::assertSentOnDemand(CheckIncidentNotification::class, function ($notification, $channels) {
        return in_array('mail', $channels) && ! in_array('database', $channels);
    });
});
