<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GameRound>
 */
class GameRoundFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'server_id' => $this->faker->randomElement(['main1', 'main2', 'main3', 'main4']),
            'map' => $this->faker->randomElement(['COGMAP', 'COGMAP2', 'DESTINY', 'OSHAN']),
            'game_type' => $this->faker->randomElement(['secret', 'mixed', 'extended']),
            'rp_mode' => false,
            'crashed' => false,
            'ended_at' => null,
            'created_at' => null,
            'updated_at' => null,
        ];
    }
}
