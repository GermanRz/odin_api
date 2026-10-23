<?php

require_once __DIR__ . "/../modelos/usuarios.modelo.php";
require_once __DIR__ . "/../modelos/roles.modelo.php";
require_once __DIR__ . "/roles.controlador.php";
require_once __DIR__ . "/../api/helpers/respuesta.helper.php";

/**
 * ControladorUsuarios
 * 
 * Controlador de la API RESTful para el módulo de usuarios y autenticación RBAC del Sistema ODIN.
 * Cumple con RF-01, RF-N09, RF-N10, RF-N13, RF-N15 y RF-44.
 */
class ControladorUsuarios {

    /*=============================================
    API REST: LISTAR USUARIOS (GET /api/usuarios)
    =============================================*/
    public static function ctrListar(): void {
        try {
            $usuarios = ModeloUsuarios::mdlMostrarUsuarios();
            RespuestaHelper::exito($usuarios, "Listado de usuarios obtenido correctamente");
        } catch (Exception $e) {
            error_log("Error en ControladorUsuarios::ctrListar: " . $e->getMessage());
            RespuestaHelper::error("Error interno al obtener el listado de usuarios", 500);
        }
    }

    /*=============================================
    API REST: OBTENER USUARIO Y PERMISOS (GET /api/usuarios/{id})
    =============================================*/
    public static function ctrObtener(int $id): void {
        try {
            $usuario = ModeloUsuarios::mdlMostrarUsuario("id_usuario", $id);
            if ($usuario) {
                // Validar si la contraseña actual sigue siendo igual al documento de identificación
                $esClavePorDefecto = password_verify((string)$usuario["num_identificacion"], (string)$usuario["password"]);
                $usuario["clavePorDefecto"] = $esClavePorDefecto;

                // Seguridad: jamás exponer el hash de la contraseña en respuestas de la API
                unset($usuario["password"]);
                $idRol = (int)($usuario["id_rol"] ?? 0);
                $usuario["permisos_rol"] = $idRol > 0 ? ControladorRoles::ctrMostrarPermisosPorRol($idRol) : [];
                RespuestaHelper::exito($usuario, "Usuario encontrado");
            } else {
                RespuestaHelper::error("Usuario no encontrado", 404);
            }
        } catch (Exception $e) {
            error_log("Error en ControladorUsuarios::ctrObtener: " . $e->getMessage());
            RespuestaHelper::error("Error interno al obtener los datos del usuario", 500);
        }
    }

    /*=============================================
    API REST: CREAR USUARIO (POST /api/usuarios)
    =============================================*/
    public static function ctrCrear(array $datos): void {
        $nombre               = trim($datos["nombre"] ?? "");
        $numeroIdentificacion = trim($datos["numeroIdentificacion"] ?? $datos["numero_identificacion"] ?? "");
        $correo               = trim(strtolower($datos["correo"] ?? ""));
        $tipoIdentificacion   = trim($datos["tipoIdentificacion"] ?? $datos["tipo_identificacion"] ?? "CC");
        $idRol                = (int)($datos["idRol"] ?? $datos["id_rol"] ?? 0);
        $idDependencia        = (int)($datos["idDependencia"] ?? $datos["id_dependencia"] ?? 0);
        $direccion            = trim($datos["direccion"] ?? "");
        $telefono             = trim($datos["telefono"] ?? "");
        $passwordPlano        = trim($datos["password"] ?? "");

        if (empty($nombre) || empty($numeroIdentificacion) || empty($correo)) {
            RespuestaHelper::error("Los campos Nombre, Identificación y Correo electrónico son obligatorios", 400);
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            RespuestaHelper::error("El correo electrónico no posee un formato válido", 400);
        }

        $existeDoc = ModeloUsuarios::mdlMostrarUsuario("num_identificacion", $numeroIdentificacion);
        if ($existeDoc) {
            RespuestaHelper::error("El número de identificación ya se encuentra registrado en el sistema", 409);
        }

        $existeCorreo = ModeloUsuarios::mdlMostrarUsuario("correo", $correo);
        if ($existeCorreo) {
            RespuestaHelper::error("El correo electrónico ya se encuentra registrado para otro usuario", 409);
        }

        $claveFinal = $passwordPlano !== "" ? $passwordPlano : $numeroIdentificacion;

        $datosCrear = [
            "tipoIdentificacion"   => $tipoIdentificacion,
            "numeroIdentificacion" => $numeroIdentificacion,
            "nombre"               => $nombre,
            "correo"               => $correo,
            "idRol"                => $idRol,
            "idDependencia"        => $idDependencia,
            "direccion"            => $direccion,
            "telefono"             => $telefono,
            "estado"               => "Activo",
            "password"             => password_hash($claveFinal, PASSWORD_DEFAULT)
        ];

        $resultado = ModeloUsuarios::mdlCrearUsuario($datosCrear);

        if ($resultado) {
            RespuestaHelper::exito([], "Usuario creado exitosamente", 201);
        } else {
            RespuestaHelper::error("No fue posible registrar el usuario en el sistema", 500);
        }
    }

