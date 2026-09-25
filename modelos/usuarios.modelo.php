<?php

require_once "conexion.php";

/**
 * ModeloUsuarios
 * 
 * Capa de persistencia para el módulo de usuarios.
 * Ejecuta consultas seguras mediante PDO con sentencias preparadas y bindParam tipificado.
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
            $stmt = Conexion::conectar()->prepare("SELECT u.id_usuario,
                                                          u.nombre,
                                                          u.num_identificacion,
                                                          u.correo,
                                                          r.nombre AS rol,
                                                          d.nombre AS dependencia,
                                                          u.estado
                                                   FROM usuarios u
                                                   LEFT JOIN roles r ON r.id_rol = u.id_rol
                                                   LEFT JOIN dependencias d ON d.id_dependencia = u.id_dependencia
                                                   ORDER BY u.id_usuario DESC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlMostrarUsuarios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca un usuario por un campo específico (id_usuario, num_identificacion, correo).
     * Soporta firma moderna (campo, valor) y firma heredada (tabla, campo, valor).
     * 
     * @param mixed $arg1 Campo o nombre de tabla.
     * @param mixed $arg2 Valor o campo.
     * @param mixed $arg3 Valor (cuando se usa firma heredada de 3 argumentos).
     * @return array|false Datos del usuario o false si no existe.
     */
    public static function mdlMostrarUsuario($arg1, $arg2 = null, $arg3 = null) {
        $campo = ($arg3 !== null) ? (string)$arg2 : (string)$arg1;
        $valor = ($arg3 !== null) ? $arg3 : $arg2;

        // Lista blanca de campos permitidos para prevenir inyección SQL en identificadores
        $camposPermitidos = ["id_usuario", "num_identificacion", "correo"];
        if (!in_array($campo, $camposPermitidos, true)) {
            $campo = "id_usuario";
        }

        try {
            $stmt = Conexion::conectar()->prepare("SELECT id_usuario, tipo_identificacion, num_identificacion,
                                                          nombre, correo, id_rol, id_dependencia, estado,
                                                          direccion, telefono, password
                                                   FROM usuarios
                                                   WHERE {$campo} = :valor LIMIT 1");
            $stmt->bindParam(":valor", $valor, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en mdlMostrarUsuario ({$campo}): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Registra un nuevo usuario en la base de datos.
     * Soporta firma canónica ($datos) y firma heredada ($tabla, $datos).
     * 
     * @param mixed $arg1 Datos o nombre de tabla.
     * @param array|null $arg2 Datos cuando se envía tabla en primer argumento.
     * @return bool True si se insertó con éxito, False en caso contrario.
     */
    public static function mdlCrearUsuario($arg1, $arg2 = null): bool {
        $datos = is_array($arg1) ? $arg1 : (array)$arg2;

        try {
            $stmt = Conexion::conectar()->prepare("INSERT INTO usuarios (tipo_identificacion, num_identificacion,
                                                                         nombre, correo, id_rol, id_dependencia,
                                                                         estado, password, direccion, telefono)
                                                   VALUES (:tipo_identificacion, :num_identificacion,
                                                           :nombre, :correo, :id_rol, :id_dependencia,
                                                           :estado, :password, :direccion, :telefono)");

            $tipoIdentificacion = $datos["tipoIdentificacion"] ?? $datos["tipo_identificacion"] ?? $datos["tipo_documento"] ?? "CC";
            $numIdentificacion  = $datos["numeroIdentificacion"] ?? $datos["num_identificacion"] ?? $datos["numero_identificacion"] ?? "";
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
     * Actualiza la información de un usuario existente.
     * Soporta firma canónica (int $id, array $datos) y heredada ($tabla, $datos).
     * 
     * @param mixed $arg1 ID del usuario o nombre de tabla.
     * @param array $arg2 Datos a actualizar.
     * @return bool True si se actualizó con éxito, False en caso contrario.
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
            $stmt = Conexion::conectar()->prepare("UPDATE usuarios
                                                   SET nombre = :nombre,
                                                       correo = :correo,
                                                       id_rol = :id_rol,
                                                       id_dependencia = :id_dependencia,
                                                       direccion = :direccion,
                                                       telefono = :telefono
                                                   WHERE id_usuario = :id");

            $nombre        = $datos["nombre"] ?? "";
            $correo        = $datos["correo"] ?? "";
            $idRol         = (int)($datos["idRol"] ?? $datos["id_rol"] ?? $datos["rol"] ?? 0);
            $idDependencia = (int)($datos["idDependencia"] ?? $datos["id_dependencia"] ?? $datos["dependencia"] ?? 0);
            $direccion     = $datos["direccion"] ?? "";
            $telefono      = $datos["telefono"] ?? "";

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
     * 
     * @param int $id ID del usuario.
     * @param string $estado Nuevo estado.
     * @return bool True si tuvo éxito, False en caso de error.
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