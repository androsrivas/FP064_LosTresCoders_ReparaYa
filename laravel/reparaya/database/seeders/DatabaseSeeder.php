<?php
declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/** Datos de catálogo (también en producción): php artisan db:seed
 * Datos de demostración: php artisan db:seed --class=DemoSeeder
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SpecialitySeeder::class,
            ZoneSeeder::class,
        ]);       
    }
}
