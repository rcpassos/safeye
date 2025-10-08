<?php

declare(strict_types=1);

use App\Actions\RunPingCheck;
use App\Enums\CheckHistoryType;
use App\Enums\CheckType;
use App\Models\Check;
use App\Models\User;

test('runs ping check successfully', function (): void {
    $user = User::factory()->create();

    $check = Check::factory()->create([
        'user_id' => $user->id,
        'type' => CheckType::PING,
        'endpoint' => '8.8.8.8',
        'config' => [
            'count' => 4,
            'timeout' => 10,
        ],
    ]);

    $action = app(RunPingCheck::class);
    $action->handle($check);

    $this->assertDatabaseHas('check_history', [
        'check_id' => $check->id,
    ]);

    $history = $check->history()->latest()->first();
    expect($history)->not->toBeNull();
    expect($history->metadata)->toHaveKey('host');
    expect($history->metadata)->toHaveKey('packets_transmitted');
    expect($history->metadata)->toHaveKey('packets_received');
});
test('handles invalid host gracefully', function (): void {
    $user = User::factory()->create();

    $check = Check::factory()->create([
        'user_id' => $user->id,
        'type' => CheckType::PING,
        'endpoint' => 'invalid-host-that-does-not-exist-12345.com',
        'config' => [
            'count' => 2,
            'timeout' => 5,
        ],
    ]);

    $action = app(RunPingCheck::class);
    $action->handle($check);

    $history = $check->history()->latest()->first();
    expect($history->type)->toBe(CheckHistoryType::ERROR);
});

test('extracts host from url', function (): void {
    $user = User::factory()->create();

    $check = Check::factory()->create([
        'user_id' => $user->id,
        'type' => CheckType::PING,
        'endpoint' => 'https://google.com/path',
        'config' => [
            'count' => 2,
            'timeout' => 10,
        ],
    ]);

    $action = app(RunPingCheck::class);
    $action->handle($check);

    $history = $check->history()->latest()->first();
    expect($history->metadata['host'])->toBe('google.com');
});

test('updates last run at timestamp', function (): void {
    $user = User::factory()->create();

    $check = Check::factory()->create([
        'user_id' => $user->id,
        'type' => CheckType::PING,
        'endpoint' => '8.8.8.8',
        'config' => [
            'count' => 2,
            'timeout' => 5,
        ],
        'last_run_at' => null,
    ]);

    $action = app(RunPingCheck::class);
    $action->handle($check);

    $check->refresh();
    expect($check->last_run_at)->not->toBeNull();
});
