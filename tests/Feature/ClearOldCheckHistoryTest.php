<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\CheckHistory;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ClearOldCheckHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_deletes_old_check_history_records(): void
    {
        // Create some old records (older than 30 days)
        $oldRecords = CheckHistory::factory(3)->create([
            'created_at' => Carbon::now()->subDays(35),
        ]);

        // Create some recent records (within 30 days)
        $recentRecords = CheckHistory::factory(2)->create([
            'created_at' => Carbon::now()->subDays(15),
        ]);

        // Run the command
        $this->artisan('app:clear-old-check-history')
            ->expectsOutput('Successfully deleted 3 old check history record(s) older than 30 days.')
            ->assertExitCode(0);

        // Assert old records are deleted
        foreach ($oldRecords as $record) {
            $this->assertDatabaseMissing('check_history', ['id' => $record->id]);
        }

        // Assert recent records still exist
        foreach ($recentRecords as $record) {
            $this->assertDatabaseHas('check_history', ['id' => $record->id]);
        }
    }

    public function test_command_with_custom_retention_period(): void
    {
        config(['app.check_history_retention_days' => 7]);

        // Create records older than 7 days
        $oldRecords = CheckHistory::factory(2)->create([
            'created_at' => Carbon::now()->subDays(10),
        ]);

        // Create records within 7 days
        $recentRecords = CheckHistory::factory(2)->create([
            'created_at' => Carbon::now()->subDays(5),
        ]);

        $this->artisan('app:clear-old-check-history')
            ->expectsOutput('Successfully deleted 2 old check history record(s) older than 7 days.')
            ->assertExitCode(0);

        // Assert old records are deleted
        foreach ($oldRecords as $record) {
            $this->assertDatabaseMissing('check_history', ['id' => $record->id]);
        }

        // Assert recent records still exist
        foreach ($recentRecords as $record) {
            $this->assertDatabaseHas('check_history', ['id' => $record->id]);
        }
    }

    public function test_command_when_no_old_records_exist(): void
    {
        // Create only recent records
        CheckHistory::factory(3)->create([
            'created_at' => Carbon::now()->subDays(15),
        ]);

        $this->artisan('app:clear-old-check-history')
            ->expectsOutput('No old check history records found to delete.')
            ->assertExitCode(0);

        // Assert all records still exist
        $this->assertDatabaseCount('check_history', 3);
    }

    public function test_command_when_retention_is_disabled(): void
    {
        config(['app.check_history_retention_days' => 0]);

        CheckHistory::factory(3)->create([
            'created_at' => Carbon::now()->subDays(100),
        ]);

        $this->artisan('app:clear-old-check-history')
            ->expectsOutput('Check history retention is disabled (retention days is 0 or negative).')
            ->assertExitCode(0);

        // Assert no records are deleted
        $this->assertDatabaseCount('check_history', 3);
    }

    public function test_command_with_negative_retention_days(): void
    {
        config(['app.check_history_retention_days' => -5]);

        CheckHistory::factory(2)->create([
            'created_at' => Carbon::now()->subDays(50),
        ]);

        $this->artisan('app:clear-old-check-history')
            ->expectsOutput('Check history retention is disabled (retention days is 0 or negative).')
            ->assertExitCode(0);

        // Assert no records are deleted
        $this->assertDatabaseCount('check_history', 2);
    }
}
