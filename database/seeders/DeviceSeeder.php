<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Device;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeviceSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua client yang sudah ada
        $clients = Client::all();

        if ($clients->isEmpty()) {
            return;
        }

        // Buat device untuk client default SIMKAS (1 device)
        $simkas = $clients->firstWhere('code', 'SIMKAS');
        if ($simkas && Device::where('code', 'DEV-SIMKAS-01')->doesntExist()) {
            Device::factory()->create([
                'code' => 'DEV-SIMKAS-01',
                'serial_number' => 'SN-SIMKAS-001',
                'device_name' => 'SIMKAS Main Device',
                'client_id' => $simkas->id,
            ]);
        }

        // Buat 2 device untuk setiap client lainnya
        foreach ($clients->where('code', '!=', 'SIMKAS') as $client) {
            Device::factory(2)->create([
                'client_id' => $client->id,
            ]);
        }
    }
}
