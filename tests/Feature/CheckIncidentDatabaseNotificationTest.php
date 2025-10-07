<?php

declare(strict_types=1);

use App\Actions\SaveCheckHistory;
use App\Enums\CheckHistoryType;
use App\Models\Check;
use App\Models\User;

test('check failure creates database notification for user', function () {
    $user = User::factory()->create();
    $check = Check::factory()->create([
        'user_id' => $user->id,
        'notify_emails' => 'test@example.com',
    ]);

    $this->assertDatabaseCount('notifications', 0);

    $action = app(SaveCheckHistory::class);
    $action->handle(
        check: $check,
        metadata: ['error' => 'Connection failed'],
        rootCause: ['type' => 'status_code', 'sign' => '!=', 'value' => '200'],
        type: CheckHistoryType::ERROR
    );

    // Verify database notification was created
    $this->assertDatabaseCount('notifications', 1);

    $this->assertDatabaseHas('notifications', [
        'notifiable_type' => User::class,
        'notifiable_id' => $user->id,
        'type' => 'App\Notifications\CheckIncidentNotification',
    ]);

    // Verify notification contains correct Filament notification data
    $notification = $user->notifications()->first();
    $this->assertNotNull($notification);
    $this->assertArrayHasKey('title', $notification->data);
    $this->assertArrayHasKey('body', $notification->data);
    $this->assertArrayHasKey('actions', $notification->data);
    $this->assertStringContainsString($check->name, $notification->data['title']);
});

test('successful check does not create database notification', function () {
    $user = User::factory()->create();
    $check = Check::factory()->create([
        'user_id' => $user->id,
        'notify_emails' => 'test@example.com',
    ]);

    $this->assertDatabaseCount('notifications', 0);

    $action = app(SaveCheckHistory::class);
    $action->handle(
        check: $check,
        metadata: ['response_code' => 200],
        rootCause: [],
        type: CheckHistoryType::SUCCESS
    );

    // Verify no database notification was created
    $this->assertDatabaseCount('notifications', 0);
});

test('user can see unread notification count', function () {
    $user = User::factory()->create();
    $check = Check::factory()->create([
        'user_id' => $user->id,
        'notify_emails' => 'test@example.com',
    ]);

    expect($user->unreadNotifications()->count())->toBe(0);

    $action = app(SaveCheckHistory::class);
    $action->handle(
        check: $check,
        metadata: ['error' => 'Connection failed'],
        rootCause: ['type' => 'status_code', 'sign' => '!=', 'value' => '200'],
        type: CheckHistoryType::ERROR
    );

    // Refresh user model to get updated notifications
    $user->refresh();

    expect($user->unreadNotifications()->count())->toBe(1);

    // Mark as read
    $user->unreadNotifications->markAsRead();

    $user->refresh();
    expect($user->unreadNotifications()->count())->toBe(0);
});
