<?php
declare(strict_types=1);

namespace App\Exceptions\Incident;

use DomainException;

/**
 * Base de las excepciones de reglas de negocio de incidencias.
 * Representa errores esperables que se muestran al usuario, no fallos del sistema.
 */
abstract class IncidentException extends DomainException
{
}
