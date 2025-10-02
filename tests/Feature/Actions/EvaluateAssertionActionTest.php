<?php

declare(strict_types=1);

namespace Tests\Feature\Actions;

use App\Actions\EvaluateAssertion;
use App\Enums\AssertionSign;
use App\Enums\AssertionType;
use App\Models\Assertion;
use App\Models\Check;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EvaluateAssertionActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_evaluates_less_than_correctly(): void
    {
        $user = User::factory()->create();
        $check = Check::factory()->create(['user_id' => $user->id]);
        $assertion = Assertion::factory()->create([
            'check_id' => $check->id,
            'type' => AssertionType::RESPONSE_TIME,
            'sign' => AssertionSign::LESS_THAN,
            'value' => '1.0',
        ]);

        $action = app(EvaluateAssertion::class);

        $this->assertTrue($action->handle($assertion, 0.5));
        $this->assertFalse($action->handle($assertion, 1.0));
        $this->assertFalse($action->handle($assertion, 1.5));
    }

    public function test_evaluates_less_than_or_equal_correctly(): void
    {
        $user = User::factory()->create();
        $check = Check::factory()->create(['user_id' => $user->id]);
        $assertion = Assertion::factory()->create([
            'check_id' => $check->id,
            'type' => AssertionType::RESPONSE_CODE,
            'sign' => AssertionSign::LESS_THAN_OR_EQUAL,
            'value' => '200',
        ]);

        $action = app(EvaluateAssertion::class);

        $this->assertTrue($action->handle($assertion, 199));
        $this->assertTrue($action->handle($assertion, 200));
        $this->assertFalse($action->handle($assertion, 201));
    }

    public function test_evaluates_equal_correctly(): void
    {
        $user = User::factory()->create();
        $check = Check::factory()->create(['user_id' => $user->id]);
        $assertion = Assertion::factory()->create([
            'check_id' => $check->id,
            'type' => AssertionType::RESPONSE_CODE,
            'sign' => AssertionSign::EQUAL,
            'value' => '200',
        ]);

        $action = app(EvaluateAssertion::class);

        $this->assertTrue($action->handle($assertion, 200));
        $this->assertTrue($action->handle($assertion, 200.0005)); // Within tolerance
        $this->assertFalse($action->handle($assertion, 201));
    }

    public function test_evaluates_not_equal_correctly(): void
    {
        $user = User::factory()->create();
        $check = Check::factory()->create(['user_id' => $user->id]);
        $assertion = Assertion::factory()->create([
            'check_id' => $check->id,
            'type' => AssertionType::RESPONSE_CODE,
            'sign' => AssertionSign::NOT_EQUAL,
            'value' => '200',
        ]);

        $action = app(EvaluateAssertion::class);

        $this->assertFalse($action->handle($assertion, 200));
        $this->assertTrue($action->handle($assertion, 404));
    }

    public function test_evaluates_greater_than_correctly(): void
    {
        $user = User::factory()->create();
        $check = Check::factory()->create(['user_id' => $user->id]);
        $assertion = Assertion::factory()->create([
            'check_id' => $check->id,
            'type' => AssertionType::RESPONSE_TIME,
            'sign' => AssertionSign::GREATER_THAN,
            'value' => '1.0',
        ]);

        $action = app(EvaluateAssertion::class);

        $this->assertFalse($action->handle($assertion, 0.5));
        $this->assertFalse($action->handle($assertion, 1.0));
        $this->assertTrue($action->handle($assertion, 1.5));
    }

    public function test_evaluates_greater_than_or_equal_correctly(): void
    {
        $user = User::factory()->create();
        $check = Check::factory()->create(['user_id' => $user->id]);
        $assertion = Assertion::factory()->create([
            'check_id' => $check->id,
            'type' => AssertionType::RESPONSE_CODE,
            'sign' => AssertionSign::GREATER_THAN_OR_EQUAL,
            'value' => '200',
        ]);

        $action = app(EvaluateAssertion::class);

        $this->assertFalse($action->handle($assertion, 199));
        $this->assertTrue($action->handle($assertion, 200));
        $this->assertTrue($action->handle($assertion, 201));
    }
}
