<?php

require_once __DIR__ . "/../modelos/conexion.php";

try {
    $db = Conexion::conectar();
    echo "Conexión a BD exitosa para sembrado de CCD (Series y Subseries).\n";

    // 1. Sembrar Unidades Administrativas (ccd_unidades)
    $sqlUnidades = "INSERT INTO ccd_unidades (id_unidad, codigo_unidad, nombre_unidad, nivel, descripcion) VALUES
        (1, '1.10', 'Dirección General', 1, 'Despacho principal y alta dirección'),
        (2, '1.20', 'Secretaría General y Gestión Documental', 1, 'Administración de correspondencia y archivo'),
        (3, '1.30', 'Área Jurídica', 1, 'Asesoría jurídica y representación judicial')
    ON DUPLICATE KEY UPDATE nombre_unidad = VALUES(nombre_unidad), nivel = VALUES(nivel);";
    $db->exec($sqlUnidades);
    echo "1. Unidades administrativas (ccd_unidades) sembradas correctamente.\n";

    // 2. Sembrar Series Documentales (ccd_series)
    $sqlSeries = "INSERT INTO ccd_series (id_serie, codigo_serie, nombre_serie, codigo_unidad, descripcion, informacion_publica) VALUES
        (1, '1.1010-42', 'ACTAS', '1.10', 'Actas de comités, consejos y reuniones institucionales', 'PUBLICO'),
        (2, '1.1012-27', 'HISTORIAS LABORALES', '1.10', 'Expedientes de hojas de vida y trayectoria del personal', 'RESERVADO'),
        (3, '1.1013-27', 'INFORMES DE GESTIÓN', '1.10', 'Informes semestrales, anuales y a entes de control', 'PUBLICO'),
        (4, '1.1015-27', 'RESOLUCIONES ADMINISTRATIVAS', '1.10', 'Actos administrativos expedidos por la dirección', 'PUBLICO'),
        (5, '1.2020-27', 'PETICIONES, QUEJAS Y RECLAMOS (PQRSF)', '1.20', 'Correspondencia de solicitudes ciudadanas y externas', 'PUBLICO')
    ON DUPLICATE KEY UPDATE nombre_serie = VALUES(nombre_serie), codigo_unidad = VALUES(codigo_unidad);";
    $db->exec($sqlSeries);
    echo "2. Series documentales (ccd_series) sembradas correctamente.\n";

    // 3. Sembrar Subseries Documentales (ccd_subseries)
    $sqlSubseries = "INSERT INTO ccd_subseries (id_subserie, codigo_subserie, nombre_subserie, id_serie, retencion_anios, disposicion_final, nivel_acceso, valor_documental) VALUES
        (1, '1.1010-42-01', 'Actas de Consejo Directivo', 1, 10, 'CONSERVACION', 'PUBLICO', 'ADMINISTRATIVO'),
        (2, '1.1010-42-02', 'Actas de Comité Técnico y de Gerencia', 1, 5, 'CONSERVACION', 'PUBLICO', 'ADMINISTRATIVO'),
        (3, '1.1012-27-01', 'Historias Laborales de Funcionarios de Planta', 2, 80, 'CONSERVACION', 'RESERVADO', 'LEGAL'),
        (4, '1.1012-27-02', 'Historias Laborales de Contratistas de Prestación de Servicios', 2, 20, 'CONSERVACION', 'RESERVADO', 'LEGAL'),
        (5, '1.1012-27-03', 'Hojas de Vida y Documentos de Selección de Personal', 2, 5, 'SELECCION', 'RESERVADO', 'ADMINISTRATIVO'),
        (6, '1.1013-27-01', 'Informes a Entes de Control (Contraloría / Procuraduría)', 3, 10, 'CONSERVACION', 'PUBLICO', 'TESTIMONIAL'),
        (7, '1.1013-27-02', 'Informes de Gestión y Rendición de Cuentas Interna', 3, 5, 'CONSERVACION', 'PUBLICO', 'ADMINISTRATIVO'),
        (8, '1.1015-27-01', 'Resoluciones de Nombramiento, Traslado y Retiro', 4, 80, 'CONSERVACION', 'PUBLICO', 'LEGAL'),
        (9, '1.1015-27-02', 'Resoluciones Administrativas y Reglamentarias', 4, 20, 'CONSERVACION', 'PUBLICO', 'ADMINISTRATIVO'),
        (10, '1.2020-27-01', 'Derechos de Petición de Interés General y Particular', 5, 5, 'CONSERVACION', 'PUBLICO', 'LEGAL'),
        (11, '1.2020-27-02', 'Quejas, Reclamos y Sugerencias Ciudadanas', 5, 5, 'ELIMINACION', 'PUBLICO', 'ADMINISTRATIVO')
    ON DUPLICATE KEY UPDATE nombre_subserie = VALUES(nombre_subserie), id_serie = VALUES(id_serie);";
    $db->exec($sqlSubseries);
    echo "3. Subseries documentales (ccd_subseries) sembradas correctamente.\n";

    echo "=== Migración de CCD finalizada con éxito. ===\n";
} catch (Exception $e) {
    echo "Error en migración CCD: " . $e->getMessage() . "\n";
    exit(1);
}
