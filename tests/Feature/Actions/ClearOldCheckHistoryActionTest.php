<?php

declare(strict_types=1);

namespace Tests\Feature\Actions;

use App\Actions\ClearOldCheckHistory;
use App\Models\Check;
use App\Models\CheckHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ClearOldCheckHistoryActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deletes_old_check_history(): void
    {
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

        $this->assertEquals(1, $deletedCount);
        $this->assertDatabaseMissing('check_history', ['id' => $oldHistory->id]);
        $this->assertDatabaseHas('check_history', ['id' => $recentHistory->id]);
    }

    public function test_does_not_delete_when_retention_is_zero(): void
    {
        $user = User::factory()->create();
        $check = Check::factory()->create(['user_id' => $user->id]);

        CheckHistory::factory()->count(3)->create([
            'check_id' => $check->id,
            'created_at' => now()->subDays(100),
        ]);

        $action = app(ClearOldCheckHistory::class);
        $deletedCount = $action->handle(0);

        $this->assertEquals(0, $deletedCount);
        $this->assertDatabaseCount('check_history', 3);
    }

    public function test_does_not_delete_when_retention_is_negative(): void
    {
        $user = User::factory()->create();
        $check = Check::factory()->create(['user_id' => $user->id]);

        CheckHistory::factory()->count(2)->create([
            'check_id' => $check->id,
            'created_at' => now()->subDays(100),
        ]);

        $action = app(ClearOldCheckHistory::class);
        $deletedCount = $action->handle(-1);

        $this->assertEquals(0, $deletedCount);
        $this->assertDatabaseCount('check_history', 2);
    }

    public function test_returns_zero_when_no_old_records_exist(): void
    {
        $user = User::factory()->create();
        $check = Check::factory()->create(['user_id' => $user->id]);

        CheckHistory::factory()->create([
            'check_id' => $check->id,
            'created_at' => now()->subDays(5),
        ]);

        $action = app(ClearOldCheckHistory::class);
        $deletedCount = $action->handle(30);

        $this->assertEquals(0, $deletedCount);
        $this->assertDatabaseCount('check_history', 1);
    }
}
