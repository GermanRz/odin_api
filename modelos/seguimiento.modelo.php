<?php

require_once __DIR__ . "/conexion.php";

/**
 * Modelo de Persistencia para Consulta, Seguimiento y Trazabilidad — Proyecto ODIN
 * 
 * Cumple con los lineamientos del Archivo General de la Nación (AGN - Colombia),
 * la Ley 594 de 2000 (Ley General de Archivos) y la Ley 1712 de 2014 (Transparencia).
 * 
 * Gestiona consultas avanzadas, cálculo del semáforo legal RF-30, historial
 * inmutable de auditoría y ventanilla de consulta ciudadana.
 */
class ModeloSeguimiento
{
    /**
     * Calcula de forma dinámica el semáforo de plazos legales (RF-30).
     * 
     * Reglas de semaforización:
     * - 🟢 Verde: Más de 3 días restantes (Plazo normal).
     * - 🟡 Amarillo: Entre 0 y 3 días restantes (Alerta preventiva / prioritaria).
     * - 🔴 Rojo: Menos de 0 días restantes (Trámite vencido / requiere escalamiento).
     * - ⚪ Finalizado/Cerrado: Trámites en estados terminales (Finalizado, Rechazado, Archivado).
     * 
     * @param string|null $fechaLimite Fecha límite legal del trámite.
     * @param string|null $fechaRadicado Fecha de registro del radicado.
     * @param int|null $diasTramite Días estipulados para el trámite (default 15).
     * @param int|null $idEstado Identificador del estado actual.
     * @return array Metadatos del semáforo (color, días, etiqueta, icono, clase CSS).
     */
    public static function calcularSemaforo(?string $fechaLimite, ?string $fechaRadicado, ?int $diasTramite, ?int $idEstado): array
    {
        // Estados terminales de cierre (5 = Rechazado, 6 = Finalizado, 7 = Archivado)
        if (in_array((int)$idEstado, [5, 6, 7], true)) {
            return [
                "dias_restantes"  => 0,
                "color"           => "finalizado",
                "semaforo"        => "finalizado",
                "badge_class"     => "badge-secondary",
                "icono"           => "fas fa-check-double",
                "texto"           => "Concluido / Cerrado",
                "descripcion"     => "Trámite finalizado o archivado"
            ];
        }

        $hoy = new DateTime();
        $hoy->setTime(0, 0, 0);

        $fechaFin = null;
        if (!empty($fechaLimite)) {
            try {
                $fechaFin = new DateTime($fechaLimite);
                $fechaFin->setTime(0, 0, 0);
            } catch (Exception $e) {
                $fechaFin = null;
            }
        }

        if (!$fechaFin && !empty($fechaRadicado)) {
            try {
                $dias = ($diasTramite && $diasTramite > 0) ? $diasTramite : 15;
                $fechaFin = new DateTime($fechaRadicado);
                $fechaFin->modify("+{$dias} days");
                $fechaFin->setTime(0, 0, 0);
            } catch (Exception $e) {
                $fechaFin = null;
            }
        }

        if (!$fechaFin) {
            return [
                "dias_restantes"  => null,
                "color"           => "sin_plazo",
                "semaforo"        => "sin_plazo",
                "badge_class"     => "badge-light text-dark border",
                "icono"           => "far fa-clock",
                "texto"           => "Sin plazo fijado",
                "descripcion"     => "Sin fecha límite asignada"
            ];
        }

        $diff = $hoy->diff($fechaFin);
        $diasRestantes = (int)$diff->format("%r%a");

        if ($diasRestantes > 3) {
            return [
                "dias_restantes"  => $diasRestantes,
                "color"           => "verde",
                "semaforo"        => "verde",
                "badge_class"     => "badge-success",
                "icono"           => "fas fa-circle text-white mr-1",
                "texto"           => "En tiempo ({$diasRestantes} d)",
                "descripcion"     => "Plazo legal normal (> 3 días restantes)"
            ];
        } elseif ($diasRestantes >= 0 && $diasRestantes <= 3) {
            $txt = ($diasRestantes === 0) ? "Vence hoy" : "Por vencer ({$diasRestantes} d)";
            return [
                "dias_restantes"  => $diasRestantes,
                "color"           => "amarillo",
                "semaforo"        => "amarillo",
                "badge_class"     => "badge-warning text-dark font-weight-bold",
                "icono"           => "fas fa-exclamation-triangle mr-1",
                "texto"           => $txt,
                "descripcion"     => "Alerta preventiva / prioritaria (0 a 3 días)"
            ];
        } else {
            $diasVencido = abs($diasRestantes);
            return [
                "dias_restantes"  => $diasRestantes,
                "color"           => "rojo",
                "semaforo"        => "rojo",
                "badge_class"     => "badge-danger",
                "icono"           => "fas fa-exclamation-circle text-white mr-1",
                "texto"           => "Vencido ({$diasVencido} d)",
                "descripcion"     => "Trámite legalmente vencido / requiere escalamiento (< 0 días)"
            ];
        }
    }

