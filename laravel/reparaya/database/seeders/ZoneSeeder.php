<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Zone;
use Illuminate\Database\Seeder;

class ZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['Centro', 'Norte', 'Sur', 'Este', 'Oeste'] as $name) {
            Zone::firstOrCreate(['name' => $name]);
        }
    }
}
