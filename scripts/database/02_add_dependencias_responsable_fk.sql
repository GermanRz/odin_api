-- =========================================================================
-- MIGRACIÓN 02: Llave foránea de usuario responsable en dependencias y trámites
-- Sistema de Seguimiento y Gestión Documental ODIN
-- =========================================================================

-- 1. Agregar columna id_usuario_responsable a la tabla dependencias
ALTER TABLE `dependencias`
ADD COLUMN `id_usuario_responsable` BIGINT NULL AFTER `estado`;

-- 2. Agregar columna id_usuario_responsable a la tabla tramites
ALTER TABLE `tramites`
ADD COLUMN `id_usuario_responsable` BIGINT NULL AFTER `id_dependencia_responsable`;

-- 3. Crear usuario encargado para el Área Jurídica (Dra. Marcela Rincón)
INSERT INTO `usuarios` (
  `id_rol`, `nombre`, `password`, `estado`, `correo`, 
  `id_dependencia`, `direccion`, `num_identificacion`, `telefono`, `tipo_identificacion`
) VALUES (
  3, 'Dra. Marcela Rincón', '$2y$10$HCbg9/jC5tcQJ1u1Al0Qiefs7NT8nDbxfaGYk2fCgKAKIX.hxxypS', 
  'Activo', 'marcela.rincon@odin.gov.co', 4, 'Oficina Jurídica Piso 3', '52890123', '3109876543', 'CC'
);

-- 4. Asociar usuarios activos con sus dependencias reales
UPDATE `usuarios` SET `id_dependencia` = 1 WHERE `id_usuario` = 5; -- Patricia Silva -> Dirección General
UPDATE `usuarios` SET `id_dependencia` = 2 WHERE `id_usuario` = 2; -- Carlos Mendoza -> Gestión Documental
UPDATE `usuarios` SET `id_dependencia` = 3 WHERE `id_usuario` = 3; -- Laura Gómez -> Secretaría General
UPDATE `usuarios` SET `id_dependencia` = 4 WHERE `correo` = 'marcela.rincon@odin.gov.co'; -- Marcela Rincón -> Jurídica

-- 5. Asignar responsables por dependencia (ninguno es el Administrador ID 1)
UPDATE `dependencias` SET `id_usuario_responsable` = 5 WHERE `id_dependencia` = 1; -- Patricia Silva
UPDATE `dependencias` SET `id_usuario_responsable` = 2 WHERE `id_dependencia` = 2; -- Carlos Mendoza
UPDATE `dependencias` SET `id_usuario_responsable` = 3 WHERE `id_dependencia` = 3; -- Laura Gómez
UPDATE `dependencias` SET `id_usuario_responsable` = (SELECT `id_usuario` FROM `usuarios` WHERE `correo` = 'marcela.rincon@odin.gov.co' LIMIT 1) WHERE `id_dependencia` = 4;

-- 6. Asignar responsable por defecto en cada trámite según su dependencia
UPDATE `tramites` t
JOIN `dependencias` d ON d.`id_dependencia` = t.`id_dependencia_responsable`
SET t.`id_usuario_responsable` = d.`id_usuario_responsable`
WHERE d.`id_usuario_responsable` IS NOT NULL;

-- 7. Restricciones de integridad referencial (Foreign Keys)
ALTER TABLE `dependencias`
ADD CONSTRAINT `fk_dependencia_usuario_responsable`
FOREIGN KEY (`id_usuario_responsable`) REFERENCES `usuarios` (`id_usuario`)
ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `tramites`
ADD CONSTRAINT `fk_tramite_usuario_responsable`
FOREIGN KEY (`id_usuario_responsable`) REFERENCES `usuarios` (`id_usuario`)
ON DELETE SET NULL ON UPDATE CASCADE;
