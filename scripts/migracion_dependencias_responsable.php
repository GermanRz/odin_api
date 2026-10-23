<?php

/**
 * Migración: Agregar Llave Foránea de Usuario Responsable en Dependencias y Trámites
 * Proyecto ODIN
 */

require_once __DIR__ . "/../modelos/conexion.php";

echo "Iniciando migración de dependencias y trámites...\n";

try {
    $db = Conexion::conectar();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Agregar id_usuario_responsable a dependencias si no existe
    $colDepCheck = $db->query("SHOW COLUMNS FROM dependencias LIKE 'id_usuario_responsable'")->fetch();
    if (!$colDepCheck) {
        $db->exec("ALTER TABLE dependencias ADD COLUMN id_usuario_responsable BIGINT NULL AFTER estado");
        echo "  [OK] Columna 'id_usuario_responsable' agregada a tabla dependencias.\n";
    } else {
        echo "  [INFO] Columna 'id_usuario_responsable' ya existe en dependencias.\n";
    }

    // 2. Agregar id_usuario_responsable a tramites si no existe
    $colTramCheck = $db->query("SHOW COLUMNS FROM tramites LIKE 'id_usuario_responsable'")->fetch();
    if (!$colTramCheck) {
        $db->exec("ALTER TABLE tramites ADD COLUMN id_usuario_responsable BIGINT NULL AFTER id_dependencia_responsable");
        echo "  [OK] Columna 'id_usuario_responsable' agregada a tabla tramites.\n";
    } else {
        echo "  [INFO] Columna 'id_usuario_responsable' ya existe en tramites.\n";
    }

    // 3. Crear usuario para Área Jurídica si no existe (Dra. Marcela Rincón)
    $stmtUserCheck = $db->prepare("SELECT id_usuario FROM usuarios WHERE correo = 'marcela.rincon@odin.gov.co' LIMIT 1");
    $stmtUserCheck->execute();
    $usuarioJuridica = $stmtUserCheck->fetch(PDO::FETCH_ASSOC);

    $idUsuarioJuridica = null;
    if (!$usuarioJuridica) {
        $hashPassword = password_hash("12345678", PASSWORD_DEFAULT);
        $stmtInsUser = $db->prepare(
            "INSERT INTO usuarios (id_rol, nombre, password, estado, correo, id_dependencia, direccion, num_identificacion, telefono, tipo_identificacion)
             VALUES (3, 'Dra. Marcela Rincón', :pass, 'Activo', 'marcela.rincon@odin.gov.co', 4, 'Oficina Jurídica Piso 3', '52890123', '3109876543', 'CC')"
        );
        $stmtInsUser->bindParam(":pass", $hashPassword, PDO::PARAM_STR);
        $stmtInsUser->execute();
        $idUsuarioJuridica = (int)$db->lastInsertId();
        echo "  [OK] Creado nuevo usuario responsable: Dra. Marcela Rincón (ID: {$idUsuarioJuridica}) para Área Jurídica.\n";
    } else {
        $idUsuarioJuridica = (int)$usuarioJuridica["id_usuario"];
        echo "  [INFO] Usuario Dra. Marcela Rincón ya existe (ID: {$idUsuarioJuridica}).\n";
    }

    // 4. Actualizar id_dependencia de los usuarios existentes para que correspondan a sus dependencias
    // Carlos Mendoza (ID 2) -> Gestión Documental (Dep 2)
    $db->exec("UPDATE usuarios SET id_dependencia = 2 WHERE id_usuario = 2");
    // Laura Gómez (ID 3) -> Secretaría General (Dep 3)
    $db->exec("UPDATE usuarios SET id_dependencia = 3 WHERE id_usuario = 3");
    // Patricia Silva (ID 5) -> Dirección General (Dep 1)
    $db->exec("UPDATE usuarios SET id_dependencia = 1 WHERE id_usuario = 5");
    // Marcela Rincón -> Área Jurídica (Dep 4)
    if ($idUsuarioJuridica) {
        $db->exec("UPDATE usuarios SET id_dependencia = 4 WHERE id_usuario = {$idUsuarioJuridica}");
    }
    echo "  [OK] Dependencias asignadas correctamente a usuarios.\n";

    // 5. Asignar responsables en dependencias (Ninguno es el Administrador ID 1):
    // Dep 1 (Dirección General)   -> ID 5 (Patricia Silva)
    // Dep 2 (Gestión Documental)  -> ID 2 (Carlos Mendoza)
    // Dep 3 (Secretaría General)  -> ID 3 (Laura Gómez)
    // Dep 4 (Área Jurídica)       -> ID de Marcela Rincón (ej. 6)
    $db->exec("UPDATE dependencias SET id_usuario_responsable = 5 WHERE id_dependencia = 1");
    $db->exec("UPDATE dependencias SET id_usuario_responsable = 2 WHERE id_dependencia = 2");
    $db->exec("UPDATE dependencias SET id_usuario_responsable = 3 WHERE id_dependencia = 3");
    if ($idUsuarioJuridica) {
        $db->exec("UPDATE dependencias SET id_usuario_responsable = {$idUsuarioJuridica} WHERE id_dependencia = 4");
    }
    echo "  [OK] Responsables asignados a todas las dependencias (Patricia Silva, Carlos Mendoza, Laura Gómez, Marcela Rincón).\n";

    // 6. Asignar por defecto a los trámites el responsable de su dependencia
    $db->exec("
        UPDATE tramites t
        JOIN dependencias d ON d.id_dependencia = t.id_dependencia_responsable
        SET t.id_usuario_responsable = d.id_usuario_responsable
        WHERE d.id_usuario_responsable IS NOT NULL
    ");
    echo "  [OK] Trámites actualizados con el responsable por defecto de su dependencia.\n";

    // 7. Agregar claves foráneas si no existen
    try {
        $db->exec("ALTER TABLE dependencias ADD CONSTRAINT fk_dependencia_usuario_responsable FOREIGN KEY (id_usuario_responsable) REFERENCES usuarios(id_usuario) ON DELETE SET NULL ON UPDATE CASCADE");
        echo "  [OK] Clave foránea agregada a dependencias.\n";
    } catch (Exception $e) {
        echo "  [INFO] Clave foránea en dependencias ya configurada o no requiere cambio.\n";
    }

    try {
        $db->exec("ALTER TABLE tramites ADD CONSTRAINT fk_tramite_usuario_responsable FOREIGN KEY (id_usuario_responsable) REFERENCES usuarios(id_usuario) ON DELETE SET NULL ON UPDATE CASCADE");
        echo "  [OK] Clave foránea agregada a tramites.\n";
    } catch (Exception $e) {
        echo "  [INFO] Clave foránea en tramites ya configurada o no requiere cambio.\n";
    }

    echo "\n>>> MIGRACIÓN EJECUTADA EXITOSAMENTE <<<\n";

} catch (Exception $e) {
    echo "ERROR EN LA MIGRACIÓN: " . $e->getMessage() . "\n";
    exit(1);
}
