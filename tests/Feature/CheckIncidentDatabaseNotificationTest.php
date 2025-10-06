<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\SaveCheckHistory;
use App\Enums\CheckHistoryType;
use App\Models\Check;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CheckIncidentDatabaseNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_failure_creates_database_notification_for_user(): void
    {
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
    }

    public function test_successful_check_does_not_create_database_notification(): void
    {
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
    }

    public function test_user_can_see_unread_notification_count(): void
    {
        $user = User::factory()->create();
        $check = Check::factory()->create([
            'user_id' => $user->id,
            'notify_emails' => 'test@example.com',
        ]);

        $this->assertEquals(0, $user->unreadNotifications()->count());

        $action = app(SaveCheckHistory::class);
        $action->handle(
            check: $check,
            metadata: ['error' => 'Connection failed'],
            rootCause: ['type' => 'status_code', 'sign' => '!=', 'value' => '200'],
            type: CheckHistoryType::ERROR
        );

        // Refresh user model to get updated notifications
        $user->refresh();

        $this->assertEquals(1, $user->unreadNotifications()->count());

        // Mark as read
        $user->unreadNotifications->markAsRead();

        $user->refresh();
        $this->assertEquals(0, $user->unreadNotifications()->count());
    }
}
