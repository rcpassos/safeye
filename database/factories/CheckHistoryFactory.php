<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CheckHistory>
 */
final class CheckHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'check_id' => \App\Models\Check::factory(),
            'notified_emails' => $this->faker->email(),
            'metadata' => ['response_time' => $this->faker->numberBetween(100, 5000)],
            'root_cause' => [],
            'type' => $this->faker->randomElement(\App\Enums\CheckHistoryType::cases()),
        ];
    }
}
