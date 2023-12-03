<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game>
 */
class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'app_id' => function () {
                if ($this->faker->boolean(5)) {
                    return null; // package, no app id
                }

                return $this->faker->unique()->numberBetween(1, 100000);
            },
            'package_id' => function (array $attributes) {
                if ($attributes['app_id']) {
                    return null; // app, no package id
                }

                return $this->faker->unique()->numberBetween(1, 10000);
            },
            'name' => $this->faker->unique()->sentence(6),
        ];
    }
}
