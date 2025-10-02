<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AssertionSign;
use App\Enums\AssertionType;
use App\Models\Check;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Assertion>
 */
final class AssertionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'check_id' => Check::factory(),
            'type' => AssertionType::RESPONSE_CODE,
            'sign' => AssertionSign::EQUAL,
            'value' => '200',
        ];
    }
}
