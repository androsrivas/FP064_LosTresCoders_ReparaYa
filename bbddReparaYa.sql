-- 1. TABLA DE USUARIOS (Para el Alumno de Gestión de Usuarios)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Se usará password_hash() de PHP
    rol ENUM('admin', 'tecnico', 'particular') NOT NULL DEFAULT 'particular',
    telefono VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. TABLA DE CATEGORÍAS/ESPECIALIDADES (Para el Alumno de Maestros)
CREATE TABLE especialidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_especialidad VARCHAR(50) NOT NULL -- Fontanería, Electricidad, etc.
);

-- 3. TABLA DE TÉCNICOS (Para el Alumno de Gestión de Técnicos)
CREATE TABLE tecnicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNIQUE, -- Relación 1:1 con la tabla usuarios (si el técnico tiene login)
    nombre_completo VARCHAR(100) NOT NULL,
    especialidad_id INT,
    disponible BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (especialidad_id) REFERENCES especialidades(id)
);

-- 4. TABLA DE INCIDENCIAS/RESERVAS (Para el Alumno de Gestión de Incidencias)
CREATE TABLE incidencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    localizador VARCHAR(12) NOT NULL UNIQUE, -- Código tipo REP-2026-XXXX
    cliente_id INT NOT NULL,
    tecnico_id INT DEFAULT NULL, -- El admin lo asignará después
    especialidad_id INT NOT NULL, -- Tipo de servicio solicitado
    descripcion TEXT NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    fecha_servicio DATETIME NOT NULL, -- Campo clave para la regla de las 48h
    tipo_urgencia ENUM('Estándar', 'Urgente') DEFAULT 'Estándar',
    estado ENUM('Pendiente', 'Asignada', 'Finalizada', 'Cancelada') DEFAULT 'Pendiente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES usuarios(id),
    FOREIGN KEY (tecnico_id) REFERENCES tecnicos(id),
    FOREIGN KEY (especialidad_id) REFERENCES especialidades(id)
);


