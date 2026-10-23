-- Tabla para registrar cada cambio de estado o modificación en las incidencias
CREATE TABLE historial_incidencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    incidencia_id INT NOT NULL,
    usuario_id INT DEFAULT NULL, -- Usuario que realizó el cambio
    estado_anterior ENUM('Pendiente', 'Asignada', 'Finalizada', 'Cancelada') DEFAULT 'Pendiente',
    estado_nuevo ENUM('Pendiente', 'Asignada', 'Finalizada', 'Cancelada') NOT NULL,
    comentario TEXT,             -- Motivo del cambio o nota técnica
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (incidencia_id) REFERENCES incidencias(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Tabla complementaria para extender la información de especialidades
CREATE TABLE detalles_especialidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    especialidad_id INT NOT NULL UNIQUE, -- Relación 1:1 con especialidades
    descripcion TEXT, -- Descripcion general
    responsabilidades TEXT, -- Responsabilidades asignadas
    salario_base DECIMAL(10,2) DEFAULT 0.00,
    precio_hora_orientativo DECIMAL(10,2) DEFAULT 0.00, -- Para el calculo de presupuestos
    FOREIGN KEY (especialidad_id) REFERENCES especialidades(id) ON DELETE CASCADE
);

-- Tabla para la mensajeria entre cliente y tecnico sobre una incidencia
CREATE TABLE comentarios_incidencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    incidencia_id INT NOT NULL, -- Incidencia relacionada
    usuario_id INT NOT NULL, -- Usuario que comenta
    comentario TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (incidencia_id) REFERENCES incidencias(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);