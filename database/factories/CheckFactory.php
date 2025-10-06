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
            'http_method' => \App\Enums\HTTPMethod::GET,
            'interval' => $this->faker->numberBetween(1, 60),
            'request_timeout' => 30,
            'request_headers' => [],
            'request_body' => [],
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
}
