<?php

namespace Database\Factories;

use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GamedataSteamgifts>
 */
class GamedataSteamgiftsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'game_id' => Game::factory(),
            'cv_reduced_at' => $this->faker->boolean(95) ? $this->faker->dateTimeBetween('-2 year', 'now') : null,
            'cv_removed_at' => $this->faker->boolean(20) ? $this->faker->dateTimeBetween('-1 year', 'now') : null,
        ];
    }
}
