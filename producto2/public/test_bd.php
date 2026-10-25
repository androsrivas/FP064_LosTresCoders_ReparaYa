<?php
require_once __DIR__ . '/../config/config.php';

try {
    global $pdo;

    // Consulta con PDO para obtener todos los usuarios
    $stmt = $pdo->query("SELECT id, nombre, email, rol, telefono, created_at FROM usuarios ORDER BY id ASC");
    $usuarios = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test BD - Listado de Usuarios</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background-color: #f4f6f9; }
        h2 { color: #333; }
        table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #007bff; color: white; }
        tr:hover { background-color: #f1f1f1; }
        .badge { padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 0.85em; }
        .badge-Cliente { background-color: #e3f2fd; color: #0d47a1; }
        .badge-Técnico { background-color: #e8f5e9; color: #1b5e20; }
        .badge-Administrador { background-color: #fbe9e7; color: #b71c1c; }
    </style>
</head>
<body>

    <h2>Prueba de Conexión y Listado de Usuarios (`usuarios`)</h2>

    <?php if (!empty($usuarios)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Rol</th>
                    <th>Fecha Alta</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['id']) ?></td>
                        <td><?= htmlspecialchars($u['nombre']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['telefono'] ?? 'N/A') ?></td>
                        <td>
                            <span class="badge badge-<?= htmlspecialchars($u['rol']) ?>">
                                <?= htmlspecialchars($u['rol']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($u['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No se encontraron registros en la tabla <code>usuarios</code>.</p>
    <?php endif; ?>

</body>
</html>