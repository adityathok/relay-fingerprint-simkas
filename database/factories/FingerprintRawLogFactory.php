<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\FingerprintRawLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FingerprintRawLog>
 */
class FingerprintRawLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'device_sn' => Device::inRandomOrder()->first()?->serial_number ?? strtoupper(fake()->bothify('SN-????-####')),
            'raw_payload' => json_encode([
                'uid' => fake()->numerify('######'),
                'fingerprint_id' => fake()->numberBetween(1, 10),
                'timestamp' => fake()->unixTime(),
                'status' => fake()->randomElement(['success', 'failed']),
            ]),
            'retry_count' => fake()->numberBetween(0, 3),
        ];
    }
}
