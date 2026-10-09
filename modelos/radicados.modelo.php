<?php

require_once __DIR__ . "/conexion.php";

/**
 * Modelo de Persistencia para Radicados y Documentos — Proyecto ODIN
 * 
 * Gestiona la persistencia atómica mediante PDO con sentencias preparadas,
 * generación concurrente de consecutivos por vigencia anual y registro
 * de metadatos documentales y de trazabilidad.
 */
class ModeloRadicados
{
    /**
     * Genera de forma atómica y concurrente el siguiente número de radicado
     * respetando la vigencia anual y el prefijo según el tipo de radicación:
     * - ENTRADA -> E-AAAA-000001
     * - SALIDA  -> S-AAAA-000001
     * - INTERNO -> INT-AAAA-000001
     */
    public static function mdlGenerarNumeroRadicado(string $tipoRadicado, ?PDO $pdo = null): string
    {
        $db = $pdo ?? Conexion::conectar();
        $anio = (int)date("Y");

        $tipoNormalizado = strtoupper(trim($tipoRadicado));
        switch ($tipoNormalizado) {
            case "SALIDA":
                $prefijo = "S";
                break;
            case "INTERNO":
            case "INTERNA":
                $prefijo = "INT";
                break;
            case "ENTRADA":
            default:
                $prefijo = "E";
                break;
        }

        // Bloqueo y actualización atómica en radicado_consecutivos
        $stmtUpdate = $db->prepare(
            "INSERT INTO radicado_consecutivos (anio, prefijo, consecutivo)
             VALUES (:anio, :prefijo, 1)
             ON DUPLICATE KEY UPDATE consecutivo = consecutivo + 1"
        );
        $stmtUpdate->bindParam(":anio", $anio, PDO::PARAM_INT);
        $stmtUpdate->bindParam(":prefijo", $prefijo, PDO::PARAM_STR);
        $stmtUpdate->execute();

        $stmtSelect = $db->prepare(
            "SELECT consecutivo FROM radicado_consecutivos
             WHERE anio = :anio AND prefijo = :prefijo"
        );
        $stmtSelect->bindParam(":anio", $anio, PDO::PARAM_INT);
        $stmtSelect->bindParam(":prefijo", $prefijo, PDO::PARAM_STR);
        $stmtSelect->execute();
        $fila = $stmtSelect->fetch(PDO::FETCH_ASSOC);

        $consecutivo = $fila ? (int)$fila["consecutivo"] : 1;
        $consecutivoPad = str_pad((string)$consecutivo, 6, "0", STR_PAD_LEFT);

        return "{$prefijo}-{$anio}-{$consecutivoPad}";
    }

