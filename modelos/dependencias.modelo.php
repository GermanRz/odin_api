<?php

require_once "conexion.php";

/**
 * ModeloDependencias
 * 
 * Capa de persistencia PDO para la gestión de dependencias institucionales.
 * Utiliza las columnas originales de la tabla dependencias:
 * (id_dependencia, nombre, descripcion, estado, dependencia, codigo, tipo).
 * Cumple con RF-40, RF-41 y RNF-14.
 */
class ModeloDependencias {

    /**
     * Lista todas las dependencias con conteo de usuarios y radicados asociados,
     * o consulta una dependencia específica por campo.
     * 
     * @param string|null $tabla Nombre de la tabla (retrocompatibilidad).
     * @param string|null $campo Campo de filtro opcional (id_dependencia, codigo, nombre).
     * @param mixed $valor Valor del filtro.
     * @return array|false
     */
    public static function mdlMostrarDependencias($tabla = "dependencias", $campo = null, $valor = null) {
        $pdo = Conexion::conectar();

        if ($campo !== null) {
            $camposPermitidos = ["id_dependencia", "codigo", "nombre", "estado"];
            if (!in_array($campo, $camposPermitidos, true)) {
                $campo = "id_dependencia";
            }

            try {
                $sql = "SELECT d.id_dependencia,
                               d.nombre,
                               d.dependencia,
                               d.codigo,
                               d.descripcion,
                               d.tipo,
                               d.estado,
                               (SELECT COUNT(*) FROM usuarios u WHERE u.id_dependencia = d.id_dependencia) AS total_usuarios,
                               (SELECT COUNT(*) FROM radicados r WHERE r.id_dependencia = d.id_dependencia) AS total_radicados
                        FROM dependencias d
                        WHERE d.{$campo} = :valor
                        LIMIT 1";
                $stmt = $pdo->prepare($sql);
                if ($campo === "id_dependencia") {
                    $idVal = (int)$valor;
                    $stmt->bindParam(":valor", $idVal, PDO::PARAM_INT);
                } else {
                    $strVal = (string)$valor;
                    $stmt->bindParam(":valor", $strVal, PDO::PARAM_STR);
                }
                $stmt->execute();
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log("Error en mdlMostrarDependencias ({$campo}): " . $e->getMessage());
                return false;
            }
        }

        try {
            $sql = "SELECT d.id_dependencia,
                           d.nombre,
                           d.dependencia,
                           d.codigo,
                           d.descripcion,
                           d.tipo,
                           d.estado,
                           (SELECT COUNT(*) FROM usuarios u WHERE u.id_dependencia = d.id_dependencia) AS total_usuarios,
                           (SELECT COUNT(*) FROM radicados r WHERE r.id_dependencia = d.id_dependencia) AS total_radicados
                    FROM dependencias d
                    ORDER BY d.id_dependencia ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlMostrarDependencias (listado): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene las métricas generales del módulo de dependencias (KPIs).
     * 
     * @return array
     */
    public static function mdlEstadisticasDependencias(): array {
        $pdo = Conexion::conectar();
        try {
            $total = (int)$pdo->query("SELECT COUNT(*) FROM dependencias")->fetchColumn();
            $activas = (int)$pdo->query("SELECT COUNT(*) FROM dependencias WHERE estado IN ('Activo', 'Activa')")->fetchColumn();
            $inactivas = (int)$pdo->query("SELECT COUNT(*) FROM dependencias WHERE estado NOT IN ('Activo', 'Activa')")->fetchColumn();

            return [
                "total"     => $total,
                "activas"   => $activas,
                "inactivas" => $inactivas
            ];
        } catch (PDOException $e) {
            error_log("Error en mdlEstadisticasDependencias: " . $e->getMessage());
            return [
                "total"     => 0,
                "activas"   => 0,
                "inactivas" => 0
            ];
        }
    }

    /**
     * Registra una nueva dependencia institucional.
     * 
     * @param array $datos
     * @return int|false ID de la dependencia creada o false en caso de error.
     */
    public static function mdlCrearDependencia(array $datos) {
        $pdo = Conexion::conectar();

        try {
            $stmt = $pdo->prepare(
                "INSERT INTO dependencias (nombre, descripcion, estado, dependencia, codigo, tipo)
                 VALUES (:nombre, :descripcion, :estado, :dependencia, :codigo, :tipo)"
            );

            $nombre      = $datos["nombre"] ?? "";
            $descripcion = $datos["descripcion"] !== "" ? $datos["descripcion"] : ("Unidad organizacional: " . $nombre);
            $estado      = $datos["estado"] ?? "Activo";
            $dependencia = $datos["dependencia"] ?? $nombre;
            $codigo      = $datos["codigo"] ?? "";
            $tipo        = $datos["tipo"] ?? "operativa";

            $stmt->bindParam(":nombre",      $nombre, PDO::PARAM_STR);
            $stmt->bindParam(":descripcion", $descripcion, PDO::PARAM_STR);
            $stmt->bindParam(":estado",      $estado, PDO::PARAM_STR);
            $stmt->bindParam(":dependencia", $dependencia, PDO::PARAM_STR);
            $stmt->bindParam(":codigo",      $codigo, PDO::PARAM_STR);
            $stmt->bindParam(":tipo",        $tipo, PDO::PARAM_STR);
            $stmt->execute();

            return (int)$pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error en mdlCrearDependencia: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza los datos de una dependencia institucional existente.
     * 
     * @param int $idDependencia
     * @param array $datos
     * @return bool
     */
    public static function mdlEditarDependencia(int $idDependencia, array $datos): bool {
        $pdo = Conexion::conectar();

        try {
            $stmt = $pdo->prepare(
                "UPDATE dependencias
                 SET nombre = :nombre,
                     descripcion = :descripcion,
                     estado = :estado,
                     dependencia = :dependencia,
                     codigo = :codigo,
                     tipo = :tipo
                 WHERE id_dependencia = :id_dependencia"
            );

            $nombre      = $datos["nombre"] ?? "";
            $descripcion = $datos["descripcion"] !== "" ? $datos["descripcion"] : ("Unidad organizacional: " . $nombre);
            $estado      = $datos["estado"] ?? "Activo";
            $dependencia = $datos["dependencia"] ?? $nombre;
            $codigo      = $datos["codigo"] ?? "";
            $tipo        = $datos["tipo"] ?? "operativa";

            $stmt->bindParam(":nombre",         $nombre, PDO::PARAM_STR);
            $stmt->bindParam(":descripcion",    $descripcion, PDO::PARAM_STR);
            $stmt->bindParam(":estado",         $estado, PDO::PARAM_STR);
            $stmt->bindParam(":dependencia",    $dependencia, PDO::PARAM_STR);
            $stmt->bindParam(":codigo",         $codigo, PDO::PARAM_STR);
            $stmt->bindParam(":tipo",           $tipo, PDO::PARAM_STR);
            $stmt->bindParam(":id_dependencia", $idDependencia, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en mdlEditarDependencia: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza atómicamente el estado de una dependencia (Activo / Inactivo).
     * 
     * @param int $idDependencia
     * @param string $estado
     * @return bool
     */
    public static function mdlActualizarEstado(int $idDependencia, string $estado): bool {
        try {
            $stmt = Conexion::conectar()->prepare("UPDATE dependencias SET estado = :estado WHERE id_dependencia = :id_dependencia");
            $stmt->bindParam(":estado",         $estado, PDO::PARAM_STR);
            $stmt->bindParam(":id_dependencia", $idDependencia, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en ModeloDependencias::mdlActualizarEstado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina una dependencia validando integridad referencial con usuarios y radicados.
     * 
     * @param int $idDependencia
     * @return string "ok", "en_uso" o "error"
     */
    public static function mdlEliminarDependencia(int $idDependencia): string {
        $pdo = Conexion::conectar();

        try {
            $stmtUsers = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE id_dependencia = :id_dependencia");
            $stmtUsers->bindParam(":id_dependencia", $idDependencia, PDO::PARAM_INT);
            $stmtUsers->execute();
            if ((int)$stmtUsers->fetchColumn() > 0) {
                return "en_uso";
            }

            $stmtRads = $pdo->prepare("SELECT COUNT(*) FROM radicados WHERE id_dependencia = :id_dependencia");
            $stmtRads->bindParam(":id_dependencia", $idDependencia, PDO::PARAM_INT);
            $stmtRads->execute();
            if ((int)$stmtRads->fetchColumn() > 0) {
                return "en_uso";
            }

            $stmtDel = $pdo->prepare("DELETE FROM dependencias WHERE id_dependencia = :id_dependencia");
            $stmtDel->bindParam(":id_dependencia", $idDependencia, PDO::PARAM_INT);
            $stmtDel->execute();

            return "ok";
        } catch (PDOException $e) {
            error_log("Error en mdlEliminarDependencia: " . $e->getMessage());
            return "error";
        }
    }
}
