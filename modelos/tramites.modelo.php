<?php

require_once __DIR__ . "/conexion.php";

/**
 * Modelo de Persistencia para Catálogo y Parametrización de Trámites — Proyecto ODIN
 * 
 * Gestiona el catálogo de trámites (RF-26, RF-28, RF-29), plazos de respuesta,
 * estados iniciales, dependencias responsables y conteos de métricas operativas.
 */
class ModeloTramites
{
    /**
     * Consulta el catálogo completo de tipos de trámites con dependencias y estados asociados.
     * 
     * @return array Listado asociativo de trámites.
     */
    public static function mdlMostrarTramites(): array
    {
        try {
            $db = Conexion::conectar();
            $sql = "SELECT t.id_tramite, t.nombre, t.descripcion, t.dias_respuesta,
                           t.prioridad_default, t.activo, t.requiere_respuesta,
                           t.fecha_creacion, t.fecha_actualizacion,
                           t.id_dependencia_responsable,
                           COALESCE(d.nombre, 'Sin asignar') AS dependencia_nombre,
                           t.id_estado_inicial,
                           COALESCE(e.nombre, 'Por defecto') AS estado_inicial_nombre,
                           COALESCE(e.color, '#6c757d') AS estado_inicial_color
                    FROM tramites t
                    LEFT JOIN dependencias d ON d.id_dependencia = t.id_dependencia_responsable
                    LEFT JOIN estados e ON e.id_estado = t.id_estado_inicial
                    ORDER BY t.id_tramite DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ModeloTramites::mdlMostrarTramites: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Consulta un tipo de trámite por su identificador primario.
     * 
     * @param int $id Identificador del trámite.
     * @return array|null Datos del trámite o null si no existe.
     */
    public static function mdlObtenerTramite(int $id): ?array
    {
        try {
            $db = Conexion::conectar();
            $stmt = $db->prepare(
                "SELECT t.*, d.nombre AS dependencia_nombre, e.nombre AS estado_inicial_nombre
                 FROM tramites t
                 LEFT JOIN dependencias d ON d.id_dependencia = t.id_dependencia_responsable
                 LEFT JOIN estados e ON e.id_estado = t.id_estado_inicial
                 WHERE t.id_tramite = :id"
            );
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            $tramite = $stmt->fetch(PDO::FETCH_ASSOC);
            return $tramite ?: null;
        } catch (PDOException $e) {
            error_log("Error en ModeloTramites::mdlObtenerTramite: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Inserta un nuevo tipo de trámite en la base de datos con sentencias preparadas.
     * 
     * @param array $datos Datos parametrizados del trámite.
     * @return int|false ID del registro creado o false en caso de fallo.
     */
    public static function mdlCrearTramite(array $datos)
    {
        try {
            $db = Conexion::conectar();
            $sql = "INSERT INTO tramites (
                        nombre, descripcion, id_dependencia_responsable,
                        id_estado_inicial, dias_respuesta, prioridad_default,
                        requiere_respuesta, activo, fecha_creacion
                    ) VALUES (
                        :nombre, :descripcion, :id_dep,
                        :id_estado, :dias, :prio,
                        :requiere, :activo, NOW()
                    )";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(":nombre",      $datos["nombre"],      PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
            $stmt->bindValue(":id_dep",      $datos["id_dependencia_responsable"] ?: null, $datos["id_dependencia_responsable"] ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(":id_estado",   $datos["id_estado_inicial"] ?: null,          $datos["id_estado_inicial"] ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(":dias",        isset($datos["dias_respuesta"]) ? (int)$datos["dias_respuesta"] : 15, PDO::PARAM_INT);
            $stmt->bindParam(":prio",        $datos["prioridad_default"], PDO::PARAM_STR);
            $stmt->bindValue(":requiere",    !empty($datos["requiere_respuesta"]) ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(":activo",      isset($datos["activo"]) ? ((int)$datos["activo"] ? 1 : 0) : 1, PDO::PARAM_INT);

            if ($stmt->execute()) {
                return (int)$db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en ModeloTramites::mdlCrearTramite: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza un tipo de trámite existente en la base de datos.
     * 
     * @param int $id Identificador del trámite.
     * @param array $datos Datos actualizados del trámite.
     * @return bool True si la actualización fue exitosa, false en caso contrario.
     */
    public static function mdlActualizarTramite(int $id, array $datos): bool
    {
        try {
            $db = Conexion::conectar();
            $sql = "UPDATE tramites SET
                        nombre = :nombre,
                        descripcion = :descripcion,
                        id_dependencia_responsable = :id_dep,
                        id_estado_inicial = :id_estado,
                        dias_respuesta = :dias,
                        prioridad_default = :prio,
                        requiere_respuesta = :requiere,
                        activo = :activo,
                        fecha_actualizacion = NOW()
                    WHERE id_tramite = :id";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(":id",          $id,                   PDO::PARAM_INT);
            $stmt->bindParam(":nombre",      $datos["nombre"],      PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $datos["descripcion"], PDO::PARAM_STR);
            $stmt->bindValue(":id_dep",      $datos["id_dependencia_responsable"] ?: null, $datos["id_dependencia_responsable"] ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(":id_estado",   $datos["id_estado_inicial"] ?: null,          $datos["id_estado_inicial"] ? PDO::PARAM_INT : PDO::PARAM_NULL);
            $stmt->bindValue(":dias",        isset($datos["dias_respuesta"]) ? (int)$datos["dias_respuesta"] : 15, PDO::PARAM_INT);
            $stmt->bindParam(":prio",        $datos["prioridad_default"], PDO::PARAM_STR);
            $stmt->bindValue(":requiere",    !empty($datos["requiere_respuesta"]) ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(":activo",      isset($datos["activo"]) ? ((int)$datos["activo"] ? 1 : 0) : 1, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en ModeloTramites::mdlActualizarTramite: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambia de forma atómica el estado activo/inactivo de un tipo de trámite.
     * 
     * @param int $id Identificador del trámite.
     * @param int $activo 1 para Activo, 0 para Inactivo.
     * @return bool Resultado de la operación.
     */
    public static function mdlCambiarEstado(int $id, int $activo): bool
    {
        try {
            $db = Conexion::conectar();
            $stmt = $db->prepare("UPDATE tramites SET activo = :activo, fecha_actualizacion = NOW() WHERE id_tramite = :id");
            $stmt->bindParam(":id",     $id,     PDO::PARAM_INT);
            $stmt->bindParam(":activo", $activo, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en ModeloTramites::mdlCambiarEstado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene las métricas cuantitativas para las tarjetas de resumen (KPIs) de la bandeja de trámites.
     * 
     * @return array Conteo de pendientes, en proceso, finalizados, vencidos y total.
     */
    public static function mdlObtenerEstadisticasTramites(): array
    {
        try {
            $db = Conexion::conectar();

            // Total radicados
            $stmtTotal = $db->query("SELECT COUNT(*) FROM radicados");
            $total = (int)$stmtTotal->fetchColumn();

            // Pendientes (id_estado = 1 o nombre estado Pendiente)
            $stmtPendientes = $db->query(
                "SELECT COUNT(*) FROM radicados r
                 LEFT JOIN estados e ON e.id_estado = r.id_estado
                 WHERE r.id_estado = 1 OR LOWER(e.nombre) LIKE '%pendiente%' OR LOWER(e.nombre) LIKE '%recibido%'"
            );
            $pendientes = (int)$stmtPendientes->fetchColumn();

            // En proceso (id_estado = 2 o nombre estado en proceso/trámite/revisión)
            $stmtProceso = $db->query(
                "SELECT COUNT(*) FROM radicados r
                 LEFT JOIN estados e ON e.id_estado = r.id_estado
                 WHERE r.id_estado IN (2, 3) OR LOWER(e.nombre) LIKE '%proceso%' OR LOWER(e.nombre) LIKE '%revis%'"
            );
            $enProceso = (int)$stmtProceso->fetchColumn();

            // Finalizados (id_estado = 6 o fecha_cierre no nula o nombre estado Finalizado/Cerrado)
            $stmtFinalizados = $db->query(
                "SELECT COUNT(*) FROM radicados r
                 LEFT JOIN estados e ON e.id_estado = r.id_estado
                 WHERE r.id_estado IN (4, 6, 7) OR r.fecha_cierre IS NOT NULL OR LOWER(e.nombre) LIKE '%finaliz%' OR LOWER(e.nombre) LIKE '%cerrad%'"
            );
            $finalizados = (int)$stmtFinalizados->fetchColumn();

            // Vencidos (fecha_vencimiento anterior a hoy y no finalizados ni archivados)
            $stmtVencidos = $db->query(
                "SELECT COUNT(*) FROM radicados r
                 LEFT JOIN estados e ON e.id_estado = r.id_estado
                 WHERE r.fecha_vencimiento IS NOT NULL
                   AND r.fecha_vencimiento != ''
                   AND STR_TO_DATE(r.fecha_vencimiento, '%Y-%m-%d') < CURDATE()
                   AND (r.id_estado NOT IN (6, 7) OR r.id_estado IS NULL)
                   AND r.fecha_cierre IS NULL"
            );
            $vencidos = (int)$stmtVencidos->fetchColumn();

            return [
                "total"       => $total,
                "pendientes"  => $pendientes,
                "en_proceso"  => $enProceso,
                "finalizados" => $finalizados,
                "vencidos"    => $vencidos
            ];
        } catch (PDOException $e) {
            error_log("Error en ModeloTramites::mdlObtenerEstadisticasTramites: " . $e->getMessage());
            return [
                "total"       => 0,
                "pendientes"  => 0,
                "en_proceso"  => 0,
                "finalizados" => 0,
                "vencidos"    => 0
            ];
        }
    }
}
