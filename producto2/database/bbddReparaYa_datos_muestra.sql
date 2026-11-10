-- 0. Crear los usuarios a mano uno por uno a través del formulario de /home (Si meto el hash a mano en la bd, falla)

--    /  Nombre /         / email /      / telefono /   / contraseña /

-- Carlos Cliente / cliente@reparaya.com / 600010203 / 12345678a
-- Laura Cliente / laura@reparaya.com / 600020304 / 12345678a
-- Pedro Fontanero / fontanero@reparaya.com / 601030405 / 12345678a
-- Ana Admin / admin@reparaya.com / 601040506 / 12345678a
-- Marta Electricista / electro@reparaya.com / 601050607 / 12345678a
-- Javier Clima / clima@reparaya.com / 601060708 / 12345678a


-- 1. Actualizar roles de los usuarios según sus IDs y correos
UPDATE `usuarios` SET `rol` = 'tecnico' WHERE `id` = 3; -- Pedro Fontanero
UPDATE `usuarios` SET `rol` = 'admin'   WHERE `id` = 4; -- Ana Admin
UPDATE `usuarios` SET `rol` = 'tecnico' WHERE `id` = 5; -- Marta Electricista
UPDATE `usuarios` SET `rol` = 'tecnico' WHERE `id` = 6; -- Javier Clima

-- 2. Insertar especialidades con la nueva estructura
INSERT INTO `especialidades` (`id`, `nombre_especialidad`, `descripcion`, `responsabilidades`, `precio_base`, `salario_base`) VALUES
(1, 'Fontanería', 'Servicio especializado en reparación de tuberías, grifería, fugas de agua y desatascos.', 'Inspección de tuberías, reparación de fugas, instalación de sanitarios y resolución de atascos.', 35.00, 1800.00),
(2, 'Electricidad', 'Diagnóstico y reparación de averías eléctricas, cuadros principales e instalaciones.', 'Revisión de cableado, reparación de cortocircuitos, sustitución de diferenciales y montaje de mecanismos.', 40.00, 1950.00),
(3, 'Climatización', 'Mantenimiento, carga de gas y reparación de sistemas de aire acondicionado y calefacción.', 'Limpieza de filtros, recarga de refrigerante, diagnóstico de compresores y calibración de termostatos.', 45.00, 2100.00);

-- 3. Vincular los técnicos creados con sus respectivas especialidades
INSERT INTO `tecnicos` (`id`, `usuario_id`, `nombre_completo`, `especialidad_id`, `disponible`) VALUES
(1, 3, 'Pedro Fontanero', 1, 1),
(2, 5, 'Marta Electricista', 2, 1),
(3, 6, 'Javier Clima', 3, 1);

-- 4. Insertar incidencias de prueba asignadas a los nuevos clientes y técnicos
INSERT INTO `incidencias` (`localizador`, `cliente_id`, `tecnico_id`, `especialidad_id`, `descripcion`, `direccion`, `fecha_servicio`, `tipo_urgencia`, `estado`) VALUES
('REP-20261101-A1B2C3', 1, 1, 1, 'Fuga de agua persistente en el lavabo del baño principal.', 'Calle Mayor 12, 2ºA', '2026-11-10 10:00:00', 'Estándar', 'Asignada'),
('REP-20261102-D4E5F6', 1, 2, 2, 'El diferencial salta al encender el horno eléctrico.', 'Calle Mayor 12, 2ºA', '2026-11-08 16:30:00', 'Urgente', 'En curso'),
('REP-20261103-G7H8I9', 1, NULL, 3, 'Revisión anual del sistema de calefacción central.', 'Calle Mayor 12, 2ºA', '2026-11-15 11:00:00', 'Estándar', 'Pendiente'),
('REP-20261025-J0K1L2', 2, 3, 3, 'Limpieza de filtros y recarga de gas en split de salón.', 'Avenida Brasil 45, 1ºC', '2026-10-28 09:00:00', 'Estándar', 'Finalizada');

-- 5. Insertar trazabilidad inicial en historial_incidencias
INSERT INTO `historial_incidencias` (`incidencia_id`, `usuario_id`, `estado_anterior`, `estado_nuevo`, `comentario`) VALUES
(1, 1, 'Pendiente', 'Pendiente', 'Incidencia registrada por el cliente.'),
(1, 4, 'Pendiente', 'Asignada', 'Técnico Pedro asignado por el Administrador.'),
(2, 1, 'Pendiente', 'Pendiente', 'Incidencia registrada por el cliente.'),
(2, 5, 'Pendiente', 'Asignada', 'Técnico asignado.'),
(2, 3, 'Asignada', 'En curso', 'El técnico ha iniciado la reparación en el domicilio.');