    /*=============================================
    API REST: ACTUALIZAR USUARIO (PUT /api/usuarios/{id})
    =============================================*/
    public static function ctrActualizar(int $id, array $datos): void {
        $nombre        = trim($datos["nombre"] ?? "");
        $correo        = trim(strtolower($datos["correo"] ?? ""));
        $idRol         = (int)($datos["idRol"] ?? $datos["id_rol"] ?? 0);
        $idDependencia = (int)($datos["idDependencia"] ?? $datos["id_dependencia"] ?? 0);
        $direccion     = trim($datos["direccion"] ?? "");
        $telefono      = trim($datos["telefono"] ?? "");
        $nuevaPassword = trim($datos["password"] ?? "");

        if (empty($nombre) || empty($correo)) {
            RespuestaHelper::error("El nombre y el correo electrónico son obligatorios para actualizar", 400);
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            RespuestaHelper::error("El correo electrónico no posee un formato válido", 400);
        }

        $usuarioExistente = ModeloUsuarios::mdlMostrarUsuario("id_usuario", $id);
        if (!$usuarioExistente) {
            RespuestaHelper::error("El usuario a actualizar no existe", 404);
        }

        $usuarioConCorreo = ModeloUsuarios::mdlMostrarUsuario("correo", $correo);
        if ($usuarioConCorreo && (int)$usuarioConCorreo["id_usuario"] !== $id) {
            RespuestaHelper::error("El correo electrónico ya está en uso por otro usuario", 409);
        }

        // Si no se proporcionó rol o dependencia válida (ej: actualización desde perfil propio), preservar los existentes
        if ($idRol <= 0 && isset($usuarioExistente["id_rol"])) {
            $idRol = (int)$usuarioExistente["id_rol"];
        }
        if ($idDependencia <= 0 && isset($usuarioExistente["id_dependencia"])) {
            $idDependencia = (int)$usuarioExistente["id_dependencia"];
        }

        $datosActualizar = [
            "nombre"        => $nombre,
            "correo"        => $correo,
            "idRol"         => $idRol,
            "idDependencia" => $idDependencia,
            "direccion"     => $direccion,
            "telefono"      => $telefono
        ];

        // Manejo opcional de cambio de contraseña
        $nuevaClave = !empty($datos["password"]) ? trim((string)$datos["password"]) : $nuevaPassword;
        if ($nuevaClave !== "") {
            if (strlen($nuevaClave) < 4) {
                RespuestaHelper::error("La nueva contraseña debe tener al menos 4 caracteres", 400);
            }
            $datosActualizar["password"] = password_hash($nuevaClave, PASSWORD_DEFAULT);
        }

        $resultado = ModeloUsuarios::mdlEditarUsuario($id, $datosActualizar);

        if ($resultado) {
            // Si el usuario actualizado es el mismo que tiene la sesión activa, sincronizar la sesión
            if (session_status() === PHP_SESSION_ACTIVE) {
                if ((isset($_SESSION["id"]) && (int)$_SESSION["id"] === $id) ||
                    (isset($_SESSION["id_usuario"]) && (int)$_SESSION["id_usuario"] === $id)) {
                    $_SESSION["nombre"] = $nombre;
                    $_SESSION["correo"] = $correo;
                    $_SESSION["rol"] = $idRol;
                    ControladorRoles::ctrCargarPermisosSesion($idRol);
                    if (!empty($datos["password"])) {
                        $_SESSION["clavePorDefecto"] = ((string)$usuarioExistente["num_identificacion"] === trim((string)$datos["password"]));
                    }
                }
            }
            RespuestaHelper::exito([
                "clavePorDefecto" => (!empty($datos["password"]) ? ((string)$usuarioExistente["num_identificacion"] === trim((string)$datos["password"])) : false)
            ], "Usuario actualizado correctamente", 200);
        } else {
            RespuestaHelper::error("No fue posible actualizar la información del usuario", 500);
        }
    }

