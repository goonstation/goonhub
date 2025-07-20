<?php

namespace Database\Factories;

use App\Models\PlayerParticipation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlayerParticipation>
 */
class PlayerParticipationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PlayerParticipation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job' => $this->faker->randomElement([
                'Staff Assistant',
                'Captain',
                'Head of Security',
                'Head of Personnel',
                'Research Director',
                'Janitor',
                'Clown',
                'Mime',
                'Botanist',
                'Chef',
                'Chaplain',
            ]),
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
