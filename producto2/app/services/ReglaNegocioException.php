<?php

declare(strict_types=1);

namespace App\Services;

use DomainException;

/**
 * Una regla de negocio impide la operación. Su mensaje se puede mostrar al usuario.
 */
final class ReglaNegocioException extends DomainException
{
}