    /*=============================================
    API REST: CAMBIAR ESTADO (PATCH /api/usuarios/{id}/estado)
    =============================================*/
    public static function ctrCambiarEstado(int $id, string $nuevoEstado): void {
        if ($nuevoEstado !== "Activo" && $nuevoEstado !== "Inactivo") {
            RespuestaHelper::error("El estado especificado no es válido (debe ser Activo o Inactivo)", 400);
        }

        if ($id === 1 && $nuevoEstado !== "Activo") {
            RespuestaHelper::error("El usuario Administrador principal no puede ser desactivado", 400);
        }

        $usuarioExistente = ModeloUsuarios::mdlMostrarUsuario("id_usuario", $id);
        if (!$usuarioExistente) {
            RespuestaHelper::error("El usuario a modificar no existe", 404);
        }

        $resultado = ModeloUsuarios::mdlActualizarEstado($id, $nuevoEstado);

        if ($resultado) {
            RespuestaHelper::exito(["nuevoEstado" => $nuevoEstado], "Estado del usuario actualizado correctamente");
        } else {
            RespuestaHelper::error("No fue posible modificar el estado del usuario", 500);
        }
    }

    /*=============================================
    API REST: ELIMINAR USUARIO (DELETE /api/usuarios/{id})
    =============================================*/
    public static function ctrEliminar(int $id): void {
        if ($id === 1) {
            RespuestaHelper::error("El usuario Administrador principal del sistema no puede eliminarse", 400);
        }

        if (isset($_SESSION["id"]) && (int)$_SESSION["id"] === $id) {
            RespuestaHelper::error("No puede eliminar su propia cuenta con sesión activa", 400);
        }

        $usuarioExistente = ModeloUsuarios::mdlMostrarUsuario("id_usuario", $id);
        if (!$usuarioExistente) {
            RespuestaHelper::error("El usuario que intenta eliminar no existe", 404);
        }

        $resultado = ModeloUsuarios::mdlEliminarUsuario($id);

        if ($resultado === "ok") {
            RespuestaHelper::exito([], "El usuario fue eliminado correctamente", 200);
        } elseif ($resultado === "en_uso") {
            RespuestaHelper::error("No se puede eliminar este usuario porque tiene radicados asociados. Desactívelo en su lugar.", 409);
        } else {
            RespuestaHelper::error("Error interno al intentar eliminar el usuario", 500);
        }
    }

    /*=============================================
    API REST: MÉTRICAS Y ESTADÍSTICAS (GET /api/usuarios/stats)
    =============================================*/
    public static function ctrEstadisticas(): void {
        try {
            $total = ModeloUsuarios::mdlContarUsuarios();
            $activos = ModeloUsuarios::mdlContarUsuariosActivos();
            $inactivos = ModeloUsuarios::mdlContarUsuariosInactivos();
            $statsRoles = ModeloRoles::mdlEstadisticasRoles();

            RespuestaHelper::exito([
                "total"        => (int)($total["total"] ?? 0),
                "activos"      => (int)($activos["total"] ?? 0),
                "inactivos"    => (int)($inactivos["total"] ?? 0),
                "rolesActivos" => (int)($statsRoles["activos"] ?? 0)
            ], "Estadísticas de usuarios obtenidas correctamente");
        } catch (Exception $e) {
            error_log("Error en ControladorUsuarios::ctrEstadisticas: " . $e->getMessage());
            RespuestaHelper::error("Error al calcular estadísticas", 500);
        }
    }

