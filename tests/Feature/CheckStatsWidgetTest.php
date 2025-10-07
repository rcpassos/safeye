<?php

declare(strict_types=1);

use App\Enums\CheckHistoryType;
use App\Models\Check;
use App\Models\CheckHistory;
use App\Models\User;

test('shows most recent check information', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $check = Check::factory()->create(['user_id' => $user->id]);

    // Create older check (15 hours ago, SUCCESS)
    CheckHistory::factory()->create([
        'check_id' => $check->id,
        'type' => CheckHistoryType::SUCCESS,
        'created_at' => now()->subHours(15),
    ]);

    // Create most recent check (1 minute ago, ERROR)
    $recentCheck = CheckHistory::factory()->create([
        'check_id' => $check->id,
        'type' => CheckHistoryType::ERROR,
        'created_at' => now()->subMinute(),
    ]);

    $this->actingAs($user);

    // Refresh the check to reload relationships
    $check->refresh();

    // The most recent check should be first (ordered desc)
    $mostRecentCheck = $check->latestChecks->first();

    $this->assertNotNull($mostRecentCheck);
    expect($mostRecentCheck->id)->toBe($recentCheck->id);
    expect($mostRecentCheck->type)->toBe(CheckHistoryType::ERROR);

    // Verify it's more recent than 10 minutes
    expect($mostRecentCheck->created_at->isAfter(now()->subMinutes(10)))->toBeTrue();
});

test('shows correct uptime percentage', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $check = Check::factory()->create(['user_id' => $user->id]);

    // Create 8 successful checks
    CheckHistory::factory()->count(8)->create([
        'check_id' => $check->id,
        'type' => CheckHistoryType::SUCCESS,
        'created_at' => now()->subHours(rand(1, 23)),
    ]);

    // Create 2 error checks
    CheckHistory::factory()->count(2)->create([
        'check_id' => $check->id,
        'type' => CheckHistoryType::ERROR,
        'created_at' => now()->subHours(rand(1, 23)),
    ]);

    $this->actingAs($user);

    // Refresh to load relationships
    $check->refresh();

    // Total checks should be 10
    expect($check->latestChecks->count())->toBe(10);

    // Error checks should be 2
    expect($check->latestIssues->count())->toBe(2);

    // Uptime should be 80% ((10-2)/10 * 100)
    $uptime = ceil(($check->latestChecks->count() - $check->latestIssues->count()) * 100 / $check->latestChecks->count());
    expect($uptime)->toBe(80.0);
});
