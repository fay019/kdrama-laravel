<?php

namespace Database\Factories;

use App\Models\Actor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Actor>
 */
class ActorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tmdb_id' => $this->faker->unique()->numberBetween(1000000, 9999999),
            'name' => $this->faker->name(),
            'en_name' => $this->faker->name(),
            'biography' => $this->faker->paragraph(),
            'profile_path' => '/path/to/profile.jpg',
            'birthday' => $this->faker->dateTimeBetween('-80 years', '-18 years'),
            'birthplace' => $this->faker->city(),
            'known_for' => json_encode([]),
            'combined_credits' => json_encode(['cast' => []]),
            'tv_credits_count' => $this->faker->numberBetween(1, 50),
            'popularity' => $this->faker->randomFloat(1, 1, 100),
            'external_ids' => json_encode([]),
            'last_synced_at' => now(),
        ];
    }
}
