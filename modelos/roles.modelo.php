<?php

require_once "conexion.php";

/**
 * ModeloRoles
 * 
 * Capa de persistencia PDO para la gestión de roles y matriz de permisos granulares (RBAC).
 * Utiliza exclusivamente las columnas originales de la base de datos odin_api_db:
 * - roles (id_rol, estado, nombre, rol)
 * - permisos (id_permiso, nombre, descripcion, modulo, accion)
 * - rol_permisos (id_rol, nivel_acceso, nombre, estado, descripcion, id_permiso)
 */
class ModeloRoles {

    /**
     * Lista todos los roles registrados con conteo de permisos y usuarios asociados,
     * o consulta un rol específico por campo.
     * 
     * @param string|null $tabla Nombre de la tabla (retrocompatibilidad).
     * @param string|null $campo Campo de filtro opcional (id_rol, rol, nombre, estado).
     * @param mixed $valor Valor del filtro.
     * @return array|false
     */
    public static function mdlMostrarRoles($tabla = "roles", $campo = null, $valor = null) {
        $pdo = Conexion::conectar();

        if ($campo !== null) {
            $camposPermitidos = ["id_rol", "rol", "nombre", "estado"];
            if (!in_array($campo, $camposPermitidos, true)) {
                $campo = "id_rol";
            }

            try {
                $sql = "SELECT r.id_rol,
                               r.rol,
                               r.nombre,
                               r.estado,
                               (SELECT COUNT(*) FROM rol_permisos rp WHERE rp.id_rol = r.id_rol) AS total_permisos,
                               (SELECT COUNT(*) FROM usuarios u WHERE u.id_rol = r.id_rol) AS total_usuarios
                        FROM roles r
                        WHERE r.{$campo} = :valor
                        LIMIT 1";
                $stmt = $pdo->prepare($sql);
                if ($campo === "id_rol") {
                    $idVal = (int)$valor;
                    $stmt->bindParam(":valor", $idVal, PDO::PARAM_INT);
                } else {
                    $strVal = (string)$valor;
                    $stmt->bindParam(":valor", $strVal, PDO::PARAM_STR);
                }
                $stmt->execute();
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log("Error en mdlMostrarRoles ({$campo}): " . $e->getMessage());
                return false;
            }
        }

        try {
            $sql = "SELECT r.id_rol,
                           r.rol,
                           r.nombre,
                           r.estado,
                           (SELECT COUNT(*) FROM rol_permisos rp WHERE rp.id_rol = r.id_rol) AS total_permisos,
                           (SELECT COUNT(*) FROM usuarios u WHERE u.id_rol = r.id_rol) AS total_usuarios
                    FROM roles r
                    ORDER BY r.id_rol ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlMostrarRoles (listado): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene las métricas generales del módulo de roles y permisos (KPIs).
     * 
     * @return array
     */
    public static function mdlEstadisticasRoles(): array {
        $pdo = Conexion::conectar();
        try {
            $totalRoles = (int)$pdo->query("SELECT COUNT(*) FROM roles")->fetchColumn();
            $rolesActivos = (int)$pdo->query("SELECT COUNT(*) FROM roles WHERE estado = 'Activo'")->fetchColumn();
            $rolesInactivos = (int)$pdo->query("SELECT COUNT(*) FROM roles WHERE estado <> 'Activo'")->fetchColumn();
            $totalPermisos = (int)$pdo->query("SELECT COUNT(*) FROM permisos")->fetchColumn();

            return [
                "total"         => $totalRoles,
                "activos"       => $rolesActivos,
                "inactivos"     => $rolesInactivos,
                "totalPermisos" => $totalPermisos
            ];
        } catch (PDOException $e) {
            error_log("Error en mdlEstadisticasRoles: " . $e->getMessage());
            return [
                "total"         => 0,
                "activos"       => 0,
                "inactivos"     => 0,
                "totalPermisos" => 0
            ];
        }
    }

    /**
     * Obtiene el catálogo completo de permisos granulares (RBAC).
     * 
     * @return array
     */
    public static function mdlMostrarPermisos(): array {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT id_permiso, nombre, descripcion, modulo, accion
                 FROM permisos
                 ORDER BY modulo ASC, id_permiso ASC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlMostrarPermisos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los permisos asignados a un rol específico desde rol_permisos.
     * 
     * @param int $idRol
     * @return array
     */
    public static function mdlMostrarPermisosPorRol(int $idRol): array {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT p.id_permiso,
                        p.nombre,
                        p.descripcion,
                        p.modulo,
                        p.accion
                 FROM permisos p
                 INNER JOIN rol_permisos rp ON rp.id_permiso = p.id_permiso
                 WHERE rp.id_rol = :id_rol
                 ORDER BY p.modulo ASC, p.id_permiso ASC"
            );
            $stmt->bindParam(":id_rol", $idRol, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlMostrarPermisosPorRol: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Registra un nuevo rol y su matriz de permisos granulares de forma transaccional
     * usando únicamente las columnas originales (rol, nombre, estado).
     * 
     * @param array $datos
     * @param array $permisosIds
     * @return int|false ID del rol creado o false en caso de error.
     */
    public static function mdlCrearRol(array $datos, array $permisosIds = []) {
        $pdo = Conexion::conectar();

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "INSERT INTO roles (rol, nombre, estado)
                 VALUES (:rol, :nombre, :estado)"
            );

            $rolCodigo = $datos["rol"] ?? strtolower($datos["nombre"] ?? "");
            $nombre    = $datos["nombre"] ?? "";
            $estado    = $datos["estado"] ?? "Activo";

            $stmt->bindParam(":rol",    $rolCodigo, PDO::PARAM_STR);
            $stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            $stmt->execute();

            $idRol = (int)$pdo->lastInsertId();

            if ($idRol > 0 && !empty($permisosIds)) {
                self::sincronizarPermisosRol($pdo, $idRol, $permisosIds);
            }

            $pdo->commit();
            return $idRol;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en mdlCrearRol: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza los datos de un rol y reemplaza su matriz de permisos granulares
     * usando únicamente las columnas originales (rol, nombre, estado).
     * 
     * @param int $idRol
     * @param array $datos
     * @param array $permisosIds
     * @return bool
     */
    public static function mdlEditarRol(int $idRol, array $datos, array $permisosIds = []): bool {
        $pdo = Conexion::conectar();

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "UPDATE roles
                 SET rol = :rol,
                     nombre = :nombre,
                     estado = :estado
                 WHERE id_rol = :id_rol"
            );

            $rolCodigo = $datos["rol"] ?? strtolower($datos["nombre"] ?? "");
            $nombre    = $datos["nombre"] ?? "";
            $estado    = $datos["estado"] ?? "Activo";

            $stmt->bindParam(":rol",    $rolCodigo, PDO::PARAM_STR);
            $stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            $stmt->bindParam(":id_rol", $idRol, PDO::PARAM_INT);
            $stmt->execute();

            $stmtDel = $pdo->prepare("DELETE FROM rol_permisos WHERE id_rol = :id_rol");
            $stmtDel->bindParam(":id_rol", $idRol, PDO::PARAM_INT);
            $stmtDel->execute();

            if (!empty($permisosIds)) {
                self::sincronizarPermisosRol($pdo, $idRol, $permisosIds);
            }

            $pdo->commit();
            return true;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en mdlEditarRol: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Inserta las asociaciones en la tabla rol_permisos respetando su esquema exacto.
     */
    private static function sincronizarPermisosRol(PDO $pdo, int $idRol, array $permisosIds): void {
        $stmtInfo = $pdo->prepare("SELECT id_permiso, nombre, descripcion FROM permisos WHERE id_permiso = :id_permiso LIMIT 1");
        $stmtInsert = $pdo->prepare(
            "INSERT INTO rol_permisos (id_rol, id_permiso, nivel_acceso, nombre, estado, descripcion)
             VALUES (:id_rol, :id_permiso, :nivel_acceso, :nombre, 'Activo', :descripcion)"
        );

        $nivelAcceso = ($idRol === 1) ? 10 : 5;
        $vistos = [];

        foreach ($permisosIds as $rawId) {
            $idPermiso = (int)$rawId;
            if ($idPermiso <= 0 || isset($vistos[$idPermiso])) {
                continue;
            }
            $vistos[$idPermiso] = true;

            $stmtInfo->bindParam(":id_permiso", $idPermiso, PDO::PARAM_INT);
            $stmtInfo->execute();
            $perm = $stmtInfo->fetch(PDO::FETCH_ASSOC);

            if ($perm) {
                $nombrePerm = $perm["nombre"] ?? "";
                $descPerm   = $perm["descripcion"] ?? "";

                $stmtInsert->bindParam(":id_rol",       $idRol, PDO::PARAM_INT);
                $stmtInsert->bindParam(":id_permiso",   $idPermiso, PDO::PARAM_INT);
                $stmtInsert->bindParam(":nivel_acceso", $nivelAcceso, PDO::PARAM_INT);
                $stmtInsert->bindParam(":nombre",       $nombrePerm, PDO::PARAM_STR);
                $stmtInsert->bindParam(":descripcion",  $descPerm, PDO::PARAM_STR);
                $stmtInsert->execute();
            }
        }
    }

    /**
     * Actualiza atómicamente el estado de un rol (Activo / Inactivo).
     */
    public static function mdlActualizarEstado(int $idRol, string $estado): bool {
        try {
            $stmt = Conexion::conectar()->prepare("UPDATE roles SET estado = :estado WHERE id_rol = :id_rol");
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            $stmt->bindParam(":id_rol", $idRol, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en ModeloRoles::mdlActualizarEstado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un rol si no tiene usuarios asociados.
     */
    public static function mdlEliminarRol(int $idRol): string {
        $pdo = Conexion::conectar();

        try {
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE id_rol = :id_rol");
            $stmtCheck->bindParam(":id_rol", $idRol, PDO::PARAM_INT);
            $stmtCheck->execute();
            if ((int)$stmtCheck->fetchColumn() > 0) {
                return "en_uso";
            }

            $pdo->beginTransaction();

            $stmtPerm = $pdo->prepare("DELETE FROM rol_permisos WHERE id_rol = :id_rol");
            $stmtPerm->bindParam(":id_rol", $idRol, PDO::PARAM_INT);
            $stmtPerm->execute();

            $stmtRol = $pdo->prepare("DELETE FROM roles WHERE id_rol = :id_rol");
            $stmtRol->bindParam(":id_rol", $idRol, PDO::PARAM_INT);
            $stmtRol->execute();

            $pdo->commit();
            return "ok";
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Error en mdlEliminarRol: " . $e->getMessage());
            return "error";
        }
    }
}
