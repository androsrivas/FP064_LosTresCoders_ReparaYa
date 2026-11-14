<?php
declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Speciality;
use App\Models\Technician;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    /** Constraseña SOLO para datos de demostración */
    public const PASSWORD = 'password123';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        self::user('admin@reparaya.test', 'Admin ReparaYa', UserRole::Admin);
        self::user('soporte@reparaya.test', 'Soporte ReparaYa', UserRole::Admin);

        self::user('laura.garcia@reparaya.test', 'Laura García', UserRole::Customer, '600111222');
        self::user('carlos.lopez@reparaya.test', 'Carlos López', UserRole::Customer, '600333444');
        self::user('ana.martinez@reparaya.test', 'Ana Martínez', UserRole::Customer, '600555666');
        self::user('david.sanchez@reparaya.test', 'David Sánchez', UserRole::Customer, '600777888');
        self::user('lucia.fernandez@reparaya.test', 'Lucía Fernández', UserRole::Customer, '611222333');
        self::user('miguel.romero@reparaya.test', 'Miguel Romero', UserRole::Customer, '622444555');
        self::user('elena.torres@reparaya.test', 'Elena Torres', UserRole::Customer, '633666777');
        self::user('sergio.navarro@reparaya.test', 'Sergio Navarro', UserRole::Customer, '644888999');
        self::user('paula.ruiz@reparaya.test', 'Paula Ruiz', UserRole::Customer, '655111000');
        self::user('jorge.molina@reparaya.test', 'Jorge Molina', UserRole::Customer, '666222111');

        $technicians = [
            ['pedro.fontanero@reparaya.test', 'Pedro Fontanero', 'Fontanería'],
            ['raul.tuberias@reparaya.test', 'Raúl Tuberías', 'Fontanería'],
            ['marta.electricista@reparaya.test', 'Marta Electricista', 'Electricidad'],
            ['ivan.voltios@reparaya.test', 'Iván Voltios', 'Electricidad'],
            ['javier.clima@reparaya.test', 'Javier Clima', 'Climatización'],
            ['nuria.frio@reparaya.test', 'Nuria Frío', 'Climatización'],
            ['antonio.carpintero@reparaya.test', 'Antonio Carpintero', 'Carpintería'],
            ['sofia.cerrajera@reparaya.test', 'Sofía Cerrajera', 'Cerrajería'],
            ['luis.electro@reparaya.test', 'Luis Electrodomésticos', 'Electrodomésticos'],
            ['carmen.pintora@reparaya.test', 'Carmen Pintora', 'Pintura'],
            ['manuel.albanil@reparaya.test', 'Manuel Albañil', 'Albañilería'],
            ['rosa.gas@reparaya.test', 'Rosa Gas', 'Gas'],
            ['alberto.cristalero@reparaya.test', 'Alberto Cristalero', 'Cristalería'],
        ];

        foreach($technicians as [$email, $name, $speciality]) {
            $user = self::user($email, $name, UserRole::Technician);

            Technician::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'full_name' => $name,
                    'speciality_id' => Speciality::where('name', $speciality)->value('id'),
                    'is_available' => true,
                ],
            );
        }
    }

    /** Crea o actualiza un usuario. 'role' no es fillable: por eso forceFill(). */
    public static function user(string $email, string $name, UserRole $role, ?string $phone = null): User
    {
        $user = User::firstOrNew(['email' => $email]);

        $user->forceFill([
            'name' => $name,
            'password' => self::PASSWORD,
            'role' => $role,
            'phone' => $phone,
        ])->save();

        return $user;
    }
}
