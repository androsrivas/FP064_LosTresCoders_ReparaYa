<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Tipo de servicio = fila de la tabla `especialidades`.
 * Los importes (DECIMAL) se manejan como string para no perder precisión con float.
 */
class TipoServicio extends Model
{
    private const SELECT = 'SELECT id, nombre_especialidad, descripcion, responsabilidades, salario_base, precio_base
        FROM especialidades';

    public static function all(): array
    {
        return self::fetchAll(self::SELECT . ' ORDER BY nombre_especialidad');
    }

    public static function findById(int $id): ?array
    {
        return self::fetchOne(self::SELECT . ' WHERE id = :id', ['id' => $id]);
    }

    public static function findByName(string $nombreEspecialidad): ?array
    {
        return self::fetchOne(
            self::SELECT . ' WHERE nombre_especialidad = :nombre_especialidad',
            ['nombre_especialidad' => trim($nombreEspecialidad)]
        );
    }

    public static function create(
        string $nombreEspecialidad,
        ?string $descripcion,
        ?string $responsabilidades,
        ?string $salarioBase,
        string $precioBase
    ): int {
        return self::insert(
            'INSERT INTO especialidades (nombre_especialidad, descripcion, responsabilidades, salario_base, precio_base)
            VALUES (:nombre_especialidad, :descripcion, :responsabilidades, :salario_base, :precio_base)',
            [
                'nombre_especialidad' => trim($nombreEspecialidad),
                'descripcion' => $descripcion,
                'responsabilidades' => $responsabilidades,
                'salario_base' => $salarioBase ?? '0.00',
                'precio_base' => $precioBase,
            ]
        );
    }

    public static function update(
        int $id,
        string $nombreEspecialidad,
        ?string $descripcion,
        ?string $responsabilidades,
        ?string $salarioBase,
        string $precioBase
    ): void {
        self::execute(
            'UPDATE especialidades
            SET nombre_especialidad = :nombre_especialidad,
                descripcion = :descripcion,
                responsabilidades = :responsabilidades,
                salario_base = :salario_base,
                precio_base = :precio_base
            WHERE id = :id',
            [
                'nombre_especialidad' => trim($nombreEspecialidad),
                'descripcion' => $descripcion,
                'responsabilidades' => $responsabilidades,
                'salario_base' => $salarioBase ?? '0.00',
                'precio_base' => $precioBase,
                'id' => $id,
            ]
        );
    }

    public static function delete(int $id): bool
    {
       return self::execute('DELETE FROM especialidades WHERE id = :id', ['id' => $id]) > 0;

    }
}
