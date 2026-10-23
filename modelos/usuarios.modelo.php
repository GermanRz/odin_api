<?php

require_once "conexion.php";

/**
 * ModeloUsuarios
 * 
 * Capa de persistencia para el módulo de usuarios.
 * Ejecuta consultas seguras mediante PDO con sentencias preparadas y bindParam tipificado.
 * Cumple con RF-01, RF-N09, RF-N10, RF-N15 y RF-44.
 */
class ModeloUsuarios {

    /**
     * Consulta el listado completo de usuarios con roles y dependencias asociados.
     * 
     * @param string|null $tabla Parámetro opcional para retrocompatibilidad.
     * @return array Lista de usuarios.
     */
    public static function mdlMostrarUsuarios($tabla = null): array {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT u.id_usuario,
                        u.tipo_identificacion,
                        u.num_identificacion,
                        u.nombre,
                        u.correo,
                        u.direccion,
                        u.telefono,
                        u.id_rol,
                        u.id_dependencia,
                        r.nombre AS rol,
                        r.rol AS rol_codigo,
                        r.estado AS rol_estado,
                        d.nombre AS dependencia,
                        d.codigo AS dependencia_codigo,
                        u.estado
                 FROM usuarios u
                 LEFT JOIN roles r ON r.id_rol = u.id_rol
                 LEFT JOIN dependencias d ON d.id_dependencia = u.id_dependencia
                 ORDER BY u.id_usuario DESC"
            );
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlMostrarUsuarios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca un usuario por un campo específico (id_usuario, num_identificacion, correo, nombre).
     * Soporta firma moderna (campo, valor) y firma heredada (tabla, campo, valor).
     */
    public static function mdlMostrarUsuario($arg1, $arg2 = null, $arg3 = null) {
        $campo = ($arg3 !== null) ? (string)$arg2 : (string)$arg1;
        $valor = ($arg3 !== null) ? $arg3 : $arg2;

        $camposPermitidos = ["id_usuario", "num_identificacion", "correo", "nombre"];
        if (!in_array($campo, $camposPermitidos, true)) {
            $campo = "id_usuario";
        }

        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT u.id_usuario,
                        u.tipo_identificacion,
                        u.num_identificacion,
                        u.nombre,
                        u.correo,
                        u.id_rol,
                        u.id_dependencia,
                        u.estado,
                        u.direccion,
                        u.telefono,
                        u.password,
                        r.nombre AS rol_nombre,
                        r.rol AS rol_codigo,
                        r.estado AS rol_estado,
                        d.nombre AS dependencia_nombre,
                        d.codigo AS dependencia_codigo
                 FROM usuarios u
                 LEFT JOIN roles r ON r.id_rol = u.id_rol
                 LEFT JOIN dependencias d ON d.id_dependencia = u.id_dependencia
                 WHERE u.{$campo} = :valor
                 LIMIT 1"
            );
            if ($campo === "id_usuario") {
                $idVal = (int)$valor;
                $stmt->bindParam(":valor", $idVal, PDO::PARAM_INT);
            } else {
                $strVal = (string)$valor;
                $stmt->bindParam(":valor", $strVal, PDO::PARAM_STR);
            }
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlMostrarUsuario ({$campo}): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca un usuario para inicio de sesión flexible (RF-01: correo, identificación o nombre)
     * trayendo también el estado del rol asignado (RF-N09).
     * 
     * @param string $valor Identificación, correo o nombre del usuario.
     * @return array|false
     */
    public static function mdlBuscarUsuarioLoginFlexible(string $valor) {
        try {
            $stmt = Conexion::conectar()->prepare(
                "SELECT u.id_usuario,
                        u.tipo_identificacion,
                        u.num_identificacion,
                        u.nombre,
                        u.correo,
                        u.id_rol,
                        u.id_dependencia,
                        u.estado,
                        u.direccion,
                        u.telefono,
                        u.password,
                        r.nombre AS rol_nombre,
                        r.rol AS rol_codigo,
                        r.estado AS rol_estado,
                        d.nombre AS dependencia_nombre
                 FROM usuarios u
                 LEFT JOIN roles r ON r.id_rol = u.id_rol
                 LEFT JOIN dependencias d ON d.id_dependencia = u.id_dependencia
                 WHERE u.num_identificacion = :val_id
                    OR LOWER(u.correo) = LOWER(:val_correo)
                    OR LOWER(u.nombre) = LOWER(:val_nombre)
                 LIMIT 1"
            );
            $stmt->bindParam(":val_id",     $valor, PDO::PARAM_STR);
            $stmt->bindParam(":val_correo", $valor, PDO::PARAM_STR);
            $stmt->bindParam(":val_nombre", $valor, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlBuscarUsuarioLoginFlexible: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registra un nuevo usuario en la base de datos.
     */
    public static function mdlCrearUsuario($arg1, $arg2 = null): bool {
        $datos = is_array($arg1) ? $arg1 : (array)$arg2;

        try {
            $stmt = Conexion::conectar()->prepare(
                "INSERT INTO usuarios (tipo_identificacion, num_identificacion,
                                       nombre, correo, id_rol, id_dependencia,
                                       estado, password, direccion, telefono)
                 VALUES (:tipo_identificacion, :num_identificacion,
                         :nombre, :correo, :id_rol, :id_dependencia,
                         :estado, :password, :direccion, :telefono)"
            );

            $tipoIdentificacion = $datos["tipoIdentificacion"] ?? $datos["tipo_identificacion"] ?? $datos["tipo_documento"] ?? "CC";
            $numIdentificacion  = $datos["numeroIdentificacion"] ?? $datos["num_identificacion"] ?? "";
            $nombre             = $datos["nombre"] ?? "";
            $correo             = $datos["correo"] ?? "";
            $idRol              = (int)($datos["idRol"] ?? $datos["id_rol"] ?? $datos["rol"] ?? 0);
            $idDependencia      = (int)($datos["idDependencia"] ?? $datos["id_dependencia"] ?? $datos["dependencia"] ?? 0);
            $estado             = $datos["estado"] ?? "Activo";
            $password           = $datos["password"] ?? "";
            $direccion          = $datos["direccion"] ?? "";
            $telefono           = $datos["telefono"] ?? "";

            $stmt->bindParam(":tipo_identificacion", $tipoIdentificacion, PDO::PARAM_STR);
            $stmt->bindParam(":num_identificacion",  $numIdentificacion, PDO::PARAM_STR);
            $stmt->bindParam(":nombre",              $nombre, PDO::PARAM_STR);
            $stmt->bindParam(":correo",              $correo, PDO::PARAM_STR);
            $stmt->bindParam(":id_rol",              $idRol, PDO::PARAM_INT);
            $stmt->bindParam(":id_dependencia",      $idDependencia, PDO::PARAM_INT);
            $stmt->bindParam(":estado",              $estado, PDO::PARAM_STR);
            $stmt->bindParam(":password",            $password, PDO::PARAM_STR);
            $stmt->bindParam(":direccion",           $direccion, PDO::PARAM_STR);
            $stmt->bindParam(":telefono",            $telefono, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en mdlCrearUsuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza la información de un usuario existente (incluyendo contraseña opcional con BCrypt).
     */
    public static function mdlEditarUsuario($arg1, $arg2 = null): bool {
        if (is_numeric($arg1)) {
            $id = (int)$arg1;
            $datos = (array)$arg2;
        } else {
            $datos = (array)$arg2;
            $id = (int)($datos["id_usuario"] ?? $datos["idUsuario"] ?? 0);
        }

        try {
            $nombre        = $datos["nombre"] ?? "";
            $correo        = $datos["correo"] ?? "";
            $idRol         = (int)($datos["idRol"] ?? $datos["id_rol"] ?? $datos["rol"] ?? 0);
            $idDependencia = (int)($datos["idDependencia"] ?? $datos["id_dependencia"] ?? $datos["dependencia"] ?? 0);
            $direccion     = $datos["direccion"] ?? "";
            $telefono      = $datos["telefono"] ?? "";
            $passwordHash  = $datos["password"] ?? "";

            if (!empty($passwordHash)) {
                $stmt = Conexion::conectar()->prepare(
                    "UPDATE usuarios
                     SET nombre = :nombre,
                         correo = :correo,
                         id_rol = :id_rol,
                         id_dependencia = :id_dependencia,
                         direccion = :direccion,
                         telefono = :telefono,
                         password = :password
                     WHERE id_usuario = :id"
                );
                $stmt->bindParam(":password", $passwordHash, PDO::PARAM_STR);
            } else {
                $stmt = Conexion::conectar()->prepare(
                    "UPDATE usuarios
                     SET nombre = :nombre,
                         correo = :correo,
                         id_rol = :id_rol,
                         id_dependencia = :id_dependencia,
                         direccion = :direccion,
                         telefono = :telefono
                     WHERE id_usuario = :id"
                );
            }

            $stmt->bindParam(":nombre",         $nombre, PDO::PARAM_STR);
            $stmt->bindParam(":correo",         $correo, PDO::PARAM_STR);
            $stmt->bindParam(":id_rol",         $idRol, PDO::PARAM_INT);
            $stmt->bindParam(":id_dependencia", $idDependencia, PDO::PARAM_INT);
            $stmt->bindParam(":direccion",      $direccion, PDO::PARAM_STR);
            $stmt->bindParam(":telefono",       $telefono, PDO::PARAM_STR);
            $stmt->bindParam(":id",             $id, PDO::PARAM_INT);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en mdlEditarUsuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza atómicamente el estado de un usuario (Activo / Inactivo).
     */
    public static function mdlActualizarEstado(int $id, string $estado): bool {
        try {
            $stmt = Conexion::conectar()->prepare("UPDATE usuarios SET estado = :estado WHERE id_usuario = :id");
            $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
            $stmt->bindParam(":id",     $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error en mdlActualizarEstado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un usuario validando que no tenga radicados asociados.
     * 
     * @param int $id ID del usuario.
     * @return string "ok", "en_uso" o "error"
     */
    public static function mdlEliminarUsuario(int $id): string {
        $pdo = Conexion::conectar();

        try {
            $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM radicados WHERE id_usuario = :id_usuario");
            $stmtCheck->bindParam(":id_usuario", $id, PDO::PARAM_INT);
            $stmtCheck->execute();
            if ((int)$stmtCheck->fetchColumn() > 0) {
                return "en_uso";
            }

            $stmtDel = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = :id_usuario");
            $stmtDel->bindParam(":id_usuario", $id, PDO::PARAM_INT);
            $stmtDel->execute();

            return "ok";
        } catch (PDOException $e) {
            error_log("Error en mdlEliminarUsuario: " . $e->getMessage());
            return "error";
        }
    }

    /**
     * Alias heredado para cambiar estado de usuario.
     */
    public static function mdlActivarUsuario(string $estadoUsuario, int $idUsuario): bool {
        return self::mdlActualizarEstado($idUsuario, $estadoUsuario);
    }

    /**
     * Total de usuarios registrados.
     */
    public static function mdlContarUsuarios($tabla = "usuarios"): array {
        try {
            $stmt = Conexion::conectar()->prepare("SELECT COUNT(*) AS total FROM usuarios");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: ["total" => 0];
        } catch (PDOException $e) {
            error_log("Error en mdlContarUsuarios: " . $e->getMessage());
            return ["total" => 0];
        }
    }

    /**
     * Total de usuarios con estado Activo.
     */
    public static function mdlContarUsuariosActivos($tabla = "usuarios"): array {
        try {
            $stmt = Conexion::conectar()->prepare("SELECT COUNT(*) AS total FROM usuarios WHERE estado = 'Activo'");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: ["total" => 0];
        } catch (PDOException $e) {
            error_log("Error en mdlContarUsuariosActivos: " . $e->getMessage());
            return ["total" => 0];
        }
    }

    /**
     * Total de usuarios con estado Inactivo.
     */
    public static function mdlContarUsuariosInactivos($tabla = "usuarios"): array {
        try {
            $stmt = Conexion::conectar()->prepare("SELECT COUNT(*) AS total FROM usuarios WHERE estado = 'Inactivo'");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: ["total" => 0];
        } catch (PDOException $e) {
            error_log("Error en mdlContarUsuariosInactivos: " . $e->getMessage());
            return ["total" => 0];
        }
    }
}
