<?php

require_once "conexion.php";

/**
 * Modelo de Dependencias — Proyecto ODIN
 * 
 * Gestiona la persistencia de las dependencias organizacionales y la asociación
 * del usuario encargado / responsable de cada área mediante PDO y sentencias preparadas.
 */
class ModeloDependencias
{
    /**
     * Consulta el listado de dependencias o una en específico por ID con los datos
     * de su usuario encargado responsable.
     * 
     * @param int|string|null $id ID de la dependencia o null para todas.
     * @return array|null Colección asociativa de dependencias o registro individual.
     */
    public static function mdlMostrarDependencias($id = null)
    {
        try {
            $db = Conexion::conectar();

            // Si se pasa un ID numérico, consultar registro individual
            if ($id !== null && is_numeric($id)) {
                $sql = "SELECT d.id_dependencia, d.nombre, d.dependencia, d.codigo, d.descripcion,
                               d.tipo, d.estado, d.id_usuario_responsable,
                               u.nombre AS encargado_nombre,
                               u.correo AS encargado_correo,
                               COALESCE(r.nombre, 'Encargado del área') AS encargado_rol
                        FROM dependencias d
                        LEFT JOIN usuarios u ON u.id_usuario = d.id_usuario_responsable
                        LEFT JOIN roles r ON r.id_rol = u.id_rol
                        WHERE d.id_dependencia = :id
                        LIMIT 1";
                $stmt = $db->prepare($sql);
                $stmt->bindValue(":id", (int)$id, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
            }

            // Listado general con información del encargado
            $sql = "SELECT d.id_dependencia, d.nombre, d.dependencia, d.codigo, d.descripcion,
                           d.tipo, d.estado, d.id_usuario_responsable,
                           u.nombre AS encargado_nombre,
                           u.correo AS encargado_correo,
                           COALESCE(r.nombre, 'Encargado del área') AS encargado_rol
                    FROM dependencias d
                    LEFT JOIN usuarios u ON u.id_usuario = d.id_usuario_responsable
                    LEFT JOIN roles r ON r.id_rol = u.id_rol
                    ORDER BY d.id_dependencia ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en ModeloDependencias::mdlMostrarDependencias: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Consulta una dependencia específica por su ID.
     */
    public static function mdlMostrarDependenciaPorId(int $id): ?array
    {
        $res = self::mdlMostrarDependencias($id);
        return is_array($res) ? $res : null;
    }

    /**
     * Registra una nueva dependencia en la base de datos.
     * 
     * @param array $datos Datos de la dependencia.
     * @return int|false ID de la dependencia creada o false en caso de error.
     */
    public static function mdlCrearDependencia(array $datos)
    {
        try {
            $db = Conexion::conectar();

            $sql = "INSERT INTO dependencias (nombre, dependencia, codigo, descripcion, tipo, estado, id_usuario_responsable)
                    VALUES (:nombre, :dependencia, :codigo, :descripcion, :tipo, :estado, :id_usuario_responsable)";
            $stmt = $db->prepare($sql);

            $nombre = trim($datos["nombre"]);
            $dependencia = !empty($datos["dependencia"]) ? trim($datos["dependencia"]) : $nombre;
            $codigo = !empty($datos["codigo"]) ? strtoupper(trim($datos["codigo"])) : null;
            $descripcion = !empty($datos["descripcion"]) ? trim($datos["descripcion"]) : null;
            $tipo = !empty($datos["tipo"]) ? trim($datos["tipo"]) : "operativa";
            $estado = !empty($datos["estado"]) ? trim($datos["estado"]) : "Activo";
            $idResponsable = !empty($datos["id_usuario_responsable"]) ? (int)$datos["id_usuario_responsable"] : null;

            $stmt->bindValue(":nombre", $nombre, PDO::PARAM_STR);
            $stmt->bindValue(":dependencia", $dependencia, PDO::PARAM_STR);
            $stmt->bindValue(":codigo", $codigo, $codigo !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(":descripcion", $descripcion, $descripcion !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(":tipo", $tipo, PDO::PARAM_STR);
            $stmt->bindValue(":estado", $estado, PDO::PARAM_STR);
            $stmt->bindValue(":id_usuario_responsable", $idResponsable, $idResponsable !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

            if ($stmt->execute()) {
                $idNuevaDep = (int)$db->lastInsertId();

                // Si se asignó un usuario encargado, asociarlo a esta dependencia
                if ($idResponsable !== null && $idNuevaDep > 0) {
                    $stmtUser = $db->prepare("UPDATE usuarios SET id_dependencia = :id_dep WHERE id_usuario = :id_user");
                    $stmtUser->bindValue(":id_dep", $idNuevaDep, PDO::PARAM_INT);
                    $stmtUser->bindValue(":id_user", $idResponsable, PDO::PARAM_INT);
                    $stmtUser->execute();
                }

                return $idNuevaDep;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en ModeloDependencias::mdlCrearDependencia: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza los datos de una dependencia existente, incluyendo el encargado responsable.
     * 
     * @param int $id ID de la dependencia a modificar.
     * @param array $datos Datos actualizados.
     * @return bool True si la operación fue exitosa, false en caso contrario.
     */
    public static function mdlActualizarDependencia(int $id, array $datos): bool
    {
        try {
            $db = Conexion::conectar();

            $sql = "UPDATE dependencias
                    SET nombre = :nombre,
                        dependencia = :dependencia,
                        codigo = :codigo,
                        descripcion = :descripcion,
                        tipo = :tipo,
                        estado = :estado,
                        id_usuario_responsable = :id_usuario_responsable
                    WHERE id_dependencia = :id";
            $stmt = $db->prepare($sql);

            $nombre = trim($datos["nombre"]);
            $dependencia = !empty($datos["dependencia"]) ? trim($datos["dependencia"]) : $nombre;
            $codigo = !empty($datos["codigo"]) ? strtoupper(trim($datos["codigo"])) : null;
            $descripcion = !empty($datos["descripcion"]) ? trim($datos["descripcion"]) : null;
            $tipo = !empty($datos["tipo"]) ? trim($datos["tipo"]) : "operativa";
            $estado = !empty($datos["estado"]) ? trim($datos["estado"]) : "Activo";
            $idResponsable = !empty($datos["id_usuario_responsable"]) ? (int)$datos["id_usuario_responsable"] : null;

            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            $stmt->bindValue(":nombre", $nombre, PDO::PARAM_STR);
            $stmt->bindValue(":dependencia", $dependencia, PDO::PARAM_STR);
            $stmt->bindValue(":codigo", $codigo, $codigo !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(":descripcion", $descripcion, $descripcion !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
            $stmt->bindValue(":tipo", $tipo, PDO::PARAM_STR);
            $stmt->bindValue(":estado", $estado, PDO::PARAM_STR);
            $stmt->bindValue(":id_usuario_responsable", $idResponsable, $idResponsable !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

            $ok = $stmt->execute();

            if ($ok && $idResponsable !== null) {
                // Sincronizar el usuario con la dependencia
                $stmtUser = $db->prepare("UPDATE usuarios SET id_dependencia = :id_dep WHERE id_usuario = :id_user");
                $stmtUser->bindValue(":id_dep", $id, PDO::PARAM_INT);
                $stmtUser->bindValue(":id_user", $idResponsable, PDO::PARAM_INT);
                $stmtUser->execute();

                // Sincronizar trámites asignados a esta dependencia con el nuevo encargado
                $stmtTram = $db->prepare("UPDATE tramites SET id_usuario_responsable = :id_user WHERE id_dependencia_responsable = :id_dep");
                $stmtTram->bindValue(":id_user", $idResponsable, PDO::PARAM_INT);
                $stmtTram->bindValue(":id_dep", $id, PDO::PARAM_INT);
                $stmtTram->execute();
            }

            return $ok;
        } catch (PDOException $e) {
            error_log("Error en ModeloDependencias::mdlActualizarDependencia: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambio atómico de estado de una dependencia (Activo / Inactivo).
     * 
     * @param int $id ID de la dependencia.
     * @param string $nuevoEstado 'Activo' o 'Inactivo'.
     * @return bool
     */
    public static function mdlCambiarEstado(int $id, string $nuevoEstado): bool
    {
        try {
            $db = Conexion::conectar();
            $stmt = $db->prepare("UPDATE dependencias SET estado = :estado WHERE id_dependencia = :id");
            $stmt->bindValue(":estado", $nuevoEstado, PDO::PARAM_STR);
            $stmt->bindValue(":id", $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en ModeloDependencias::mdlCambiarEstado: " . $e->getMessage());
            return false;
        }
    }
}
