<?php

namespace Database\Factories;

use App\Models\Player;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Player>
 */
class PlayerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Player::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->firstName();

        $byondMajor = $this->faker->randomElement([515, 516]);
        $byondMinor = $byondMajor === 515 ? $this->faker->numberBetween(1589, 1648) : $this->faker->numberBetween(1647, 1667);

        return [
            'ckey' => ckey($name),
            'key' => $name,
            'byond_join_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'byond_major' => $byondMajor,
            'byond_minor' => $byondMinor,
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