    /**
     * Persiste de forma atómica un radicado, sus datos de remitente (si aplica),
     * sus documentos adjuntos procesados y el registro de bitácora/historial.
     */
    public static function mdlRadicarDocumento(
        array $datosRadicado,
        array $documentosMetadatos,
        ?array $datosRemitente = null
    ): array {
        $db = Conexion::conectar();
        $db->beginTransaction();

        try {
            // 1. Manejo del Remitente Externo (si no es anónimo)
            $idRemitente = null;
            $esAnonimo = !empty($datosRadicado["es_anonimo"]);
            $remitenteTexto = $esAnonimo ? "ANÓNIMO (Ley de Transparencia)" : trim($datosRadicado["remitente"] ?? "Anónimo");

            if (!$esAnonimo && $datosRemitente && !empty($datosRemitente["num_identificacion"])) {
                $tipoDoc = strtoupper(trim($datosRemitente["tipo_identificacion"] ?? "CC"));
                $numDoc  = trim($datosRemitente["num_identificacion"]);

                $stmtBuscaRem = $db->prepare(
                    "SELECT id_remitente FROM remitentes_externos
                     WHERE tipo_identificacion = :tipo AND num_identificacion = :num"
                );
                $stmtBuscaRem->bindParam(":tipo", $tipoDoc, PDO::PARAM_STR);
                $stmtBuscaRem->bindParam(":num",  $numDoc,  PDO::PARAM_STR);
                $stmtBuscaRem->execute();
                $remExistente = $stmtBuscaRem->fetch(PDO::FETCH_ASSOC);

                if ($remExistente) {
                    $idRemitente = (int)$remExistente["id_remitente"];
                } else {
                    $stmtInsRem = $db->prepare(
                        "INSERT INTO remitentes_externos
                         (tipo_identificacion, num_identificacion, nombre, apellido, correo, telefono, direccion)
                         VALUES (:tipo, :num, :nom, :ape, :correo, :tel, :dir)"
                    );
                    $nombreRem = trim($datosRemitente["nombre"] ?? "");
                    $apellidoRem = trim($datosRemitente["apellido"] ?? "");
                    $correoRem = trim($datosRemitente["correo"] ?? "");
                    $telRem = trim($datosRemitente["telefono"] ?? "");
                    $dirRem = trim($datosRemitente["direccion"] ?? "");

                    $stmtInsRem->bindParam(":tipo",   $tipoDoc,     PDO::PARAM_STR);
                    $stmtInsRem->bindParam(":num",    $numDoc,      PDO::PARAM_STR);
                    $stmtInsRem->bindParam(":nom",    $nombreRem,   PDO::PARAM_STR);
                    $stmtInsRem->bindParam(":ape",    $apellidoRem, PDO::PARAM_STR);
                    $stmtInsRem->bindParam(":correo", $correoRem,   PDO::PARAM_STR);
                    $stmtInsRem->bindParam(":tel",    $telRem,      PDO::PARAM_STR);
                    $stmtInsRem->bindParam(":dir",    $dirRem,      PDO::PARAM_STR);
                    $stmtInsRem->execute();
                    $idRemitente = (int)$db->lastInsertId();
                }

                if (!empty($datosRemitente["nombre_completo"])) {
                    $remitenteTexto = trim($datosRemitente["nombre_completo"]);
                }
            }

            // 2. Generación del número de radicado con consecutivo anual
            $tipoRadicado = strtoupper(trim($datosRadicado["tipo_radicado"] ?? "ENTRADA"));
            if (!in_array($tipoRadicado, ["ENTRADA", "SALIDA", "INTERNO"], true)) {
                $tipoRadicado = "ENTRADA";
            }

            $numeroRadicado = self::mdlGenerarNumeroRadicado($tipoRadicado, $db);

            // 3. Normalizar parámetros para las restricciones CHECK de BD
            $prioridad = strtoupper(trim($datosRadicado["prioridad"] ?? "MEDIA"));
            if (!in_array($prioridad, ["BAJA", "MEDIA", "ALTA", "URGENTE"], true)) {
                $prioridad = "MEDIA";
            }

            $confidencialidad = strtoupper(trim($datosRadicado["confidencialidad"] ?? "PUBLICO"));
            if (!in_array($confidencialidad, ["PUBLICO", "RESERVADO", "CONFIDENCIAL"], true)) {
                $confidencialidad = "PUBLICO";
            }

            $medioRecepcion = trim($datosRadicado["medio_recepcion"] ?? "Ventanilla");
            $asunto = trim($datosRadicado["asunto"] ?? "");
            $fechaRadicado = date("Y-m-d H:i:s");
            $fechaLimite = !empty($datosRadicado["fecha_limite"]) ? trim($datosRadicado["fecha_limite"]) : null;
            $fechaVencimiento = !empty($datosRadicado["fecha_vencimiento"]) ? trim($datosRadicado["fecha_vencimiento"]) : $fechaLimite;

            $idTramite     = !empty($datosRadicado["id_tramite"]) ? (int)$datosRadicado["id_tramite"] : null;
            $idEstado      = !empty($datosRadicado["id_estado"]) ? (int)$datosRadicado["id_estado"] : 1; // 1 = Pendiente
            $idDependencia = !empty($datosRadicado["id_dependencia"]) ? (int)$datosRadicado["id_dependencia"] : null;
            $idUsuario     = !empty($datosRadicado["id_usuario"]) ? (int)$datosRadicado["id_usuario"] : 1;

            // 4. Inserción en tabla radicados
            $sqlRadicado = "INSERT INTO radicados (
                numero_radicado, id_tramite, id_estado, id_dependencia, id_usuario,
                id_remitente, remitente, asunto, fecha_radicado, tipo_radicado,
                medio_recepcion, prioridad, fecha_vencimiento, confidencialidad, fecha_limite
            ) VALUES (
                :num_rad, :tramite, :estado, :dep, :usuario,
                :rem_id, :rem_txt, :asunto, :f_rad, :tipo,
                :medio, :prio, :f_venc, :conf, :f_lim
            )";

            $stmtRad = $db->prepare($sqlRadicado);
            $stmtRad->bindParam(":num_rad", $numeroRadicado,  PDO::PARAM_STR);
            $stmtRad->bindValue(":tramite", $idTramite,        $idTramite ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmtRad->bindParam(":estado",  $idEstado,        PDO::PARAM_INT);
            $stmtRad->bindValue(":dep",     $idDependencia,    $idDependencia ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmtRad->bindParam(":usuario", $idUsuario,        PDO::PARAM_INT);
            $stmtRad->bindValue(":rem_id",  $idRemitente,      $idRemitente ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmtRad->bindParam(":rem_txt", $remitenteTexto,  PDO::PARAM_STR);
            $stmtRad->bindParam(":asunto",  $asunto,          PDO::PARAM_STR);
            $stmtRad->bindParam(":f_rad",   $fechaRadicado,   PDO::PARAM_STR);
            $stmtRad->bindParam(":tipo",    $tipoRadicado,    PDO::PARAM_STR);
            $stmtRad->bindParam(":medio",   $medioRecepcion,  PDO::PARAM_STR);
            $stmtRad->bindParam(":prio",    $prioridad,       PDO::PARAM_STR);
            $stmtRad->bindValue(":f_venc",  $fechaVencimiento,$fechaVencimiento ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmtRad->bindParam(":conf",    $confidencialidad,PDO::PARAM_STR);
            $stmtRad->bindValue(":f_lim",   $fechaLimite,     $fechaLimite ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmtRad->execute();

            $idRadicado = (int)$db->lastInsertId();

            // 5. Inserción de documentos asociados
            $docsInsertados = [];
            $sqlDoc = "INSERT INTO documentos (
                id_radicado, tamano, nombre_archivo, ruta_archivo, tipo,
                fecha_subida, nombre, mime_type, checksum, version_actual
            ) VALUES (
                :rad_id, :tamano, :nom_arch, :ruta, :tipo,
                :f_sub, :nom_orig, :mime, :hash, 1
            )";
            $stmtDoc = $db->prepare($sqlDoc);

            foreach ($documentosMetadatos as $docMeta) {
                $fechaSubida = date("Y-m-d H:i:s");
                $stmtDoc->bindParam(":rad_id",   $idRadicado,                PDO::PARAM_INT);
                $stmtDoc->bindParam(":tamano",   $docMeta["tamano"],         PDO::PARAM_INT);
                $stmtDoc->bindParam(":nom_arch", $docMeta["nombre_archivo"],  PDO::PARAM_STR);
                $stmtDoc->bindParam(":ruta",     $docMeta["ruta_archivo"],    PDO::PARAM_STR);
                $stmtDoc->bindParam(":tipo",     $docMeta["tipo"],            PDO::PARAM_STR);
                $stmtDoc->bindParam(":f_sub",    $fechaSubida,               PDO::PARAM_STR);
                $stmtDoc->bindParam(":nom_orig", $docMeta["nombre_original"], PDO::PARAM_STR);
                $stmtDoc->bindParam(":mime",     $docMeta["mime_type"],      PDO::PARAM_STR);
                $stmtDoc->bindParam(":hash",     $docMeta["checksum"],       PDO::PARAM_STR);
                $stmtDoc->execute();

                $docMeta["id_documento"] = (int)$db->lastInsertId();
                $docsInsertados[] = $docMeta;
            }

            // 6. Registro de Trazabilidad e Historial inicial
            $sqlHistorial = "INSERT INTO historial_radicado (
                id_radicado, id_usuario, accion, descripcion, fecha
            ) VALUES (
                :rad_id, :usuario, 'radicacion', :desc, :fecha
            )";
            $descHistorial = "Radicación inicial generada exitosamente (" . count($docsInsertados) . " documento(s) adjunto(s)).";
            $stmtHist = $db->prepare($sqlHistorial);
            $stmtHist->bindParam(":rad_id",  $idRadicado,     PDO::PARAM_INT);
            $stmtHist->bindParam(":usuario", $idUsuario,       PDO::PARAM_INT);
            $stmtHist->bindParam(":desc",    $descHistorial,   PDO::PARAM_STR);
            $stmtHist->bindParam(":fecha",   $fechaRadicado,   PDO::PARAM_STR);
            $stmtHist->execute();

            $db->commit();

            return [
                "id_radicado"     => $idRadicado,
                "numero_radicado" => $numeroRadicado,
                "tipo_radicado"   => $tipoRadicado,
                "remitente"       => $remitenteTexto,
                "asunto"          => $asunto,
                "fecha_radicado"  => $fechaRadicado,
                "documentos"      => $docsInsertados
            ];
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Error en ModeloRadicados::mdlRadicarDocumento: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Consulta el listado de radicados con detalles relacionados para DataTables.
     */
    public static function mdlListarRadicados(): array
    {
        $stmt = Conexion::conectar()->prepare(
            "SELECT r.id_radicado, r.numero_radicado, r.remitente, r.asunto,
                    r.tipo_radicado, r.prioridad, r.fecha_radicado,
                    e.nombre AS estado, e.color AS estado_color,
                    d.nombre AS dependencia,
                    t.nombre AS tramite,
                    (SELECT COUNT(*) FROM documentos d2 WHERE d2.id_radicado = r.id_radicado) AS total_documentos
             FROM radicados r
             LEFT JOIN estados e ON e.id_estado = r.id_estado
             LEFT JOIN dependencias d ON d.id_dependencia = r.id_dependencia
             LEFT JOIN tramites t ON t.id_tramite = r.id_tramite
             ORDER BY r.id_radicado DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Consulta el detalle de un radicado específico por ID con sus documentos.
     */
    public static function mdlObtenerRadicadoPorId(int $idRadicado): ?array
    {
        $stmt = Conexion::conectar()->prepare(
            "SELECT r.*, e.nombre AS estado_nombre, e.color AS estado_color,
                    d.nombre AS dependencia_nombre, t.nombre AS tramite_nombre
             FROM radicados r
             LEFT JOIN estados e ON e.id_estado = r.id_estado
             LEFT JOIN dependencias d ON d.id_dependencia = r.id_dependencia
             LEFT JOIN tramites t ON t.id_tramite = r.id_tramite
             WHERE r.id_radicado = :id"
        );
        $stmt->bindParam(":id", $idRadicado, PDO::PARAM_INT);
        $stmt->execute();
        $radicado = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$radicado) {
            return null;
        }

        $stmtDocs = Conexion::conectar()->prepare(
            "SELECT id_documento, nombre, nombre_archivo, ruta_archivo, tipo,
                    tamano, mime_type, checksum, fecha_subida
             FROM documentos
             WHERE id_radicado = :id ORDER BY id_documento ASC"
        );
        $stmtDocs->bindParam(":id", $idRadicado, PDO::PARAM_INT);
        $stmtDocs->execute();
        $radicado["documentos"] = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);

        return $radicado;
    }

    /**
     * Obtiene los catálogos necesarios para alimentar el formulario de radicación.
     */
    public static function mdlObtenerCatalogos(): array
    {
        $db = Conexion::conectar();

        $stmtTramites = $db->prepare(
            "SELECT id_tramite, nombre, descripcion, dias_respuesta, prioridad_default
             FROM tramites WHERE activo = 1 ORDER BY nombre ASC"
        );
        $stmtTramites->execute();
        $tramites = $stmtTramites->fetchAll(PDO::FETCH_ASSOC);

        $stmtDeps = $db->prepare(
            "SELECT id_dependencia, nombre, codigo, tipo
             FROM dependencias WHERE estado = 'Activo' ORDER BY nombre ASC"
        );
        $stmtDeps->execute();
        $dependencias = $stmtDeps->fetchAll(PDO::FETCH_ASSOC);

        $stmtEstados = $db->prepare(
            "SELECT id_estado, nombre, color FROM estados ORDER BY id_estado ASC"
        );
        $stmtEstados->execute();
        $estados = $stmtEstados->fetchAll(PDO::FETCH_ASSOC);

        return [
            "tramites"     => $tramites,
            "dependencias" => $dependencias,
            "estados"      => $estados
        ];
    }
}
