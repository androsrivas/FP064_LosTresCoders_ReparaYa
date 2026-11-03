-- Ampliaciones bbddReparaYa.sql.
-- Se ejecuta después del script base. Sirve para una instalación nueva y para una base
-- de datos con una versión anterior de este archivo, y se puede ejecutar más de una vez.
-- Haz una copia de seguridad si la base de datos ya tiene datos: se elimina detalles_especialidades.

-- Estado "En curso" y localizador más largo: cambios compatibles con los datos existentes.
-- MODIFY sustituye la definición completa, por eso se repite el DEFAULT.
ALTER TABLE incidencias
MODIFY COLUMN estado ENUM('Pendiente', 'Asignada', 'En curso', 'Finalizada', 'Cancelada') DEFAULT 'Pendiente',
MODIFY COLUMN localizador VARCHAR(20) NOT NULL;

-- Tabla para registrar cada cambio de estado o modificación en las incidencias
CREATE TABLE IF NOT EXISTS historial_incidencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    incidencia_id INT NOT NULL,
    usuario_id INT DEFAULT NULL, -- Usuario que realizó el cambio
    estado_anterior ENUM('Pendiente', 'Asignada', 'En curso', 'Finalizada', 'Cancelada') DEFAULT 'Pendiente',
    estado_nuevo ENUM('Pendiente', 'Asignada', 'En curso', 'Finalizada', 'Cancelada') NOT NULL,
    comentario TEXT,             -- Motivo del cambio o nota técnica
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (incidencia_id) REFERENCES incidencias(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Si la tabla ya existía, CREATE no la cambia: se actualizan aquí sus estados.
ALTER TABLE historial_incidencias
MODIFY COLUMN estado_anterior ENUM('Pendiente', 'Asignada', 'En curso', 'Finalizada', 'Cancelada') DEFAULT 'Pendiente',
MODIFY COLUMN estado_nuevo ENUM('Pendiente', 'Asignada', 'En curso', 'Finalizada', 'Cancelada') NOT NULL;

-- Información adicional de cada especialidad (tipo de servicio).
-- MySQL no admite ADD COLUMN IF NOT EXISTS: las columnas solo se añaden si aún no existen.
SET @sql_especialidades = IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME = 'especialidades'
       AND COLUMN_NAME = 'precio_base') = 0,
    'ALTER TABLE especialidades
     ADD COLUMN descripcion TEXT,
     ADD COLUMN responsabilidades TEXT,
     ADD COLUMN salario_base DECIMAL(10,2) DEFAULT 0.00,
     ADD COLUMN precio_base DECIMAL(10,2) DEFAULT 0.00',
    'DO 0'
);
PREPARE stmt_especialidades FROM @sql_especialidades;
EXECUTE stmt_especialidades;
DEALLOCATE PREPARE stmt_especialidades;

-- Sustituida por las columnas anteriores. Solo existe en bases de datos con una versión anterior de este archivo.
DROP TABLE IF EXISTS detalles_especialidades;

-- Tabla para la mensajeria entre cliente y tecnico sobre una incidencia
CREATE TABLE IF NOT EXISTS comentarios_incidencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    incidencia_id INT NOT NULL, -- Incidencia relacionada
    usuario_id INT NOT NULL, -- Usuario que comenta
    comentario TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (incidencia_id) REFERENCES incidencias(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);
