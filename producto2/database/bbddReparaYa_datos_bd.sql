-- 1. Insertar especialidades de prueba
INSERT INTO especialidades (nombre_especialidad) VALUES
('Fontanería'),
('Electricidad'),
('Climatización');

-- 2. Insertar usuarios de prueba (Password para todos: 123456)
INSERT INTO usuarios (nombre, email, password, rol, telefono) VALUES
('Carlos Cliente', 'cliente@reparaya.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1n52E.r8gV0g.O64zY7M8K4WjY0d6lS', 'particular', '600111222'),
('Laura Cliente', 'laura@reparaya.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1n52E.r8gV0g.O64zY7M8K4WjY0d6lS', 'particular', '600333444'),
('Pedro Técnico', 'tecnico@reparaya.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1n52E.r8gV0g.O64zY7M8K4WjY0d6lS', 'tecnico', '611222333'),
('Ana Admin', 'admin@reparaya.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1n52E.r8gV0g.O64zY7M8K4WjY0d6lS', 'admin', '622333444'),
('Marta Electricista', 'marta.tecnico@reparaya.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1n52E.r8gV0g.O64zY7M8K4WjY0d6lS', 'tecnico', '611333444'),
('Javier Clima', 'javier.tecnico@reparaya.com', '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe1n52E.r8gV0g.O64zY7M8K4WjY0d6lS', 'tecnico', '611444555');

-- 3. Vincular los perfiles de usuarios con la tabla tecnicos
INSERT INTO tecnicos (usuario_id, nombre_completo, especialidad_id, disponible) VALUES
(3, 'Pedro Técnico', 1, 1),
(5, 'Marta Electricista', 2, 1),
(6, 'Javier Clima', 3, 1);

-- 4. Poblar la tabla 'detalles_especialidades' para las 3 especialidades existentes
INSERT INTO detalles_especialidades (especialidad_id, descripcion, responsabilidades, precio_hora_orientativo, salario_base) VALUES
(1, 'Servicio especializado en reparación de tuberías, grifería, fugas de agua y desatascos.', 'Inspección de tuberías, reparación de fugas, instalación de sanitarios y resolución de atascos.', 35.00, 1800.00),
(2, 'Diagnóstico y reparación de averías eléctricas, cuadros principales e instalaciones.', 'Revisión de cableado, reparación de cortocircuitos, sustitución de diferenciales y montaje de mecanismos.', 40.00, 1950.00),
(3, 'Mantenimiento, carga de gas y reparación de sistemas de aire acondicionado y calefacción.', 'Limpieza de filtros, recarga de refrigerante, diagnóstico de compresores y calibración de termostatos.', 45.00, 2100.00);
