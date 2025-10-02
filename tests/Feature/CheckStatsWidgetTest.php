<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\CheckHistoryType;
use App\Models\Check;
use App\Models\CheckHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CheckStatsWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_shows_most_recent_check_information(): void
    {
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
        $this->assertEquals($recentCheck->id, $mostRecentCheck->id);
        $this->assertEquals(CheckHistoryType::ERROR, $mostRecentCheck->type);

        // Verify it's more recent than 10 minutes
        $this->assertTrue($mostRecentCheck->created_at->isAfter(now()->subMinutes(10)));
    }

    public function test_shows_correct_uptime_percentage(): void
    {
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
        $this->assertEquals(10, $check->latestChecks->count());

        // Error checks should be 2
        $this->assertEquals(2, $check->latestIssues->count());

        // Uptime should be 80% ((10-2)/10 * 100)
        $uptime = ceil(($check->latestChecks->count() - $check->latestIssues->count()) * 100 / $check->latestChecks->count());
        $this->assertEquals(80, $uptime);
    }
}
