<?php

namespace Database\Seeders;

use App\Models\FingerprintRawLog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FingerprintRawLogSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FingerprintRawLog::factory(50)->create();
    }
}
