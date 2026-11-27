<?php
declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Technician = 'tecnico';
    case Customer = 'particular';
    case PropertyManager = 'gestora';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Technician => 'Técnico',
            self::Customer => 'Particular',
            self::PropertyManager => 'Gestora',
        };
    }

    /** Nombre de la ruta donde va el usuario después de iniciar sesión */
    public function homeRoute(): string
    {
        return match ($this) {
            self::Admin => 'admin.home',
            self::Technician => 'technician.home',
            self::Customer => 'home',
            self::PropertyManager => 'profile.show'
        };
    }

    /**
     * Menú por rol: [etiqueta, nombre de la ruta]
     * Las rutas que aún no existen no se muestran (Route::has en el layout), así solo se deberán crear con estos nombres.
     * 
     * @return list<array{0: string, 1: string}>
     */
    public function menu(): array
    {
        return match ($this) {
            self::Admin => [
                ['Panel', 'admin.home'],
                ['Incidencias', 'admin.incidents.index'],
                ['Calendario', 'admin.calendar'],
                ['Usuarios', 'admin.users.index'],
                ['Técnicos', 'admin.technicians.index'],
                ['Servicios', 'admin.specialities.index'],
                ['Gestoras', 'admin.management-companies.index'],
                ['Zonas', 'admin.zones.index'],
                ['Liquidaciones', 'admin.settlements.index'],
            ],
            self::Technician => [
                ['Mi agenda', 'technician.home'],
            ],
            self::Customer => [
                ['Mis Gestiones', 'profile.show'],
            ],
            self::PropertyManager => [
                ['Mis Gestiones', 'profile.show'],
            ],
        };
    }
}
