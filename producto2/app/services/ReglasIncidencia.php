<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;
use Exception;

/**
 * Reglas de negocio sobre los datos de una incidencia: fechas, urgencia y técnico.
 * No accede a la base de datos: recibe los valores y lanza una excepción si no son válidos.
 */
final class ReglasIncidencia
{
    public const URGENCIA_ESTANDAR = 'Estándar';
    public const URGENCIA_URGENTE  = 'Urgente';

    private const HORAS_ANTELACION = 48;
    private const CAMPOS_ALTA = ['cliente_id', 'especialidad_id', 'descripcion', 'direccion'];

    public static function exigirDatosDeAlta(array $datos): void
    {
        foreach (self::CAMPOS_ALTA as $campo) {
            if (empty($datos[$campo])) {
                throw new ReglaNegocioException("El campo {$campo} es obligatorio.");
            }
        }
    }

    public static function exigirUrgenciaValida(string $tipoUrgencia): void
    {
        if (!in_array($tipoUrgencia, [self::URGENCIA_ESTANDAR, self::URGENCIA_URGENTE], true)) {
            throw new ReglaNegocioException('El tipo de urgencia no es válido.');
        }
    }

    public static function parsearFecha(string $fecha): DateTimeImmutable
    {
        if (trim($fecha) === '') {
            throw new ReglaNegocioException('La fecha de servicio es obligatoria.');
        }

        try {
            return new DateTimeImmutable($fecha);
        } catch (Exception) {
            throw new ReglaNegocioException('El formato de la fecha de servicio no es válido.');
        }
    }

    public static function exigirFechaFutura(DateTimeImmutable $fecha, ?DateTimeImmutable $ahora = null): void
    {
        if ($fecha < ($ahora ?? new DateTimeImmutable())) {
            throw new ReglaNegocioException('La fecha de servicio no puede ser en el pasado.');
        }
    }

    /** Una solicitud estándar admite 48 horas exactas; una urgente, cualquier fecha futura. */
    public static function exigirAntelacionDeAlta(
        string $tipoUrgencia,
        DateTimeImmutable $fecha,
        ?DateTimeImmutable $ahora = null
    ): void {
        if ($tipoUrgencia !== self::URGENCIA_URGENTE && self::horasHasta($fecha, $ahora) < self::HORAS_ANTELACION) {
            throw new ReglaNegocioException('Las solicitudes estándar requieren un mínimo de 48 horas de antelación.');
        }
    }

    /** El cliente solo puede cancelar si faltan más de 48 horas: con 48 exactas ya no. */
    public static function exigirAntelacionDeCancelacion(DateTimeImmutable $fecha, ?DateTimeImmutable $ahora = null): void
    {
        if (self::horasHasta($fecha, $ahora) <= self::HORAS_ANTELACION) {
            throw new ReglaNegocioException('No es posible cancelar con menos de 48 horas de antelación.');
        }
    }

    public static function exigirTecnicoCompatible(?array $tecnico, int $especialidadId): void
    {
        if ($tecnico === null) {
            throw new ReglaNegocioException('El técnico seleccionado no existe.');
        }

        if ((int) $tecnico['especialidad_id'] !== $especialidadId) {
            throw new ReglaNegocioException('La especialidad del técnico no coincide con la de la incidencia.');
        }
    }

    private static function horasHasta(DateTimeImmutable $fecha, ?DateTimeImmutable $ahora): float
    {
        return ($fecha->getTimestamp() - ($ahora ?? new DateTimeImmutable())->getTimestamp()) / 3600;
    }
}
