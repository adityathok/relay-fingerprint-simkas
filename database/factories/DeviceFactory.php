<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Device>
 */
class DeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->bothify('DEV-####')),
            'serial_number' => strtoupper(fake()->bothify('SN-????-####')),
            'device_name' => fake()->word().' Device',
            'client_id' => Client::factory(),
        ];
    }
}
