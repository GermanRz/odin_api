<?php

require_once __DIR__ . "/../modelos/usuarios.modelo.php";
require_once __DIR__ . "/../api/helpers/respuesta.helper.php";

/**
 * ControladorUsuarios
 * 
 * Controlador de la API RESTful para el módulo de usuarios del Sistema ODIN.
 * Procesa peticiones HTTP, valida datos, orquesta la persistencia y emite
 * respuestas estandarizadas mediante RespuestaHelper.
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
    API REST: OBTENER USUARIO (GET /api/usuarios/{id})
    =============================================*/
    public static function ctrObtener(int $id): void {
        try {
            $usuario = ModeloUsuarios::mdlMostrarUsuario("id_usuario", $id);
            if ($usuario) {
                // Seguridad: jamás exponer el hash de la contraseña en respuestas de la API
                unset($usuario["password"]);
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

        // 1. Validar campos obligatorios
        if (empty($nombre) || empty($numeroIdentificacion) || empty($correo)) {
            RespuestaHelper::error("Los campos Nombre, Identificación y Correo electrónico son obligatorios", 400);
        }

        // 2. Validar formato de correo
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            RespuestaHelper::error("El correo electrónico no posee un formato válido", 400);
        }

        // 3. Validar duplicidad de documento
        $existeDoc = ModeloUsuarios::mdlMostrarUsuario("num_identificacion", $numeroIdentificacion);
        if ($existeDoc) {
            RespuestaHelper::error("El número de identificación ya se encuentra registrado en el sistema", 409);
        }

        // 4. Validar duplicidad de correo
        $existeCorreo = ModeloUsuarios::mdlMostrarUsuario("correo", $correo);
        if ($existeCorreo) {
            RespuestaHelper::error("El correo electrónico ya se encuentra registrado para otro usuario", 409);
        }

        // 5. Preparar datos y cifrar contraseña por defecto con número de identificación
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
            "password"             => password_hash($numeroIdentificacion, PASSWORD_DEFAULT)
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

        if (empty($nombre) || empty($correo)) {
            RespuestaHelper::error("El nombre y el correo electrónico son obligatorios para actualizar", 400);
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            RespuestaHelper::error("El correo electrónico no posee un formato válido", 400);
        }

        // Comprobar existencia del usuario
        $usuarioExistente = ModeloUsuarios::mdlMostrarUsuario("id_usuario", $id);
        if (!$usuarioExistente) {
            RespuestaHelper::error("El usuario a actualizar no existe", 404);
        }

        // Comprobar si el correo ya está en uso por otro usuario distinto
        $usuarioConCorreo = ModeloUsuarios::mdlMostrarUsuario("correo", $correo);
        if ($usuarioConCorreo && (int)$usuarioConCorreo["id_usuario"] !== $id) {
            RespuestaHelper::error("El correo electrónico ya está en uso por otro usuario", 409);
        }

        $datosActualizar = [
            "nombre"        => $nombre,
            "correo"        => $correo,
            "idRol"         => $idRol,
            "idDependencia" => $idDependencia,
            "direccion"     => $direccion,
            "telefono"      => $telefono
        ];

        $resultado = ModeloUsuarios::mdlEditarUsuario($id, $datosActualizar);

        if ($resultado) {
            RespuestaHelper::exito([], "Usuario actualizado correctamente", 200);
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
    API REST: MÉTRICAS Y ESTADÍSTICAS (GET /api/usuarios/stats)
    =============================================*/
    public static function ctrEstadisticas(): void {
        try {
            $total = ModeloUsuarios::mdlContarUsuarios();
            $activos = ModeloUsuarios::mdlContarUsuariosActivos();
            $inactivos = ModeloUsuarios::mdlContarUsuariosInactivos();

            RespuestaHelper::exito([
                "total"     => (int)($total["total"] ?? 0),
                "activos"   => (int)($activos["total"] ?? 0),
                "inactivos" => (int)($inactivos["total"] ?? 0)
            ], "Estadísticas de usuarios obtenidas correctamente");
        } catch (Exception $e) {
            error_log("Error en ControladorUsuarios::ctrEstadisticas: " . $e->getMessage());
            RespuestaHelper::error("Error al calcular estadísticas", 500);
        }
    }

    /*=============================================
    COMPATIBILIDAD CON LOGIN DE SESIÓN EXISTENTE
    =============================================*/
    public static function ctrIngresoUsuario(): void {
        if (isset($_POST["ingUsuario"])) {
            if (preg_match('/^[a-zA-Z0-9_@.-]+$/', $_POST["ingUsuario"])) {
                $item = "num_identificacion";
                $valor = $_POST["ingUsuario"];

                $respuesta = ModeloUsuarios::mdlMostrarUsuario($item, $valor);

                if (!$respuesta) {
                    $item = "correo";
                    $respuesta = ModeloUsuarios::mdlMostrarUsuario($item, $valor);
                }

                if ($respuesta && ($respuesta["num_identificacion"] == $_POST["ingUsuario"] || $respuesta["correo"] == $_POST["ingUsuario"])) {
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

                            echo '<script>window.location = "inicio";</script>';
                        } else {
                            echo '<div class="alert alert-warning mt-3 text-center" role="alert">
                                    <i class="icon fas fa-exclamation-triangle mr-1"></i> El usuario se encuentra desactivado.
                                  </div>';
                        }
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