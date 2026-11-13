<?php
declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Technician = 'tecnico';
    case Customer = 'particular';
    case PropertyManager = 'gestora';
}