    /**
     * Consulta avanzada de radicados con filtros institucionales AGN y control de acceso.
     * 
     * @param array $filtros Filtros de búsqueda (numero_radicado, tipo_radicado, id_estado, id_dependencia, fecha_desde, fecha_hasta, semaforo, busqueda).
     * @param int|null $idUsuario Identificador del usuario autenticado.
     * @param bool $esAdmin Bandera de rol administrador (true = acceso total).
     * @return array Listado de radicados con metadatos y cálculo del semáforo.
     */
    public static function mdlConsultarRadicados(array $filtros = [], ?int $idUsuario = null, bool $esAdmin = false): array
    {
        try {
            $db = Conexion::conectar();
            $sql = "SELECT r.id_radicado, r.numero_radicado, r.remitente, r.asunto,
                           r.tipo_radicado, r.prioridad, r.confidencialidad, r.medio_recepcion,
                           r.fecha_radicado, r.fecha_limite, r.fecha_vencimiento,
                           r.id_usuario,
                           COALESCE(u.nombre, 'Sin asignar') AS responsable,
                           u.correo AS responsable_correo,
                           e.id_estado,
                           COALESCE(e.nombre, 'Sin estado') AS estado,
                           COALESCE(e.color, '#6c757d') AS estado_color,
                           d.id_dependencia,
                           COALESCE(d.nombre, 'Sin dependencia') AS dependencia,
                           d.codigo AS dependencia_codigo,
                           t.id_tramite,
                           COALESCE(t.nombre, 'Trámite General') AS tramite,
                           t.dias_respuesta,
                           r.codigo_serie, r.codigo_subserie,
                           (SELECT COUNT(*) FROM documentos d2 WHERE d2.id_radicado = r.id_radicado) AS total_documentos,
                           (SELECT d3.ruta_archivo FROM documentos d3 WHERE d3.id_radicado = r.id_radicado ORDER BY d3.id_documento ASC LIMIT 1) AS ruta_documento,
                           (SELECT d3.nombre_archivo FROM documentos d3 WHERE d3.id_radicado = r.id_radicado ORDER BY d3.id_documento ASC LIMIT 1) AS nombre_archivo,
                           (SELECT d3.id_documento FROM documentos d3 WHERE d3.id_radicado = r.id_radicado ORDER BY d3.id_documento ASC LIMIT 1) AS id_documento
                    FROM radicados r
                    LEFT JOIN usuarios u ON u.id_usuario = r.id_usuario
                    LEFT JOIN estados e ON e.id_estado = r.id_estado
                    LEFT JOIN dependencias d ON d.id_dependencia = r.id_dependencia
                    LEFT JOIN tramites t ON t.id_tramite = r.id_tramite";

            $condiciones = [];
            $params = [];

            // 1. Control de acceso por rol: Usuarios regulares ven únicamente sus radicados asignados
            if (!$esAdmin && $idUsuario !== null) {
                $condiciones[] = "r.id_usuario = :id_usuario";
                $params[":id_usuario"] = ["valor" => $idUsuario, "tipo" => PDO::PARAM_INT];
            }

            // 2. Filtro por número de radicado exacto o prefijo
            if (!empty($filtros["numero_radicado"])) {
                $condiciones[] = "r.numero_radicado LIKE :num_radicado";
                $params[":num_radicado"] = ["valor" => "%" . trim($filtros["numero_radicado"]) . "%", "tipo" => PDO::PARAM_STR];
            }

            // 3. Filtro por tipo de radicado (ENTRADA, SALIDA, INTERNO)
            if (!empty($filtros["tipo_radicado"])) {
                $condiciones[] = "r.tipo_radicado = :tipo_radicado";
                $params[":tipo_radicado"] = ["valor" => strtoupper(trim($filtros["tipo_radicado"])), "tipo" => PDO::PARAM_STR];
            }

            // 4. Filtro por estado del trámite
            if (!empty($filtros["id_estado"])) {
                $condiciones[] = "r.id_estado = :id_estado";
                $params[":id_estado"] = ["valor" => (int)$filtros["id_estado"], "tipo" => PDO::PARAM_INT];
            }

            // 5. Filtro por dependencia
            if (!empty($filtros["id_dependencia"])) {
                $condiciones[] = "r.id_dependencia = :id_dependencia";
                $params[":id_dependencia"] = ["valor" => (int)$filtros["id_dependencia"], "tipo" => PDO::PARAM_INT];
            }

            // 6. Filtros por rango de fechas (Criterio AGN)
            if (!empty($filtros["fecha_desde"])) {
                $condiciones[] = "DATE(r.fecha_radicado) >= :fecha_desde";
                $params[":fecha_desde"] = ["valor" => trim($filtros["fecha_desde"]), "tipo" => PDO::PARAM_STR];
            }
            if (!empty($filtros["fecha_hasta"])) {
                $condiciones[] = "DATE(r.fecha_radicado) <= :fecha_hasta";
                $params[":fecha_hasta"] = ["valor" => trim($filtros["fecha_hasta"]), "tipo" => PDO::PARAM_STR];
            }
            // Compatibilidad con filtro de fecha única
            if (empty($filtros["fecha_desde"]) && empty($filtros["fecha_hasta"]) && !empty($filtros["fecha"])) {
                $condiciones[] = "DATE(r.fecha_radicado) = :fecha_unica";
                $params[":fecha_unica"] = ["valor" => trim($filtros["fecha"]), "tipo" => PDO::PARAM_STR];
            }

            // 7. Búsqueda libre multi-campo
            if (!empty($filtros["busqueda"])) {
                $condiciones[] = "(r.numero_radicado LIKE :busq OR r.asunto LIKE :busq OR r.remitente LIKE :busq OR u.nombre LIKE :busq)";
                $params[":busq"] = ["valor" => "%" . trim($filtros["busqueda"]) . "%", "tipo" => PDO::PARAM_STR];
            }

            if (!empty($condiciones)) {
                $sql .= " WHERE " . implode(" AND ", $condiciones);
            }

            $sql .= " ORDER BY r.id_radicado DESC";

            $stmt = $db->prepare($sql);
            foreach ($params as $paramClave => $paramItem) {
                $stmt->bindValue($paramClave, $paramItem["valor"], $paramItem["tipo"]);
            }
            $stmt->execute();
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // 8. Inyección de semáforo dinámico y filtro opcional por color de semáforo
            $filtroSemaforo = !empty($filtros["semaforo"]) ? strtolower(trim($filtros["semaforo"])) : null;
            $resultadoFinal = [];

            foreach ($registros as $reg) {
                $fechaLim = !empty($reg["fecha_limite"]) ? $reg["fecha_limite"] : $reg["fecha_vencimiento"];
                $semaforoInfo = self::calcularSemaforo($fechaLim, $reg["fecha_radicado"], (int)($reg["dias_respuesta"] ?? 15), (int)$reg["id_estado"]);

                $reg["dias_restantes"] = $semaforoInfo["dias_restantes"];
                $reg["semaforo_color"] = $semaforoInfo["color"];
                $reg["semaforo_badge"] = $semaforoInfo["badge_class"];
                $reg["semaforo_icono"] = $semaforoInfo["icono"];
                $reg["semaforo_texto"] = $semaforoInfo["texto"];
                $reg["semaforo_desc"]  = $semaforoInfo["descripcion"];

                // Si se solicitó filtrar específicamente por color de semáforo
                if ($filtroSemaforo && $filtroSemaforo !== "todos") {
                    if ($semaforoInfo["color"] !== $filtroSemaforo) {
                        continue;
                    }
                }

                $resultadoFinal[] = $reg;
            }

            return $resultadoFinal;
        } catch (PDOException $e) {
            error_log("Error en ModeloSeguimiento::mdlConsultarRadicados: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene el detalle completo de un radicado con todos sus documentos y clasificación CCD.
     * 
     * @param int $idRadicado Identificador del radicado.
     * @return array|null Información detallada o null si no existe.
     */
    public static function mdlObtenerRadicadoDetalle(int $idRadicado): ?array
    {
        try {
            $db = Conexion::conectar();
            $stmt = $db->prepare(
                "SELECT r.*,
                        COALESCE(u.nombre, 'Sin asignar') AS responsable,
                        u.correo AS responsable_correo,
                        e.id_estado,
                        COALESCE(e.nombre, 'Sin estado') AS estado_nombre,
                        COALESCE(e.color, '#6c757d') AS estado_color,
                        d.id_dependencia,
                        COALESCE(d.nombre, 'Sin dependencia') AS dependencia_nombre,
                        d.codigo AS dependencia_codigo,
                        t.id_tramite,
                        COALESCE(t.nombre, 'Trámite General') AS tramite_nombre,
                        t.dias_respuesta,
                        cs.nombre_serie,
                        css.nombre_subserie,
                        rem.tipo_identificacion AS remitente_tipo_doc,
                        rem.num_identificacion AS remitente_num_doc,
                        rem.correo AS remitente_correo,
                        rem.telefono AS remitente_telefono,
                        rem.direccion AS remitente_direccion
                 FROM radicados r
                 LEFT JOIN usuarios u ON u.id_usuario = r.id_usuario
                 LEFT JOIN estados e ON e.id_estado = r.id_estado
                 LEFT JOIN dependencias d ON d.id_dependencia = r.id_dependencia
                 LEFT JOIN tramites t ON t.id_tramite = r.id_tramite
                 LEFT JOIN ccd_series cs ON cs.id_serie = r.id_serie
                 LEFT JOIN ccd_subseries css ON css.id_subserie = r.id_subserie
                 LEFT JOIN remitentes_externos rem ON rem.id_remitente = r.id_remitente
                 WHERE r.id_radicado = :id"
            );
            $stmt->bindParam(":id", $idRadicado, PDO::PARAM_INT);
            $stmt->execute();
            $radicado = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$radicado) {
                return null;
            }

            // Calcular semáforo
            $fechaLim = !empty($radicado["fecha_limite"]) ? $radicado["fecha_limite"] : $radicado["fecha_vencimiento"];
            $radicado["semaforo"] = self::calcularSemaforo(
                $fechaLim,
                $radicado["fecha_radicado"],
                (int)($radicado["dias_respuesta"] ?? 15),
                (int)$radicado["id_estado"]
            );

            // Adjuntos digitales
            $stmtDocs = $db->prepare(
                "SELECT id_documento, nombre, nombre_archivo, ruta_archivo, tipo,
                        tamano, mime_type, checksum, fecha_subida
                 FROM documentos
                 WHERE id_radicado = :id
                 ORDER BY id_documento ASC"
            );
            $stmtDocs->bindParam(":id", $idRadicado, PDO::PARAM_INT);
            $stmtDocs->execute();
            $radicado["documentos"] = $stmtDocs->fetchAll(PDO::FETCH_ASSOC);

            return $radicado;
        } catch (PDOException $e) {
            error_log("Error en ModeloSeguimiento::mdlObtenerRadicadoDetalle: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene la línea de tiempo (Timeline) e historial inmutable de auditoría
     * con detalle de usuario, dependencia, hora exacta y observaciones (AGN / Ley 594 de 2000).
     * 
     * @param int $idRadicado Identificador del radicado.
     * @return array Línea de tiempo cronológica.
     */
    public static function mdlObtenerTrazabilidad(int $idRadicado): array
    {
        try {
            $db = Conexion::conectar();

            // 1. Consultar historial de eventos explícitos en historial_radicado
            $stmtHist = $db->prepare(
                "SELECT h.id_historial, h.id_radicado, h.id_usuario, h.accion,
                        h.descripcion, h.fecha,
                        COALESCE(u.nombre, 'Sistema Automatizado') AS usuario_nombre,
                        u.correo AS usuario_correo,
                        COALESCE(d.nombre, 'Ventanilla Única / Correspondencia') AS dependencia_nombre,
                        d.codigo AS dependencia_codigo
                 FROM historial_radicado h
                 LEFT JOIN usuarios u ON u.id_usuario = h.id_usuario
                 LEFT JOIN dependencias d ON d.id_dependencia = u.id_dependencia
                 WHERE h.id_radicado = :id
                 ORDER BY h.fecha ASC, h.id_historial ASC"
            );
            $stmtHist->bindParam(":id", $idRadicado, PDO::PARAM_INT);
            $stmtHist->execute();
            $historial = $stmtHist->fetchAll(PDO::FETCH_ASSOC);

            // 2. Si no hay registros previos, sintetizar el hito inicial de radicación desde radicados
            if (empty($historial)) {
                $stmtRad = $db->prepare(
                    "SELECT r.id_radicado, r.id_usuario, r.fecha_radicado,
                            COALESCE(u.nombre, 'Funcionario Radicador') AS usuario_nombre,
                            COALESCE(d.nombre, 'Gestión Documental / Ventanilla Única') AS dependencia_nombre
                     FROM radicados r
                     LEFT JOIN usuarios u ON u.id_usuario = r.id_usuario
                     LEFT JOIN dependencias d ON d.id_dependencia = r.id_dependencia
                     WHERE r.id_radicado = :id"
                );
                $stmtRad->bindParam(":id", $idRadicado, PDO::PARAM_INT);
                $stmtRad->execute();
                $radBase = $stmtRad->fetch(PDO::FETCH_ASSOC);

                if ($radBase) {
                    $historial[] = [
                        "id_historial"        => 1,
                        "id_radicado"         => $radBase["id_radicado"],
                        "id_usuario"          => $radBase["id_usuario"],
                        "accion"              => "radicacion",
                        "descripcion"         => "Radicación oficial del documento en el Sistema ODIN y generación de consecutivo inmutable.",
                        "fecha"               => $radBase["fecha_radicado"],
                        "usuario_nombre"      => $radBase["usuario_nombre"],
                        "usuario_correo"      => "",
                        "dependencia_nombre"  => $radBase["dependencia_nombre"],
                        "dependencia_codigo"  => "GD-01"
                    ];
                }
            }

            // 3. Normalizar hitos con iconos y estilos acordes a AdminLTE 3 Timeline
            $timelineFinal = [];
            foreach ($historial as $item) {
                $accion = strtolower(trim($item["accion"]));
                $icono = "fas fa-history bg-secondary";
                $titulo = "Movimiento Registrado";

                if (stripos($accion, "radica") !== false) {
                    $icono = "fas fa-file-signature bg-primary";
                    $titulo = "Radicación Inicial";
                } elseif (stripos($accion, "traslado") !== false || stripos($accion, "reasigna") !== false) {
                    $icono = "fas fa-exchange-alt bg-warning text-white";
                    $titulo = "Traslado / Reasignación Interdependencias";
                } elseif (stripos($accion, "proceso") !== false || stripos($accion, "tramite") !== false) {
                    $icono = "fas fa-cogs bg-info";
                    $titulo = "Inicio de Gestión / Trámite";
                } elseif (stripos($accion, "devol") !== false) {
                    $icono = "fas fa-undo bg-danger";
                    $titulo = "Devolución por Requisitos";
                } elseif (stripos($accion, "finaliz") !== false || stripos($accion, "cierre") !== false || stripos($accion, "respon") !== false) {
                    $icono = "fas fa-check-circle bg-success";
                    $titulo = "Respuesta / Finalización del Trámite";
                } elseif (stripos($accion, "observa") !== false) {
                    $icono = "fas fa-comment-alt bg-secondary";
                    $titulo = "Anotación / Observación de Seguimiento";
                }

                $item["icono_clase"]  = $icono;
                $item["titulo_evento"] = $titulo;
                $timelineFinal[] = $item;
            }

            return $timelineFinal;
        } catch (PDOException $e) {
            error_log("Error en ModeloSeguimiento::mdlObtenerTrazabilidad: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Consulta pública y ciudadana simplificada (Ley 1712 de 2014 - Transparencia y Ley 1581 de Habeas Data).
     * Enmascara estrictamente los datos de identificación y contacto privados de personas naturales.
     * 
     * @param string $codigo Número de radicado o código institucional.
     * @return array|null Ficha de seguimiento ciudadano o null si no coincide.
     */
    public static function mdlConsultaPublica(string $codigo): ?array
    {
        try {
            $db = Conexion::conectar();
            $codigoLimpio = trim($codigo);

            $stmt = $db->prepare(
                "SELECT r.id_radicado, r.numero_radicado, r.asunto, r.tipo_radicado,
                        r.fecha_radicado, r.fecha_limite, r.fecha_vencimiento,
                        r.remitente,
                        e.id_estado,
                        COALESCE(e.nombre, 'En trámite') AS estado,
                        COALESCE(e.color, '#17a2b8') AS estado_color,
                        COALESCE(d.nombre, 'Área Responsable') AS dependencia,
                        COALESCE(t.nombre, 'Trámite Ciudadano') AS tramite,
                        t.dias_respuesta
                 FROM radicados r
                 LEFT JOIN estados e ON e.id_estado = r.id_estado
                 LEFT JOIN dependencias d ON d.id_dependencia = r.id_dependencia
                 LEFT JOIN tramites t ON t.id_tramite = r.id_tramite
                 WHERE LOWER(r.numero_radicado) = LOWER(:codigo)
                 LIMIT 1"
            );
            $stmt->bindParam(":codigo", $codigoLimpio, PDO::PARAM_STR);
            $stmt->execute();
            $rad = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$rad) {
                return null;
            }

            // Enmascaramiento de datos personales de transparencia (Habeas Data)
            $remitenteOriginal = trim($rad["remitente"] ?? "");
            if (empty($remitenteOriginal) || stripos($remitenteOriginal, "ANÓNIMO") !== false) {
                $remitentePublico = "ANÓNIMO (Protegido por Ley 1712 de 2014)";
            } else {
                // Enmascarar manteniendo primera letra y asteriscos
                $partes = explode(" ", $remitenteOriginal);
                $enmascarado = [];
                foreach ($partes as $p) {
                    if (mb_strlen($p) <= 2) {
                        $enmascarado[] = $p;
                    } else {
                        $enmascarado[] = mb_substr($p, 0, 1) . str_repeat("*", mb_strlen($p) - 1);
                    }
                }
                $remitentePublico = implode(" ", $enmascarado);
            }
            $rad["remitente"] = $remitentePublico;

            // Semáforo dinámico de cumplimiento
            $fechaLim = !empty($rad["fecha_limite"]) ? $rad["fecha_limite"] : $rad["fecha_vencimiento"];
            $rad["semaforo"] = self::calcularSemaforo(
                $fechaLim,
                $rad["fecha_radicado"],
                (int)($rad["dias_respuesta"] ?? 15),
                (int)$rad["id_estado"]
            );

            // Línea de tiempo pública simplificada (sin exponer usuarios internos privados)
            $trazas = self::mdlObtenerTrazabilidad((int)$rad["id_radicado"]);
            $trazasPublicas = [];
            foreach ($trazas as $t) {
                $trazasPublicas[] = [
                    "fecha"        => $t["fecha"],
                    "evento"       => $t["titulo_evento"],
                    "descripcion"  => $t["descripcion"],
                    "dependencia"  => $t["dependencia_nombre"],
                    "icono_clase"  => $t["icono_clase"]
                ];
            }
            $rad["trazabilidad"] = $trazasPublicas;

            return $rad;
        } catch (PDOException $e) {
            error_log("Error en ModeloSeguimiento::mdlConsultaPublica: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Registra un nuevo movimiento o transición en el historial de trazabilidad del radicado.
     * Actualiza de forma atómica el estado o dependencia si aplica y registra en auditoría inmutable.
     * 
     * @param int $idRadicado Identificador del radicado.
     * @param int $idUsuario Usuario que realiza la acción.
     * @param string $accion Nombre de la acción (traslado, observacion, cambio_estado, devolucion, finalizacion).
     * @param string $descripcion Justificación u observación archivística.
     * @param int|null $nuevoEstado Nuevo ID de estado si hubo transición.
     * @param int|null $nuevaDependencia Nuevo ID de dependencia si hubo traslado.
     * @param int|null $nuevoUsuarioResponsable Nuevo funcionario asignado si aplica.
     * @return bool True si la transacción fue exitosa.
     */
    public static function mdlRegistrarMovimiento(
        int $idRadicado,
        int $idUsuario,
        string $accion,
        string $descripcion,
        ?int $nuevoEstado = null,
        ?int $nuevaDependencia = null,
        ?int $nuevoUsuarioResponsable = null
    ): bool {
        $db = Conexion::conectar();
        $db->beginTransaction();

        try {
            $ahora = date("Y-m-d H:i:s");

            // 1. Obtener estado y asignación anterior para auditoría
            $stmtPrev = $db->prepare("SELECT id_estado, id_dependencia, id_usuario FROM radicados WHERE id_radicado = :id");
            $stmtPrev->bindParam(":id", $idRadicado, PDO::PARAM_INT);
            $stmtPrev->execute();
            $prev = $stmtPrev->fetch(PDO::FETCH_ASSOC);

            if (!$prev) {
                $db->rollBack();
                return false;
            }

            // 2. Insertar en historial inmutable
            $stmtHist = $db->prepare(
                "INSERT INTO historial_radicado (id_radicado, id_usuario, accion, descripcion, fecha)
                 VALUES (:rad_id, :usr_id, :accion, :desc, :fecha)"
            );
            $stmtHist->bindParam(":rad_id", $idRadicado, PDO::PARAM_INT);
            $stmtHist->bindParam(":usr_id", $idUsuario,  PDO::PARAM_INT);
            $stmtHist->bindParam(":accion", $accion,     PDO::PARAM_STR);
            $stmtHist->bindParam(":desc",   $descripcion,PDO::PARAM_STR);
            $stmtHist->bindParam(":fecha",  $ahora,      PDO::PARAM_STR);
            $stmtHist->execute();

            // 3. Si hay actualización de estado
            if ($nuevoEstado !== null && $nuevoEstado > 0 && (int)$prev["id_estado"] !== $nuevoEstado) {
                $stmtUpdateEstado = $db->prepare("UPDATE radicados SET id_estado = :estado WHERE id_radicado = :id");
                $stmtUpdateEstado->bindParam(":estado", $nuevoEstado, PDO::PARAM_INT);
                $stmtUpdateEstado->bindParam(":id",     $idRadicado,  PDO::PARAM_INT);
                $stmtUpdateEstado->execute();

                // Auditoría de cambio de estado
                $stmtAudit = $db->prepare(
                    "INSERT INTO auditoria_radicados (id_radicado, id_usuario, accion, campo_modificado, valor_anterior, valor_nuevo, fecha, comentario)
                     VALUES (:rad_id, :usr_id, 'CAMBIO_ESTADO', 'id_estado', :val_ant, :val_nue, :fecha, :coment)"
                );
                $antStr = (string)$prev["id_estado"];
                $nueStr = (string)$nuevoEstado;
                $stmtAudit->bindParam(":rad_id", $idRadicado, PDO::PARAM_INT);
                $stmtAudit->bindParam(":usr_id", $idUsuario,  PDO::PARAM_INT);
                $stmtAudit->bindParam(":val_ant", $antStr,    PDO::PARAM_STR);
                $stmtAudit->bindParam(":val_nue", $nueStr,    PDO::PARAM_STR);
                $stmtAudit->bindParam(":fecha",   $ahora,     PDO::PARAM_STR);
                $stmtAudit->bindParam(":coment",  $descripcion, PDO::PARAM_STR);
                $stmtAudit->execute();
            }

            // 4. Si hay traslado de dependencia o reasignación de funcionario
            if (($nuevaDependencia !== null && $nuevaDependencia > 0) || ($nuevoUsuarioResponsable !== null && $nuevoUsuarioResponsable > 0)) {
                $depFinal = ($nuevaDependencia !== null && $nuevaDependencia > 0) ? $nuevaDependencia : (int)$prev["id_dependencia"];
                $usrFinal = ($nuevoUsuarioResponsable !== null && $nuevoUsuarioResponsable > 0) ? $nuevoUsuarioResponsable : (int)$prev["id_usuario"];

                $stmtUpdateAsig = $db->prepare("UPDATE radicados SET id_dependencia = :dep, id_usuario = :usr WHERE id_radicado = :id");
                $stmtUpdateAsig->bindParam(":dep", $depFinal,    PDO::PARAM_INT);
                $stmtUpdateAsig->bindParam(":usr", $usrFinal,    PDO::PARAM_INT);
                $stmtUpdateAsig->bindParam(":id",  $idRadicado,  PDO::PARAM_INT);
                $stmtUpdateAsig->execute();

                // Registrar en reasignaciones
                $stmtReasig = $db->prepare(
                    "INSERT INTO reasignaciones (id_radicado, id_usuario_anterior, id_usuario_nuevo, id_dependencia_nueva, fecha)
                     VALUES (:rad_id, :usr_ant, :usr_nue, :dep_nue, :fecha)"
                );
                $usrAnt = (int)$prev["id_usuario"];
                $stmtReasig->bindParam(":rad_id",  $idRadicado, PDO::PARAM_INT);
                $stmtReasig->bindParam(":usr_ant", $usrAnt,     PDO::PARAM_INT);
                $stmtReasig->bindParam(":usr_nue", $usrFinal,   PDO::PARAM_INT);
                $stmtReasig->bindParam(":dep_nue", $depFinal,   PDO::PARAM_INT);
                $stmtReasig->bindParam(":fecha",   $ahora,      PDO::PARAM_STR);
                $stmtReasig->execute();
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Error en ModeloSeguimiento::mdlRegistrarMovimiento: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Catálogos para poblar los filtros del módulo (dependencias, estados y trámites).
     * 
     * @return array
     */
    public static function mdlObtenerCatalogosFiltros(): array
    {
        try {
            $db = Conexion::conectar();

            $stmtDeps = $db->query("SELECT id_dependencia, nombre, codigo FROM dependencias WHERE estado = 'Activo' ORDER BY nombre ASC");
            $dependencias = $stmtDeps->fetchAll(PDO::FETCH_ASSOC);

            $stmtEstados = $db->query("SELECT id_estado, nombre, color FROM estados ORDER BY id_estado ASC");
            $estados = $stmtEstados->fetchAll(PDO::FETCH_ASSOC);

            $stmtTramites = $db->query("SELECT id_tramite, nombre, dias_respuesta FROM tramites WHERE activo = 1 ORDER BY nombre ASC");
            $tramites = $stmtTramites->fetchAll(PDO::FETCH_ASSOC);

            return [
                "dependencias" => $dependencias,
                "estados"      => $estados,
                "tramites"     => $tramites
            ];
        } catch (PDOException $e) {
            error_log("Error en ModeloSeguimiento::mdlObtenerCatalogosFiltros: " . $e->getMessage());
            return ["dependencias" => [], "estados" => [], "tramites" => []];
        }
    }
}
