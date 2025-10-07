<?php

declare(strict_types=1);

use App\Actions\EvaluateAssertion;
use App\Enums\AssertionSign;
use App\Enums\AssertionType;
use App\Models\Assertion;
use App\Models\Check;
use App\Models\User;

test('evaluates less than correctly', function () {
    $user = User::factory()->create();
    $check = Check::factory()->create(['user_id' => $user->id]);
    $assertion = Assertion::factory()->create([
        'check_id' => $check->id,
        'type' => AssertionType::RESPONSE_TIME,
        'sign' => AssertionSign::LESS_THAN,
        'value' => '1.0',
    ]);

    $action = app(EvaluateAssertion::class);

    expect($action->handle($assertion, 0.5))->toBeTrue();
    expect($action->handle($assertion, 1.0))->toBeFalse();
    expect($action->handle($assertion, 1.5))->toBeFalse();
});

test('evaluates less than or equal correctly', function () {
    $user = User::factory()->create();
    $check = Check::factory()->create(['user_id' => $user->id]);
    $assertion = Assertion::factory()->create([
        'check_id' => $check->id,
        'type' => AssertionType::RESPONSE_CODE,
        'sign' => AssertionSign::LESS_THAN_OR_EQUAL,
        'value' => '200',
    ]);

    $action = app(EvaluateAssertion::class);

    expect($action->handle($assertion, 199))->toBeTrue();
    expect($action->handle($assertion, 200))->toBeTrue();
    expect($action->handle($assertion, 201))->toBeFalse();
});

test('evaluates equal correctly', function () {
    $user = User::factory()->create();
    $check = Check::factory()->create(['user_id' => $user->id]);
    $assertion = Assertion::factory()->create([
        'check_id' => $check->id,
        'type' => AssertionType::RESPONSE_CODE,
        'sign' => AssertionSign::EQUAL,
        'value' => '200',
    ]);

    $action = app(EvaluateAssertion::class);

    expect($action->handle($assertion, 200))->toBeTrue();
    expect($action->handle($assertion, 200.0005))->toBeTrue(); // Within tolerance
    expect($action->handle($assertion, 201))->toBeFalse();
});

test('evaluates not equal correctly', function () {
    $user = User::factory()->create();
    $check = Check::factory()->create(['user_id' => $user->id]);
    $assertion = Assertion::factory()->create([
        'check_id' => $check->id,
        'type' => AssertionType::RESPONSE_CODE,
        'sign' => AssertionSign::NOT_EQUAL,
        'value' => '200',
    ]);

    $action = app(EvaluateAssertion::class);

    expect($action->handle($assertion, 200))->toBeFalse();
    expect($action->handle($assertion, 404))->toBeTrue();
});

test('evaluates greater than correctly', function () {
    $user = User::factory()->create();
    $check = Check::factory()->create(['user_id' => $user->id]);
    $assertion = Assertion::factory()->create([
        'check_id' => $check->id,
        'type' => AssertionType::RESPONSE_TIME,
        'sign' => AssertionSign::GREATER_THAN,
        'value' => '1.0',
    ]);

    $action = app(EvaluateAssertion::class);

    expect($action->handle($assertion, 0.5))->toBeFalse();
    expect($action->handle($assertion, 1.0))->toBeFalse();
    expect($action->handle($assertion, 1.5))->toBeTrue();
});

test('evaluates greater than or equal correctly', function () {
    $user = User::factory()->create();
    $check = Check::factory()->create(['user_id' => $user->id]);
    $assertion = Assertion::factory()->create([
        'check_id' => $check->id,
        'type' => AssertionType::RESPONSE_CODE,
        'sign' => AssertionSign::GREATER_THAN_OR_EQUAL,
        'value' => '200',
    ]);

    $action = app(EvaluateAssertion::class);

    expect($action->handle($assertion, 199))->toBeFalse();
    expect($action->handle($assertion, 200))->toBeTrue();
    expect($action->handle($assertion, 201))->toBeTrue();
});
