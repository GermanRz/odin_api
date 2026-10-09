-- ==============================================================================
-- ODIN - GESTOR DOCUMENTAL
-- Script DDL de Estructura: Tabla de Consecutivos Anuales de Radicación
-- Motor: MySQL 8.0+ / MariaDB 10.4+ (Local XAMPP o Servidor Remoto MySQL)
-- ==============================================================================

USE odin_api_db;

-- 1. Crear tabla de control concurrente de consecutivos por vigencia y tipo
CREATE TABLE IF NOT EXISTS radicado_consecutivos (
    anio INT NOT NULL COMMENT 'Vigencia fiscal anual (ej. 2026)',
    prefijo VARCHAR(5) NOT NULL COMMENT 'Prefijo de radicación: E (Entrada), S (Salida), INT (Interno)',
    consecutivo INT NOT NULL DEFAULT 0 COMMENT 'Último consecutivo generado para la vigencia y prefijo',
    PRIMARY KEY (anio, prefijo),
    CONSTRAINT chk_consecutivos_positivos CHECK (consecutivo >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Control atómico de consecutivos de radicación por vigencia anual (RF-05, RF-06)';

-- 2. Inicializar consecutivo con el máximo existente en radicados históricos (si aplica)
INSERT INTO radicado_consecutivos (anio, prefijo, consecutivo)
SELECT 
    YEAR(NOW()) AS anio,
    'E' AS prefijo,
    COALESCE(MAX(CAST(SUBSTRING_INDEX(numero_radicado, '-', -1) AS UNSIGNED)), 0) AS consecutivo
FROM radicados
WHERE numero_radicado LIKE CONCAT('E-', YEAR(NOW()), '-%')
ON DUPLICATE KEY UPDATE consecutivo = GREATEST(consecutivo, VALUES(consecutivo));

INSERT INTO radicado_consecutivos (anio, prefijo, consecutivo)
SELECT 
    YEAR(NOW()) AS anio,
    'S' AS prefijo,
    COALESCE(MAX(CAST(SUBSTRING_INDEX(numero_radicado, '-', -1) AS UNSIGNED)), 0) AS consecutivo
FROM radicados
WHERE numero_radicado LIKE CONCAT('S-', YEAR(NOW()), '-%')
ON DUPLICATE KEY UPDATE consecutivo = GREATEST(consecutivo, VALUES(consecutivo));

INSERT INTO radicado_consecutivos (anio, prefijo, consecutivo)
SELECT 
    YEAR(NOW()) AS anio,
    'INT' AS prefijo,
    COALESCE(MAX(CAST(SUBSTRING_INDEX(numero_radicado, '-', -1) AS UNSIGNED)), 0) AS consecutivo
FROM radicados
WHERE numero_radicado LIKE CONCAT('INT-', YEAR(NOW()), '-%')
ON DUPLICATE KEY UPDATE consecutivo = GREATEST(consecutivo, VALUES(consecutivo));
