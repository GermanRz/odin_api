<?php
require_once __DIR__ . "/../modelos/conexion.php";

try {
    $db = Conexion::conectar();
    echo "Conexión a base de datos exitosa.\n";

    // 1. Tabla de consecutivos anuales
    $sqlConsecutivos = "CREATE TABLE IF NOT EXISTS radicado_consecutivos (
        anio INT NOT NULL,
        prefijo VARCHAR(5) NOT NULL,
        consecutivo INT NOT NULL DEFAULT 0,
        PRIMARY KEY (anio, prefijo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    $db->exec($sqlConsecutivos);
    echo "Tabla radicado_consecutivos verificada.\n";

    // 2. Sembrar estados
    $sqlEstados = "INSERT INTO estados (id_estado, nombre, color, flujo_pqrsf) VALUES
        (1, 'Pendiente', '#ffc107', 1),
        (2, 'En proceso', '#17a2b8', 1),
        (3, 'En revisión', '#6f42c1', 1),
        (4, 'Respondido', '#007bff', 1),
        (5, 'Rechazado', '#dc3545', 1),
        (6, 'Finalizado', '#28a745', 1),
        (7, 'Archivado', '#6c757d', 0)
    ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), color = VALUES(color);";
    $db->exec($sqlEstados);
    echo "Estados base sembrados.\n";

    // 3. Sembrar dependencias
    $sqlDependencias = "INSERT INTO dependencias (id_dependencia, nombre, descripcion, estado, dependencia, codigo, tipo) VALUES
        (1, 'Dirección General', 'Despacho y Administración General', 'Activo', 'Dirección General', 'DIR-01', 'directiva'),
        (2, 'Gestión Documental', 'Ventanilla Única y Correspondencia', 'Activo', 'Gestión Documental', 'GD-02', 'operativa'),
        (3, 'Secretaría General', 'Secretaría y Despacho', 'Activo', 'Secretaría General', 'SG-03', 'administrativa'),
        (4, 'Área Jurídica', 'Asuntos Legales y Tutelas', 'Activo', 'Área Jurídica', 'JUR-04', 'operativa')
    ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), estado = VALUES(estado);";
    $db->exec($sqlDependencias);
    echo "Dependencias base sembradas.\n";

    // 4. Sembrar tramites
    $sqlTramites = "INSERT INTO tramites (id_tramite, nombre, descripcion, id_dependencia_responsable, id_estado_inicial, dias_respuesta, prioridad_default, activo, requiere_respuesta) VALUES
        (1, 'Derecho de Petición', 'Solicitud formal en ejercicio del derecho de petición', 2, 1, 15, 'MEDIA', 1, 1),
        (2, 'Queja / Reclamo (PQRSF)', 'Manifestación de inconformidad o queja ciudadana', 2, 1, 15, 'ALTA', 1, 1),
        (3, 'Solicitud de Información', 'Petición de copias o consulta documental', 1, 1, 10, 'MEDIA', 1, 1),
        (4, 'Comunicación Oficial Interna', 'Memorandos y traslados internos interdependencias', 1, 1, 5, 'MEDIA', 1, 0),
        (6, 'Certificación Documental', 'Expedición de certificados o constancias', 2, 1, 10, 'BAJA', 1, 1)
    ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);";
    $db->exec($sqlTramites);
    echo "Trámites base sembrados.\n";

    // 5. Inicializar consecutivo actual de radicación si hay registros existentes
    $anioActual = (int)date("Y");
    $stmtMax = $db->query("SELECT numero_radicado FROM radicados WHERE numero_radicado LIKE 'E-$anioActual-%' ORDER BY id_radicado DESC LIMIT 1");
    $ultimo = $stmtMax->fetch(PDO::FETCH_ASSOC);
    $consecutivoInicial = 0;
    if ($ultimo && preg_match("/^E-$anioActual-(\d+)$/", $ultimo["numero_radicado"], $m)) {
        $consecutivoInicial = (int)$m[1];
    }
    $stmtInit = $db->prepare("INSERT INTO radicado_consecutivos (anio, prefijo, consecutivo)
        VALUES (:anio, 'E', :consecutivo)
        ON DUPLICATE KEY UPDATE consecutivo = GREATEST(consecutivo, VALUES(consecutivo))");
    $stmtInit->execute([':anio' => $anioActual, ':consecutivo' => $consecutivoInicial]);

    echo "Migración y sembrado completados con éxito.\n";
} catch (Exception $e) {
    echo "Error en migración: " . $e->getMessage() . "\n";
    exit(1);
}
