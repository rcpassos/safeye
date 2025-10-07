<?php

declare(strict_types=1);

use App\Models\User;

test('user can have timezone', function () {
    $user = User::factory()->create([
        'timezone' => 'America/New_York',
    ]);

    expect($user->timezone)->toBe('America/New_York');
});

test('new user defaults to utc', function () {
    $user = User::factory()->create();

    expect($user->timezone)->toBe('UTC');
});

test('middleware sets application timezone', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'timezone' => 'America/Los_Angeles',
    ]);

    $this->actingAs($user);

    $this->get('/app');

    expect(config('app.timezone'))->toBe('America/Los_Angeles');
});

test('application timezone remains utc for guests', function () {
    $this->get('/');

    expect(config('app.timezone'))->toBe('UTC');
});

test('user can update timezone in profile', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'timezone' => 'UTC',
    ]);

    $this->actingAs($user);

    // Simulate updating the profile with a new timezone
    $user->update(['timezone' => 'America/Chicago']);

    // After update, middleware should apply the new timezone
    $this->get('/app');

    expect($user->fresh()->timezone)->toBe('America/Chicago');
});
