<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\RunSingleCheck;
use App\Enums\AssertionSign;
use App\Enums\AssertionType;
use App\Enums\CheckHistoryType;
use App\Models\Assertion;
use App\Models\Check;
use App\Models\CheckHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class RunSingleCheckActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_run_single_check_action(): void
    {
        // Create a user and check
        $user = User::factory()->create();
        $check = Check::factory()->create([
            'user_id' => $user->id,
            'endpoint' => 'https://httpbin.org/status/200',
            'active' => true,
        ]);

        // Create an assertion for 200 response code
        Assertion::factory()->create([
            'check_id' => $check->id,
            'type' => AssertionType::RESPONSE_CODE,
            'sign' => AssertionSign::EQUAL,
            'value' => '200',
        ]);

        $this->assertDatabaseCount('check_history', 0);

        // Run the action
        $action = new RunSingleCheck(
            app(\App\Actions\SaveCheckHistory::class),
            app(\App\Actions\EvaluateAssertion::class)
        );
        $action->handle($check);

        // Assert check history was created (even with no assertions)
        $this->assertDatabaseCount('check_history', 1);

        // Assert last_run_at was updated
        $check->refresh();
        $this->assertNotNull($check->last_run_at);

        // Assert check history contains expected data
        $history = CheckHistory::first();
        $this->assertEquals($check->id, $history->check_id);
        $this->assertArrayHasKey('response_code', $history->metadata);
        $this->assertArrayHasKey('transfer_time', $history->metadata);
        $this->assertEquals(CheckHistoryType::SUCCESS, $history->type);
        $this->assertEquals(200, $history->metadata['response_code']);
        $this->assertEquals(CheckHistoryType::SUCCESS, $history->type);
    }

    public function test_check_execution_handles_request_failure(): void
    {
        // Create a user and check with invalid URL (no assertions - connection error only)
        $user = User::factory()->create();
        $check = Check::factory()->create([
            'user_id' => $user->id,
            'endpoint' => 'https://invalid-domain-that-does-not-exist.com',
            'notify_emails' => '', // No email notifications for this test
            'active' => true,
        ]);

        $this->assertDatabaseCount('check_history', 0);

        // Run the action
        $action = new RunSingleCheck(
            app(\App\Actions\SaveCheckHistory::class),
            app(\App\Actions\EvaluateAssertion::class)
        );
        $action->handle($check);

        // Assert check history was created even for failed requests
        $this->assertDatabaseCount('check_history', 1);

        // Assert last_run_at was updated
        $check->refresh();
        $this->assertNotNull($check->last_run_at);

        // Assert check history contains error data
        $history = CheckHistory::first();
        $this->assertEquals($check->id, $history->check_id);
        $this->assertArrayHasKey('error', $history->metadata);
        $this->assertEquals(CheckHistoryType::ERROR, $history->type);
        $this->assertEquals(CheckHistoryType::ERROR, $history->type);
    }
}
