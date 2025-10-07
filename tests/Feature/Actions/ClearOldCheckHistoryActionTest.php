<?php

declare(strict_types=1);

use App\Actions\ClearOldCheckHistory;
use App\Models\Check;
use App\Models\CheckHistory;
use App\Models\User;

test('deletes old check history', function () {
    $user = User::factory()->create();
    $check = Check::factory()->create(['user_id' => $user->id]);

    // Create old history (31 days ago)
    $oldHistory = CheckHistory::factory()->create([
        'check_id' => $check->id,
        'created_at' => now()->subDays(31),
    ]);

    // Create recent history (15 days ago)
    $recentHistory = CheckHistory::factory()->create([
        'check_id' => $check->id,
        'created_at' => now()->subDays(15),
    ]);

    $action = app(ClearOldCheckHistory::class);
    $deletedCount = $action->handle(30);

    expect($deletedCount)->toBe(1);
    $this->assertDatabaseMissing('check_history', ['id' => $oldHistory->id]);
    $this->assertDatabaseHas('check_history', ['id' => $recentHistory->id]);
});

test('does not delete when retention is zero', function () {
    $user = User::factory()->create();
    $check = Check::factory()->create(['user_id' => $user->id]);

    CheckHistory::factory()->count(3)->create([
        'check_id' => $check->id,
        'created_at' => now()->subDays(100),
    ]);

    $action = app(ClearOldCheckHistory::class);
    $deletedCount = $action->handle(0);

    expect($deletedCount)->toBe(0);
    $this->assertDatabaseCount('check_history', 3);
});

test('does not delete when retention is negative', function () {
    $user = User::factory()->create();
    $check = Check::factory()->create(['user_id' => $user->id]);

    CheckHistory::factory()->count(2)->create([
        'check_id' => $check->id,
        'created_at' => now()->subDays(100),
    ]);

    $action = app(ClearOldCheckHistory::class);
    $deletedCount = $action->handle(-1);

    expect($deletedCount)->toBe(0);
    $this->assertDatabaseCount('check_history', 2);
});

test('returns zero when no old records exist', function () {
    $user = User::factory()->create();
    $check = Check::factory()->create(['user_id' => $user->id]);

    CheckHistory::factory()->create([
        'check_id' => $check->id,
        'created_at' => now()->subDays(5),
    ]);

    $action = app(ClearOldCheckHistory::class);
    $deletedCount = $action->handle(30);

    expect($deletedCount)->toBe(0);
    $this->assertDatabaseCount('check_history', 1);
});
