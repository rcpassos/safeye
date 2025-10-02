<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\AssertionSign;
use App\Models\Assertion;

final class EvaluateAssertion
{
    public function handle(Assertion $assertion, float $actualValue): bool
    {
        $expectedValue = (float) $assertion->value;

        return match ($assertion->sign) {
            AssertionSign::LESS_THAN => $actualValue < $expectedValue,
            AssertionSign::LESS_THAN_OR_EQUAL => $actualValue <= $expectedValue,
            AssertionSign::EQUAL => abs($actualValue - $expectedValue) < 0.001,
            AssertionSign::NOT_EQUAL => abs($actualValue - $expectedValue) >= 0.001,
            AssertionSign::GREATER_THAN => $actualValue > $expectedValue,
            AssertionSign::GREATER_THAN_OR_EQUAL => $actualValue >= $expectedValue,
            default => false,
        };
    }
}
