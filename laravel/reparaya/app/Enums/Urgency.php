<?php
declare(strict_types=1);

namespace App\Enums;

enum Urgency: string
{
    case Standard = 'Estándar';
    case Urgent = 'Urgente';
}
