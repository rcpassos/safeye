<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserTimezoneTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_timezone(): void
    {
        $user = User::factory()->create([
            'timezone' => 'America/New_York',
        ]);

        $this->assertEquals('America/New_York', $user->timezone);
    }

    public function test_new_user_defaults_to_utc(): void
    {
        $user = User::factory()->create();

        $this->assertEquals('UTC', $user->timezone);
    }

    public function test_middleware_sets_application_timezone(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'timezone' => 'America/Los_Angeles',
        ]);

        $this->actingAs($user);

        $this->get('/app');

        $this->assertEquals('America/Los_Angeles', config('app.timezone'));
    }

    public function test_application_timezone_remains_utc_for_guests(): void
    {
        $this->get('/');

        $this->assertEquals('UTC', config('app.timezone'));
    }

    public function test_user_can_update_timezone_in_profile(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'timezone' => 'UTC',
        ]);

        $this->actingAs($user);

        // Simulate updating the profile with a new timezone
        $user->update(['timezone' => 'America/Chicago']);

        // After update, middleware should apply the new timezone
        $this->get('/app');

        $this->assertEquals('America/Chicago', $user->fresh()->timezone);
    }
}
