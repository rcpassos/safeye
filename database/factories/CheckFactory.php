<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Check>
 */
final class CheckFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'name' => $this->faker->sentence(3),
            'type' => \App\Enums\CheckType::HTTP,
            'endpoint' => $this->faker->url(),
            'interval' => $this->faker->numberBetween(60, 300),
            'config' => [
                'method' => \App\Enums\HTTPMethod::GET->value,
                'timeout' => 10,
                'headers' => [],
                'body' => null,
            ],
            'notify_emails' => $this->faker->email(),
            'slack_webhook_url' => null,
            'active' => true,
        ];
    }

    /**
     * Indicate that the check has a Slack webhook configured.
     */
    public function withSlack(): static
    {
        return $this->state(fn (array $attributes): array => [
            'slack_webhook_url' => 'https://hooks.slack.com/services/'.fake()->uuid(),
        ]);
    }

    /**
     * Indicate that the check is a ping check.
     */
    public function ping(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => \App\Enums\CheckType::PING,
            'endpoint' => $this->faker->domainName(),
            'config' => [
                'count' => $this->faker->numberBetween(1, 10),
                'timeout' => $this->faker->numberBetween(5, 30),
            ],
        ]);
    }
}
