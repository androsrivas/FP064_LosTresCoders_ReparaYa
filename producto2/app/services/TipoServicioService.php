<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\TipoServicio;
use PDOException;
use Throwable;

/**
 * Casos de uso de los tipos de servicio (tabla especialidades).
 *
 * Recibe datos ya validados en formato por el controlador (Validator) y aplica
 * solo las reglas de negocio que dependen de la base de datos.
 * Respuesta de crear/modificar/eliminar: ['exito' => bool, 'mensaje' => string].
 */
final class TipoServicioService
{
    private const MYSQL_FK_EN_USO = 1451;

    public function listar(): array
    {
        return TipoServicio::all();
    }

    public function obtener(int $id): ?array
    {
        return TipoServicio::findById($id);
    }

    public function crear(
        string $nombre,
        ?string $descripcion,
        ?string $responsabilidades,
        ?string $salarioBase,
        string $precioBase
    ): array {
        return $this->ejecutar('Error al crear el tipo de servicio.', function () use (
            $nombre, $descripcion, $responsabilidades, $salarioBase, $precioBase
        ): array {
            $this->exigirNombreLibre($nombre, null);

            $id = TipoServicio::create($nombre, $descripcion, $responsabilidades, $salarioBase, $precioBase);

            return ['exito' => true, 'mensaje' => 'Tipo de servicio creado.', 'id' => $id];
        });
    }

    public function modificar(
        int $id,
        string $nombre,
        ?string $descripcion,
        ?string $responsabilidades,
        ?string $salarioBase,
        string $precioBase
    ): array {
        return $this->ejecutar('Error al modificar el tipo de servicio.', function () use (
            $id, $nombre, $descripcion, $responsabilidades, $salarioBase, $precioBase
        ): array {
            $this->buscar($id);
            $this->exigirNombreLibre($nombre, $id);

            TipoServicio::update($id, $nombre, $descripcion, $responsabilidades, $salarioBase, $precioBase);

            return ['exito' => true, 'mensaje' => 'Tipo de servicio actualizado.'];
        });
    }

    public function eliminar(int $id): array
    {
        return $this->ejecutar('Error al eliminar el tipo de servicio.', function () use ($id): array {
            try {
                if (!TipoServicio::delete($id)) {
                    throw new ReglaNegocioException('Tipo de servicio no encontrado.');
                }
            } catch (PDOException $e) {
                // SQLSTATE 23000 agrupa todos los errores de integridad: solo se traduce la FK en uso
                if (($e->errorInfo[1] ?? null) === self::MYSQL_FK_EN_USO) {
                    throw new ReglaNegocioException(
                        'No se puede eliminar: hay técnicos o incidencias con este tipo de servicio.'
                    );
                }
                throw $e;
            }

            return ['exito' => true, 'mensaje' => 'Tipo de servicio eliminado.'];
        });
    }

    /** @param int|null $idActual null al crear; el propio id al modificar. */
    private function exigirNombreLibre(string $nombre, ?int $idActual): void
    {
        $existente = TipoServicio::findByName($nombre);

        if ($existente !== null && (int) $existente['id'] !== $idActual) {
            throw new ReglaNegocioException('Ya existe un tipo de servicio con ese nombre.');
        }
    }

    private function buscar(int $id): array
    {
        return TipoServicio::findById($id)
            ?? throw new ReglaNegocioException('Tipo de servicio no encontrado.');
    }

    private function ejecutar(string $mensajeError, callable $casoDeUso): array
    {
        try {
            return $casoDeUso();
        } catch (ReglaNegocioException $e) {
            return ['exito' => false, 'mensaje' => $e->getMessage()];
        } catch (Throwable $e) {
            error_log('TipoServicioService: ' . $e->getMessage());
            return ['exito' => false, 'mensaje' => $mensajeError];
        }
    }
}
