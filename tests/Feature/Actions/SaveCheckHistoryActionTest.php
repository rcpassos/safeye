<?php

declare(strict_types=1);

namespace Tests\Feature\Actions;

use App\Actions\SaveCheckHistory;
use App\Enums\CheckHistoryType;
use App\Models\Check;
use App\Models\CheckHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class SaveCheckHistoryActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_save_check_history(): void
    {
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

        $this->assertInstanceOf(CheckHistory::class, $history);
        $this->assertEquals($check->id, $history->check_id);
        $this->assertEquals($metadata, $history->metadata);
        $this->assertEquals($rootCause, $history->root_cause);
        $this->assertEquals(CheckHistoryType::SUCCESS, $history->type);
        $this->assertDatabaseHas('check_history', [
            'check_id' => $check->id,
            'type' => CheckHistoryType::SUCCESS->value,
        ]);
    }

    public function test_sends_notification_when_error_and_emails_configured(): void
    {
        Mail::fake();

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

        Mail::assertSent(\App\Mail\NotifyCheckIncident::class, 1);
    }

    public function test_does_not_send_notification_when_success(): void
    {
        Mail::fake();

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

        Mail::assertNothingSent();
    }

    public function test_does_not_send_notification_when_no_emails_configured(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $check = Check::factory()->create([
            'user_id' => $user->id,
            'notify_emails' => '',
        ]);

        $action = app(SaveCheckHistory::class);
        $action->handle(
            check: $check,
            metadata: ['error' => 'Failed'],
            rootCause: [],
            type: CheckHistoryType::ERROR
        );

        Mail::assertNothingSent();
    }
}
