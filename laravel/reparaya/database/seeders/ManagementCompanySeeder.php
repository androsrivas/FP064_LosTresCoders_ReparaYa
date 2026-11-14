<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Community;
use App\Models\ManagementCompany;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class ManagementCompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            [
                'email' => 'fincas.mediterraneo@reparaya.test',
                'name' => 'Fincas Mediterráneo SL',
                'tax_id' => 'B12345678',
                'contact_phone' => '931112233',
                'commission_rate' => '10.00',
                'communities' => [
                    ['Edificio Sol', 'Carrer del Sol 14', 'Centro'],
                    ['Residencial Las Palmeras', 'Avinguda Nord 220', 'Norte'],
                ],
            ],
            [
                'email' => 'admin.rambla@reparaya.test',
                'name' => 'Administraciones Rambla SL',
                'tax_id' => 'B87654321',
                'contact_phone' => '934445566',
                'commission_rate' => '12.50',
                'communities' => [
                    ['Comunidad Rambla 21', 'Rambla 21', 'Centro'],
                    ['Torre Mirador', 'Passeig del Mar 8', 'Sur'],
                    ['Edificio Levante', 'Carrer Llevant 3', 'Este'],
                ],
            ],
            [
                'email' => 'gestion.diagonal@reparaya.test',
                'name' => 'Gestión Integral Diagonal SL',
                'tax_id' => 'B23456789',
                'contact_phone' => '932223344',
                'commission_rate' => '11.00',
                'communities' => [
                    ['Residencial Diagonal Park', 'Avinguda Diagonal 455', 'Oeste'],
                    ['Edificio Montseny', 'Carrer Montseny 27', 'Norte'],
                ],
            ],
            [
                'email' => 'costa.daurada@reparaya.test',
                'name' => 'Administración de Fincas Costa Daurada SA',
                'tax_id' => 'A34567890',
                'contact_phone' => '977556677',
                'commission_rate' => '9.50',
                'communities' => [
                    ['Urbanización Mar Blau', 'Passeig Marítim 102', 'Sur'],
                    ['Comunidad Els Pins', 'Carrer dels Pins 9', 'Oeste'],
                    ['Edificio Miramar', 'Carrer Miramar 45', 'Sur'],
                ],
            ],
            [
                'email' => 'fincas.eixample@reparaya.test',
                'name' => 'Fincas Eixample SL',
                'tax_id' => 'B45678901',
                'contact_phone' => '936778899',
                'commission_rate' => '13.00',
                'communities' => [
                    ['Comunidad Consell de Cent 310', 'Carrer del Consell de Cent 310', 'Centro'],
                    ['Edificio Aragó', 'Carrer d\'Aragó 188', 'Este'],
                ],
            ],
        ];

        foreach ($companies as $data) {
            $user = DemoUserSeeder::user($data['email'], $data['name'], UserRole::PropertyManager);

            $company = ManagementCompany::updateOrCreate(
                ['tax_id' => $data['tax_id']],
                [
                    'user_id' => $user->id,
                    'name' => $data['name'],
                    'contact_email' => $data['email'],
                    'contact_phone' => $data['contact_phone'],
                    'commission_rate' => $data['commission_rate'],
                ]
            );

            foreach ($data['communities'] as [$name, $address, $zone]) {
                Community::updateOrCreate(
                    ['management_company_id' => $company->id, 'name' => $name],
                    ['address' => $address, 'zone_id' => Zone::where('name', $zone)->value('id')],
                );
            }
        }
    }
}
