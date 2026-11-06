<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Tecnico;
use App\Models\TipoServicio;
use App\Models\Usuario;
use PDOException;
use Throwable;

/**
 * Casos de uso de los técnicos (tablas usuarios + tecnicos, 1:1 por usuario_id).
 *
 * Recibe datos ya validados en formato por el controlador (Validator) y aplica
 * solo las reglas de negocio que dependen de la base de datos.
 * Respuesta de crear/modificar/eliminar: ['exito' => bool, 'mensaje' => string].
 */
final class TecnicoService
{
    private const MYSQL_DUPLICADO = 1062;
    private const MYSQL_FK_EN_USO = 1451;

    public function listar(): array
    {
        return Tecnico::all();
    }

    public function obtener(int $id): ?array
    {
        return Tecnico::findById($id);
    }

    /** Opciones para el desplegable de especialidad del formulario. */
    public function especialidades(): array
    {
        return TipoServicio::all();
    }

    public function crear(
        string $nombre,
        string $email,
        string $password,
        ?int $especialidadId,
        ?string $telefono,
        bool $disponible
    ): array {
        return $this->ejecutar('Error al crear el técnico.', function () use (
            $nombre, $email, $password, $especialidadId, $telefono, $disponible
        ): array {
            $this->exigirEspecialidad($especialidadId);
            $this->exigirEmailLibre($email, null);

            $id = $this->guardar(fn(): int => Tecnico::create(
                $nombre, $email, $password, $especialidadId, $telefono, $disponible
            ));

            return ['exito' => true, 'mensaje' => 'Técnico creado.', 'id' => $id];
        });
    }

    public function modificar(
        int $id,
        string $nombre,
        string $email,
        ?int $especialidadId,
        ?string $telefono,
        bool $disponible
    ): array {
        return $this->ejecutar('Error al modificar el técnico.', function () use (
            $id, $nombre, $email, $especialidadId, $telefono, $disponible
        ): array {
            $tecnico = $this->buscar($id);
            $this->exigirEspecialidad($especialidadId);
            // El email vive en `usuarios`: se excluye la cuenta del propio técnico
            $this->exigirEmailLibre($email, $tecnico['usuario_id'] !== null ? (int) $tecnico['usuario_id'] : null);

            $this->guardar(fn() => Tecnico::update($id, $nombre, $email, $especialidadId, $telefono, $disponible));

            return ['exito' => true, 'mensaje' => 'Técnico actualizado.'];
        });
    }

    public function eliminar(int $id): array
    {
        return $this->ejecutar('Error al eliminar el técnico.', function () use ($id): array {
            try {
                if (!Tecnico::delete($id)) {
                    throw new ReglaNegocioException('Técnico no encontrado.');
                }
            } catch (PDOException $e) {
                // La FK incidencias.tecnico_id no tiene ON DELETE: borrar perdería el historial
                if (($e->errorInfo[1] ?? null) === self::MYSQL_FK_EN_USO) {
                    throw new ReglaNegocioException(
                        'No se puede eliminar: el técnico tiene incidencias asignadas. '
                        . 'Si ya no trabaja con nosotros, edítalo y desmarca "Disponible".'
                    );
                }
                throw $e;
            }

            return ['exito' => true, 'mensaje' => 'Técnico eliminado.'];
        });
    }

    private function exigirEspecialidad(?int $especialidadId): void
    {
        if ($especialidadId !== null && TipoServicio::findById($especialidadId) === null) {
            throw new ReglaNegocioException('La especialidad seleccionada no existe.');
        }
    }

    /** @param int|null $usuarioIdActual null al crear; el usuario del técnico al modificar. */
    private function exigirEmailLibre(string $email, ?int $usuarioIdActual): void
    {
        if (Usuario::emailExists($email, $usuarioIdActual)) {
            throw new ReglaNegocioException('Ya existe una cuenta con ese correo.');
        }
    }

    /**
     * Ejecuta la escritura traduciendo el UNIQUE de email: si otro usuario lo cogió
     * entre la comprobación y el INSERT/UPDATE, la BD es quien lo frena.
     */
    private function guardar(callable $escritura): mixed
    {
        try {
            return $escritura();
        } catch (PDOException $e) {
            if (($e->errorInfo[1] ?? null) === self::MYSQL_DUPLICADO) {
                throw new ReglaNegocioException('Ya existe una cuenta con ese correo.');
            }
            throw $e;
        }
    }

    private function buscar(int $id): array
    {
        return Tecnico::findById($id)
            ?? throw new ReglaNegocioException('Técnico no encontrado.');
    }

    private function ejecutar(string $mensajeError, callable $casoDeUso): array
    {
        try {
            return $casoDeUso();
        } catch (ReglaNegocioException $e) {
            return ['exito' => false, 'mensaje' => $e->getMessage()];
        } catch (Throwable $e) {
            error_log('TecnicoService: ' . $e->getMessage());
            return ['exito' => false, 'mensaje' => $mensajeError];
        }
    }
}
