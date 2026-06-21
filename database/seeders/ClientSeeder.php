<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat client default
        if (Client::where('code', 'SIMKAS')->first() === null) {
            Client::factory()->create([
                'name' => 'Simkas Relay',
                'code' => 'SIMKAS',
                'domain' => 'simkas.example.com',
            ]);
        }

        // Buat 5 client dummy
        Client::factory(5)->create();
    }
}
