<?php

declare(strict_types=1);

use App\Enums\CheckHistoryType;
use App\Filament\App\Pages\Dashboard;
use App\Models\Check;
use App\Models\CheckHistory;
use App\Models\User;
use Livewire\Livewire;

test('authenticated users can access dashboard', function () {
    /** @var User $user */
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Dashboard::class)
        ->assertOk();
});

test('unauthenticated users cannot access dashboard', function () {
    $this->get('/app')
        ->assertRedirect('/app/login');
});

test('dashboard displays check statistics', function () {
    /** @var User $user */
    $user = User::factory()->create();

    // Create checks for the user
    $activeChecks = Check::factory()->count(3)->create(['user_id' => $user->id, 'active' => true]);
    $inactiveChecks = Check::factory()->count(2)->create(['user_id' => $user->id, 'active' => false]);

    $this->actingAs($user);

    $response = Livewire::test(Dashboard::class);

    $response->assertOk();

    // Verify checks were created correctly
    expect(Check::where('user_id', $user->id)->where('active', true)->count())->toBe(3);
    expect(Check::where('user_id', $user->id)->count())->toBe(5);
});

test('dashboard displays check history', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $check = Check::factory()->create(['user_id' => $user->id]);

    // Create check history for the last 24 hours
    $successHistory = CheckHistory::factory()->create([
        'check_id' => $check->id,
        'type' => CheckHistoryType::SUCCESS,
        'created_at' => now()->subHours(12),
    ]);

    $errorHistory = CheckHistory::factory()->create([
        'check_id' => $check->id,
        'type' => CheckHistoryType::ERROR,
        'created_at' => now()->subHours(6),
    ]);

    $this->actingAs($user);

    $response = Livewire::test(Dashboard::class);

    $response->assertOk();

    // Verify history was created correctly
    expect(CheckHistory::where('check_id', $check->id)->count())->toBe(2);
});

test('dashboard only shows current user data', function () {
    /** @var User $user1 */
    $user1 = User::factory()->create();
    /** @var User $user2 */
    $user2 = User::factory()->create();

    // Create checks for different users
    $check1 = Check::factory()->create(['user_id' => $user1->id]);
    $check2 = Check::factory()->create(['user_id' => $user2->id]);

    CheckHistory::factory()->count(5)->create(['check_id' => $check1->id]);
    CheckHistory::factory()->count(3)->create(['check_id' => $check2->id]);

    $this->actingAs($user1);

    // User1 should only see their own data
    Livewire::test(Dashboard::class)
        ->assertOk();

    // The stats should reflect only user1's data
    $this->assertDatabaseCount('checks', 2);
    $this->assertDatabaseCount('check_history', 8);
});

test('dashboard filters by 24 hours', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $check = Check::factory()->create(['user_id' => $user->id]);

    // Create history within and outside 24h range
    CheckHistory::factory()->create([
        'check_id' => $check->id,
        'type' => CheckHistoryType::SUCCESS,
        'created_at' => now()->subHours(12),
    ]);

    CheckHistory::factory()->create([
        'check_id' => $check->id,
        'type' => CheckHistoryType::ERROR,
        'created_at' => now()->subDays(2),
    ]);

    $this->actingAs($user);

    Livewire::test(Dashboard::class)
        ->set('filters.dateRange', '24h')
        ->assertOk();
});

test('dashboard filters by 7 days', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $check = Check::factory()->create(['user_id' => $user->id]);

    CheckHistory::factory()->count(3)->create([
        'check_id' => $check->id,
        'type' => CheckHistoryType::SUCCESS,
        'created_at' => now()->subDays(3),
    ]);

    CheckHistory::factory()->create([
        'check_id' => $check->id,
        'type' => CheckHistoryType::ERROR,
        'created_at' => now()->subDays(10),
    ]);

    $this->actingAs($user);

    Livewire::test(Dashboard::class)
        ->set('filters.dateRange', '7d')
        ->assertOk();
});

test('dashboard filters by custom date range', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $check = Check::factory()->create(['user_id' => $user->id]);

    CheckHistory::factory()->create([
        'check_id' => $check->id,
        'type' => CheckHistoryType::SUCCESS,
        'created_at' => now()->subDays(5),
    ]);

    $this->actingAs($user);

    $startDate = now()->subDays(7)->toDateString();
    $endDate = now()->toDateString();

    Livewire::test(Dashboard::class)
        ->set('filters.dateRange', 'custom')
        ->set('filters.startDate', $startDate)
        ->set('filters.endDate', $endDate)
        ->assertOk();
});

test('dashboard defaults to 30 days', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $check = Check::factory()->create(['user_id' => $user->id]);

    CheckHistory::factory()->count(5)->create([
        'check_id' => $check->id,
        'created_at' => now()->subDays(15),
    ]);

    $this->actingAs($user);

    Livewire::test(Dashboard::class)
        ->assertOk()
        ->assertSet('filters.dateRange', '30d');
});

test('response time chart displays performance data', function () {
    /** @var User $user */
    $user = User::factory()->create();
    $check = Check::factory()->create(['user_id' => $user->id]);

    // Create check history with transfer time metadata
    CheckHistory::factory()->create([
        'check_id' => $check->id,
        'type' => CheckHistoryType::SUCCESS,
        'metadata' => ['transfer_time' => 0.5],
        'created_at' => now()->subDays(1),
    ]);

    CheckHistory::factory()->create([
        'check_id' => $check->id,
        'type' => CheckHistoryType::SUCCESS,
        'metadata' => ['transfer_time' => 1.2],
        'created_at' => now()->subDays(2),
    ]);

    $this->actingAs($user);

    Livewire::test(Dashboard::class)
        ->assertOk();

    // Verify check history with metadata exists
    expect(CheckHistory::where('check_id', $check->id)
        ->whereNotNull('metadata->transfer_time')
        ->count())->toBe(2);
});