    /*=============================================
    AUTENTICACIÓN FLEXIBLE Y VALIDACIÓN RBAC (RF-01 / RF-N09 / RF-N13)
    =============================================*/
    public static function ctrIngresoUsuario(): void {
        if (isset($_POST["ingUsuario"])) {
            $usuarioInput = trim($_POST["ingUsuario"]);

            if (preg_match('/^[a-zA-Z0-9_@.\-\sáéíóúÁÉÍÓÚñÑ()\/]+$/u', $usuarioInput)) {
                $respuesta = ModeloUsuarios::mdlBuscarUsuarioLoginFlexible($usuarioInput);

                if ($respuesta) {
                    if (password_verify($_POST["ingPassword"], $respuesta["password"])) {
                        if ($respuesta["estado"] === "Activo") {
                            $_SESSION["iniciarSesion"] = "ok";
                            $_SESSION["id"] = $respuesta["id_usuario"];
                            $_SESSION["nombre"] = $respuesta["nombre"];
                            $_SESSION["numIdentificacion"] = $respuesta["num_identificacion"];
                            $_SESSION["num_identificacion"] = $respuesta["num_identificacion"];
                            $_SESSION["correo"] = $respuesta["correo"];
                            $_SESSION["rol"] = $respuesta["id_rol"];
                            $_SESSION["estado"] = $respuesta["estado"];
                            $_SESSION["clavePorDefecto"] = password_verify((string)$respuesta["num_identificacion"], (string)$respuesta["password"]);
                            ControladorRoles::ctrCargarPermisosSesion((int)$respuesta["id_rol"]);

                            echo '<script>window.location = "inicio";</script>';
                        } else {
                            echo '<div class="alert alert-warning mt-3 text-center" role="alert">
                                    <i class="icon fas fa-exclamation-triangle mr-1"></i> El usuario se encuentra desactivado.
                                  </div>';
                            return;
                        }

                        // RF-N09: Impedir el inicio de sesión de usuarios cuyo rol esté inactivo
                        if (isset($respuesta["rol_estado"]) && $respuesta["rol_estado"] !== "Activo") {
                            $nombreRol = htmlspecialchars($respuesta["rol_nombre"] ?? "Asignado");
                            echo '<div class="alert alert-warning mt-3 text-center" role="alert">
                                    <i class="icon fas fa-user-lock mr-1"></i> El rol asignado a su cuenta (' . $nombreRol . ') se encuentra inactivo.
                                  </div>';
                            return;
                        }

                        $_SESSION["iniciarSesion"]      = "ok";
                        $_SESSION["id"]                 = $respuesta["id_usuario"];
                        $_SESSION["nombre"]             = $respuesta["nombre"];
                        $_SESSION["numIdentificacion"]  = $respuesta["num_identificacion"];
                        $_SESSION["num_identificacion"] = $respuesta["num_identificacion"];
                        $_SESSION["correo"]             = $respuesta["correo"];
                        $_SESSION["rol"]                = (int)$respuesta["id_rol"];
                        $_SESSION["id_dependencia"]     = (int)$respuesta["id_dependencia"];
                        $_SESSION["estado"]             = $respuesta["estado"];

                        // RF-N13: Cargar permisos granulares del rol en sesión
                        ControladorRoles::ctrCargarPermisosSesion((int)$respuesta["id_rol"]);

                        echo '<script>window.location = "inicio";</script>';
                    } else {
                        echo '<div class="alert alert-danger mt-3 text-center" role="alert">
                                <i class="icon fas fa-ban mr-1"></i> Error al ingresar, usuario o contraseña incorrectos.
                              </div>';
                    }
                } else {
                    echo '<div class="alert alert-danger mt-3 text-center" role="alert">
                            <i class="icon fas fa-ban mr-1"></i> Error al ingresar, usuario o contraseña incorrectos.
                          </div>';
                }
            } else {
                echo '<div class="alert alert-danger mt-3 text-center" role="alert">
                        <i class="icon fas fa-ban mr-1"></i> Caracteres no permitidos en el usuario.
                      </div>';
            }
        }
    }

    /*=============================================
    MÉTODOS HEREDADOS (RETROCOMPATIBILIDAD)
    =============================================*/
    public static function ctrMostrarUsuarios(): array {
        return ModeloUsuarios::mdlMostrarUsuarios();
    }

    public static function ctrMostrarUsuario($campo, $valor) {
        return ModeloUsuarios::mdlMostrarUsuario($campo, $valor);
    }

    public static function ctrActivarUsuario($estadoUsuario, $idUsuario): bool {
        return ModeloUsuarios::mdlActualizarEstado($idUsuario, $estadoUsuario);
    }

    public static function ctrContarUsuarios(): array {
        return ModeloUsuarios::mdlContarUsuarios();
    }

    public static function ctrContarUsuariosActivos(): array {
        return ModeloUsuarios::mdlContarUsuariosActivos();
    }
}
