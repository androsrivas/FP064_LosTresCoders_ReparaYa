<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Speciality;
use Illuminate\Database\Seeder;

/** Especialidades y precios base del P2 */
final class SpecialitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $specialities = [
            [
                'name' => 'Fontanería',
                'description' => 'Servicio especializado en reparación de tuberías, grifería, fugas de agua y desatascos.',
                'responsibilities' => 'Inspección de tuberías, reparación de fugas, instalación de sanitarios y resolución de atascos.',
                'base_price' => '35.00',
                'base_salary' => '1800.00',
            ],
            [
                'name' => 'Electricidad',
                'description' => 'Diagnóstico y reparación de averías eléctricas, cuadros principales e instalaciones.',
                'responsibilities' => 'Revisión de cableado, reparación de cortocircuitos, sustitución de diferenciales y montaje de mecanismos.',
                'base_price' => '40.00',
                'base_salary' => '1950.00',
            ],
            [
                'name' => 'Climatización',
                'description' => 'Mantenimiento, carga de gas y reparación de sistemas de aire acondicionado y calefacción.',
                'responsibilities' => 'Limpieza de filtros, recarga de refrigerante, diagnóstico de compresores y calibración de termostatos.',
                'base_price' => '45.00',
                'base_salary' => '2100.00',
            ],
            [
                'name' => 'Carpintería',
                'description' => 'Reparación y ajuste de puertas, ventanas, armarios y elementos de madera.',
                'responsibilities' => 'Ajuste de bisagras, sustitución de cerraduras, reparación de marcos y montaje de mobiliario.',
                'base_price' => '32.00',
                'base_salary' => '1750.00',
            ],
            [
                'name' => 'Cerrajería',
                'description' => 'Apertura de puertas, cambio de cerraduras y refuerzo de sistemas de seguridad.',
                'responsibilities' => 'Apertura sin daños, cambio de bombines, instalación de cerrojos y reparación de persianas metálicas.',
                'base_price' => '50.00',
                'base_salary' => '1900.00',
            ],
            [
                'name' => 'Electrodomésticos',
                'description' => 'Diagnóstico y reparación de lavadoras, lavavajillas, frigoríficos, hornos y vitrocerámicas.',
                'responsibilities' => 'Diagnóstico de averías, sustitución de motores y resistencias, cambio de juntas y revisión de placas electrónicas.',
                'base_price' => '42.00',
                'base_salary' => '2000.00',
            ],
            [
                'name' => 'Pintura',
                'description' => 'Pintado de interiores y exteriores, reparación de desconchados y tratamiento de humedades.',
                'responsibilities' => 'Preparación de superficies, aplicación de masilla, pintado de paredes y techos y tratamiento antimoho.',
                'base_price' => '28.00',
                'base_salary' => '1650.00',
            ],
            [
                'name' => 'Albañilería',
                'description' => 'Pequeñas reformas y reparaciones de obra en paredes, suelos y alicatados.',
                'responsibilities' => 'Reparación de grietas, sustitución de azulejos, colocación de baldosas y levantamiento de tabiques.',
                'base_price' => '38.00',
                'base_salary' => '1850.00',
            ],
            [
                'name' => 'Gas',
                'description' => 'Revisión, mantenimiento y reparación de calderas, calentadores e instalaciones de gas.',
                'responsibilities' => 'Revisión de calderas, detección de fugas de gas, sustitución de termopares y certificación de instalaciones.',
                'base_price' => '48.00',
                'base_salary' => '2150.00',
            ],
            [
                'name' => 'Cristalería',
                'description' => 'Sustitución y reparación de cristales, espejos y mamparas.',
                'responsibilities' => 'Medición y corte de vidrio, sustitución de cristales rotos, instalación de mamparas y sellado de ventanas.',
                'base_price' => '36.00',
                'base_salary' => '1780.00',
            ],
        ];

        foreach($specialities as $data) {
            Speciality::updateOrCreate(['name' => $data['name']], $data);
        }
    }
}
