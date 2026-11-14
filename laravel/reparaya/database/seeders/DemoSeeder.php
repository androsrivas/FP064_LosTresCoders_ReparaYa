<?php
declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/** php artisan db:seed --class=DemoSeeder (require antes el catálogo) */
final class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            DemoUserSeeder::class,
            ManagementCompanySeeder::class,
            DemoIncidentSeeder::class,
        ]);
    }
}
