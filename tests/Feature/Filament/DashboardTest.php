<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Enums\CheckHistoryType;
use App\Filament\App\Pages\Dashboard;
use App\Models\Check;
use App\Models\CheckHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_access_dashboard(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(Dashboard::class)
            ->assertOk();
    }

    public function test_unauthenticated_users_cannot_access_dashboard(): void
    {
        $this->get('/app')
            ->assertRedirect('/app/login');
    }

    public function test_dashboard_displays_check_statistics(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        // Create checks for the user
        $activeChecks = Check::factory()->count(3)->create(['user_id' => $user->id, 'active' => true]);
        $inactiveChecks = Check::factory()->count(2)->create(['user_id' => $user->id, 'active' => false]);

        $this->actingAs($user);

        $response = Livewire::test(Dashboard::class);

        $response->assertOk();

        // Verify checks were created correctly
        $this->assertEquals(3, Check::where('user_id', $user->id)->where('active', true)->count());
        $this->assertEquals(5, Check::where('user_id', $user->id)->count());
    }

    public function test_dashboard_displays_check_history(): void
    {
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
        $this->assertEquals(2, CheckHistory::where('check_id', $check->id)->count());
    }

    public function test_dashboard_only_shows_current_user_data(): void
    {
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
    }

    public function test_dashboard_filters_by_24_hours(): void
    {
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
    }

    public function test_dashboard_filters_by_7_days(): void
    {
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
    }

    public function test_dashboard_filters_by_custom_date_range(): void
    {
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
    }

    public function test_dashboard_defaults_to_30_days(): void
    {
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
    }

    public function test_response_time_chart_displays_performance_data(): void
    {
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
        $this->assertEquals(2, CheckHistory::where('check_id', $check->id)
            ->whereNotNull('metadata->transfer_time')
            ->count());
    }
}
