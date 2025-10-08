<?php

declare(strict_types=1);

use App\Actions\RunSingleCheck;
use App\Enums\AssertionSign;
use App\Enums\AssertionType;
use App\Enums\CheckHistoryType;
use App\Enums\CheckType;
use App\Models\Assertion;
use App\Models\Check;
use App\Models\CheckHistory;
use App\Models\User;

test('can run single check action', function () {
    // Create a user and check
    $user = User::factory()->create();
    $check = Check::factory()->create([
        'user_id' => $user->id,
        'type' => CheckType::HTTP,
        'endpoint' => 'https://example.com',
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
    $action = app(RunSingleCheck::class);
    $action->handle($check);

    // Assert check history was created (even with no assertions)
    $this->assertDatabaseCount('check_history', 1);

    // Assert last_run_at was updated
    $check->refresh();
    $this->assertNotNull($check->last_run_at);

    // Assert check history contains expected data
    $history = CheckHistory::first();
    expect($history->check_id)->toBe($check->id);
    $this->assertArrayHasKey('response_code', $history->metadata);
    $this->assertArrayHasKey('transfer_time', $history->metadata);
    expect($history->type)->toBe(CheckHistoryType::SUCCESS);
    expect($history->metadata['response_code'])->toBe(200);
    expect($history->type)->toBe(CheckHistoryType::SUCCESS);
});

test('check execution handles request failure', function () {
    // Create a user and check with invalid URL (no assertions - connection error only)
    $user = User::factory()->create();
    $check = Check::factory()->create([
        'user_id' => $user->id,
        'type' => CheckType::HTTP,
        'endpoint' => 'https://invalid-domain-that-does-not-exist.com',
        'notify_emails' => '', // No email notifications for this test
        'active' => true,
    ]);

    $this->assertDatabaseCount('check_history', 0);

    // Run the action
    $action = app(RunSingleCheck::class);
    $action->handle($check);

    // Assert check history was created even for failed requests
    $this->assertDatabaseCount('check_history', 1);

    // Assert last_run_at was updated
    $check->refresh();
    $this->assertNotNull($check->last_run_at);

    // Assert check history contains error data
    $history = CheckHistory::first();
    expect($history->check_id)->toBe($check->id);
    $this->assertArrayHasKey('error', $history->metadata);
    expect($history->type)->toBe(CheckHistoryType::ERROR);
    expect($history->type)->toBe(CheckHistoryType::ERROR);
});
