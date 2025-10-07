<?php

declare(strict_types=1);

use App\Actions\SaveCheckHistory;
use App\Enums\CheckHistoryType;
use App\Models\Check;
use App\Models\CheckHistory;
use App\Models\User;
use App\Notifications\CheckIncidentNotification;
use Illuminate\Support\Facades\Notification;

test('can save check history', function () {
    $user = User::factory()->create();
    $check = Check::factory()->create([
        'user_id' => $user->id,
        'notify_emails' => '',
    ]);

    $metadata = ['response_code' => 200, 'transfer_time' => 0.5];
    $rootCause = [];

    $action = app(SaveCheckHistory::class);
    $history = $action->handle(
        check: $check,
        metadata: $metadata,
        rootCause: $rootCause,
        type: CheckHistoryType::SUCCESS
    );

    expect($history)->toBeInstanceOf(CheckHistory::class);
    expect($history->check_id)->toBe($check->id);
    expect($history->metadata)->toBe($metadata);
    expect($history->root_cause)->toBe($rootCause);
    expect($history->type)->toBe(CheckHistoryType::SUCCESS);
    $this->assertDatabaseHas('check_history', [
        'check_id' => $check->id,
        'type' => CheckHistoryType::SUCCESS->value,
    ]);
});

test('sends notification when error and emails configured', function () {
    Notification::fake();

    $user = User::factory()->create();
    $check = Check::factory()->create([
        'user_id' => $user->id,
        'notify_emails' => "test@example.com\nadmin@example.com",
    ]);

    $metadata = ['error' => 'Connection failed'];
    $rootCause = ['error' => 'Timeout'];

    $action = app(SaveCheckHistory::class);
    $action->handle(
        check: $check,
        metadata: $metadata,
        rootCause: $rootCause,
        type: CheckHistoryType::ERROR
    );

    Notification::assertSentOnDemand(CheckIncidentNotification::class);
});

test('does not send notification when success', function () {
    Notification::fake();

    $user = User::factory()->create();
    $check = Check::factory()->create([
        'user_id' => $user->id,
        'notify_emails' => 'test@example.com',
    ]);

    $action = app(SaveCheckHistory::class);
    $action->handle(
        check: $check,
        metadata: ['response_code' => 200],
        rootCause: [],
        type: CheckHistoryType::SUCCESS
    );

    Notification::assertNothingSent();
});

test('sends database notification even when no emails configured', function () {
    Notification::fake();

    $user = User::factory()->create();
    $check = Check::factory()->create([
        'user_id' => $user->id,
        'notify_emails' => '', // No email addresses configured
    ]);

    $action = app(SaveCheckHistory::class);
    $action->handle(
        check: $check,
        metadata: ['error' => 'Failed'],
        rootCause: ['type' => 'status_code', 'sign' => '!=', 'value' => '200'],
        type: CheckHistoryType::ERROR
    );

    // Database notification should still be sent to the user
    Notification::assertSentTo($user, CheckIncidentNotification::class);

    // Only one notification should be sent (to the user, not on-demand)
    Notification::assertCount(1);
});
