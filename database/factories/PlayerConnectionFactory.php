<?php

namespace Database\Factories;

use App\Models\PlayerConnection;
use GeoIp2\Database\Reader;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlayerConnection>
 */
class PlayerConnectionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PlayerConnection::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ip = $this->faker->ipv4();
        $geoRecord = null;
        try {
            $geoReader = new Reader(storage_path('app').'/GeoLite2/GeoLite2-Country.mmdb');
            $geoRecord = $geoReader->country($ip);
        } catch (\Throwable $e) {
            // pass
        }

        return [
            'ip' => $ip,
            'comp_id' => $this->faker->numberBetween(10000000, 99999999),
            'country' => $geoRecord ? $geoRecord->country->name : null,
            'country_iso' => $geoRecord ? $geoRecord->country->isoCode : null,
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
