<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // jika user 'admin@example.com' belum ada, maka buat user baru
        if (User::where('email', 'admin@example.com')->first() === null) {
            User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('123456'),
            ]);
        }

        $this->call([
            ClientSeeder::class,
            DeviceSeeder::class,
            FingerprintRawLogSeeder::class,
        ]);
    }
}
