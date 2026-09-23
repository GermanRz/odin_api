<?php


class ControladorUsuarios {

    /*=============================================
    INGRESO DE USUARIO
    =============================================*/
    public static function ctrIngresoUsuario() {

        if (isset($_POST["ingUsuario"])) {

            if (preg_match('/^[a-zA-Z0-9_@.-]+$/', $_POST["ingUsuario"])) {

                $tabla = "usuarios";
                $item = "num_identificacion";
                $valor = $_POST["ingUsuario"];

                $respuesta = ModeloUsuarios::mdlMostrarUsuario($tabla, $item, $valor);

                // Si no se encuentra por número de identificación, buscar por correo
                if (!$respuesta) {
                    $item = "correo";
                    $respuesta = ModeloUsuarios::mdlMostrarUsuario($tabla, $item, $valor);
                }

                if ($respuesta && ($respuesta["num_identificacion"] == $_POST["ingUsuario"] || $respuesta["correo"] == $_POST["ingUsuario"])) {

                    // Verificar contraseña con password_verify
                    if (password_verify($_POST["ingPassword"], $respuesta["password"])) {

                        // Verificación de usuario en estado activo
                        if ($respuesta["estado"] == "Activo") {

                            $_SESSION["iniciarSesion"] = "ok";
                            $_SESSION["id"] = $respuesta["id_usuario"];
                            $_SESSION["nombre"] = $respuesta["nombre"];
                            $_SESSION["numIdentificacion"] = $respuesta["num_identificacion"];
                            $_SESSION["num_identificacion"] = $respuesta["num_identificacion"];
                            $_SESSION["correo"] = $respuesta["correo"];
                            $_SESSION["rol"] = $respuesta["id_rol"];
                            $_SESSION["estado"] = $respuesta["estado"];

                            echo '<script>
                                window.location = "inicio";
                            </script>';

                        } else {
                            // Usuario desactivado: div indicando estado inactivo dentro del logueo
                            echo '<div class="alert alert-warning mt-3 text-center" role="alert">
                                    <i class="icon fas fa-exclamation-triangle mr-1"></i> El usuario se encuentra desactivado.
                                  </div>';
                        }

                    } else {
                        // Contraseña incorrecta: div indicando error dentro del logueo
                        echo '<div class="alert alert-danger mt-3 text-center" role="alert">
                                <i class="icon fas fa-ban mr-1"></i> Error al ingresar, usuario o contraseña incorrectos.
                              </div>';
                    }

                } else {
                    // Usuario no encontrado: div indicando error dentro del logueo
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

    public static function ctrMostrarUsuarios() {
        $tabla = "usuarios";
        $respuesta = ModeloUsuarios::mdlMostrarUsuarios($tabla);
        return $respuesta;
    }

    public static function ctrActivarUsuario($estadoUsuario, $idUsuario) {
        $respuesta = ModeloUsuarios::mdlActivarUsuario($estadoUsuario, $idUsuario);
        return $respuesta;
    }

    public static function ctrContarUsuarios() {
        $tabla = "usuarios";
        $respuesta = ModeloUsuarios::mdlContarUsuarios($tabla);
        return $respuesta;
    }

    public static function ctrContarUsuariosActivos() {
        $tabla = "usuarios";
        $respuesta = ModeloUsuarios::mdlContarUsuariosActivos($tabla);
        return $respuesta;
    }

    public static function ctrCrearUsuario() {
        if (isset($_POST["nuevoNombre"])) {
            $tabla = "usuarios";
            $datos = array(
                "tipo_documento" => $_POST["nuevoTipoDocumento"],
                "numero_identificacion" => $_POST["nuevoNumeroIdentificacion"],
                "nombre" => $_POST["nuevoNombre"],
                "correo" => $_POST["nuevoCorreo"],
                "rol" => $_POST["nuevoRol"],
                "dependencia" => $_POST["nuevaDependencia"],
                "direccion" => $_POST["nuevaDireccion"],
                "telefono" => $_POST["nuevoTelefono"],
                "estado" => "Activo",
                "password" => password_hash($_POST["nuevoNumeroIdentificacion"], PASSWORD_DEFAULT)
            );

            $respuesta = ModeloUsuarios::mdlCrearUsuario($tabla, $datos);
            if ($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡El usuario ha sido creado correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location = "gestion_usuarios";
                        }
                    });
                </script>';
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "¡Error al crear el usuario!",
                        text: "Por favor, inténtelo de nuevo.",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }

        }

    }   
    
    public static function ctrMostrarUsuario($campo, $valor){
        $tabla = "usuarios";
        $respuesta = ModeloUsuarios::mdlMostrarUsuario($tabla, $campo, $valor);
        return $respuesta;

    }

    public static function ctrEditarUsuario() {
        if (isset($_POST["editarNombre"])) {
            $tabla = "usuarios";
            $datos = array(
                "id_usuario" => $_POST["idUsuario"],
                "nombre" => $_POST["editarNombre"],
                "correo" => $_POST["editarCorreo"],
                "rol" => $_POST["editarRol"],
                "dependencia" => $_POST["editarDependencia"],
                "direccion" => $_POST["editarDireccion"],
                "telefono" => $_POST["editarTelefono"]
            );

            $respuesta = ModeloUsuarios::mdlEditarUsuario($tabla, $datos);

            if ($respuesta == "ok") {
                echo '<script>
                    Swal.fire({
                        icon: "success",
                        title: "¡El usuario ha sido editado correctamente!",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location = "gestion_usuarios";
                        }
                    });
                </script>';
            } else {
                echo '<script>
                    Swal.fire({
                        icon: "error",
                        title: "¡Error al editar el usuario!",
                        text: "Por favor, inténtelo de nuevo.",
                        showConfirmButton: true,
                        confirmButtonText: "Cerrar"
                    });
                </script>';
            }
        }
    }

} // End of class ControladorUsuarios