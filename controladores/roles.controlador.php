<?php

require_once __DIR__ . "/../modelos/roles.modelo.php";
require_once __DIR__ . "/../api/helpers/respuesta.helper.php";

/**
 * ControladorRoles
 * 
 * Controlador RESTful para la gestión de roles y matriz de permisos granulares (RBAC).
 * Cumple con RF-N13, RF-N14, RF-N16 y RF-43 sin alterar el esquema de base de datos.
 */
class ControladorRoles {

    /**
     * Enriquece los datos de un rol con su código y descripción legible para la vista.
     */
    private static function enriquecerRol(array $rol): array {
        $codigo = strtoupper(trim($rol["rol"] ?? ""));
        $descripcionesBase = [
            "ADMIN"       => "Administrador del sistema",
            "OPERARIO"    => "Encargado de radicación y recepción documental",
            "RADICADOR"   => "Encargado de radicación documental",
            "GESTOR"      => "Encargado de gestión de trámites",
            "CONSULTA"    => "Usuario de consulta básica",
            "SUPERVISOR"  => "Supervisor de procesos documentales",
            "INVITADO"    => "Acceso de consulta general"
        ];

        $rol["codigo"] = $codigo !== "" ? $codigo : strtoupper($rol["nombre"] ?? "ROL");
        if (empty($rol["descripcion"])) {
            $rol["descripcion"] = $descripcionesBase[$codigo] ?? ("Perfil de acceso: " . ($rol["nombre"] ?? ""));
        }
        $rol["total_permisos"] = (int)($rol["total_permisos"] ?? 0);
        $rol["total_usuarios"] = (int)($rol["total_usuarios"] ?? 0);

        return $rol;
    }

    /**
     * Enriquece cada permiso con la etiqueta visual de su módulo para la matriz RBAC.
     */
    private static function enriquecerPermiso(array $permiso): array {
        $nombre = $permiso["nombre"] ?? "";
        $moduloRaw = strtolower(trim($permiso["modulo"] ?? ""));

        $mapaModulos = [
            "usuarios"     => "Usuarios",
            "seguridad"    => "Roles y Permisos",
            "dependencias" => "Dependencias",
            "ccd"          => "Clasificación Documental",
            "radicados"    => "Radicación",
            "tramites"     => "Trámites",
            "documentos"   => "Documentos",
            "reportes"     => "Reportes",
            "bitacora"     => "Auditoría"
        ];

        if ($nombre === "ver_radicados") {
            $permiso["modulo_etiqueta"] = "Consulta y Seguimiento";
        } elseif ($nombre === "trasladar_radicado" || $nombre === "finalizar_radicado") {
            $permiso["modulo_etiqueta"] = "Trámites";
        } else {
            $permiso["modulo_etiqueta"] = $mapaModulos[$moduloRaw] ?? ucfirst($moduloRaw ?: "General");
        }

        return $permiso;
    }

    /*=============================================
    API REST: LISTAR ROLES (GET /api/roles)
    =============================================*/
    public static function ctrListar(): void {
        try {
            $roles = ModeloRoles::mdlMostrarRoles("roles");
            $rolesEnriquecidos = array_map([self::class, "enriquecerRol"], $roles);
            RespuestaHelper::exito($rolesEnriquecidos, "Listado de roles obtenido correctamente");
        } catch (Exception $e) {
            error_log("Error en ControladorRoles::ctrListar: " . $e->getMessage());
            RespuestaHelper::error("Error interno al obtener el listado de roles", 500);
        }
    }

    /*=============================================
    API REST: ESTADÍSTICAS DE ROLES Y PERMISOS (GET /api/roles/stats)
    =============================================*/
    public static function ctrEstadisticas(): void {
        try {
            $stats = ModeloRoles::mdlEstadisticasRoles();
            RespuestaHelper::exito($stats, "Estadísticas de roles y permisos obtenidas correctamente");
        } catch (Exception $e) {
            error_log("Error en ControladorRoles::ctrEstadisticas: " . $e->getMessage());
            RespuestaHelper::error("Error al calcular estadísticas de roles", 500);
        }
    }

    /*=============================================
    API REST: CATÁLOGO DE PERMISOS GRANULARES (GET /api/roles/permisos)
    =============================================*/
    public static function ctrListarPermisos(): void {
        try {
            $permisos = ModeloRoles::mdlMostrarPermisos();
            $permisosEnriquecidos = array_map([self::class, "enriquecerPermiso"], $permisos);
            RespuestaHelper::exito($permisosEnriquecidos, "Catálogo de permisos RBAC obtenido correctamente");
        } catch (Exception $e) {
            error_log("Error en ControladorRoles::ctrListarPermisos: " . $e->getMessage());
            RespuestaHelper::error("Error interno al obtener el catálogo de permisos", 500);
        }
    }

    /*=============================================
    API REST: OBTENER ROL Y SUS PERMISOS (GET /api/roles/{id})
    =============================================*/
    public static function ctrObtener(int $id): void {
        try {
            $rol = ModeloRoles::mdlMostrarRoles("roles", "id_rol", $id);
            if (!$rol) {
                RespuestaHelper::error("El rol solicitado no existe", 404);
            }

            $rol = self::enriquecerRol($rol);
            $permisos = array_map([self::class, "enriquecerPermiso"], ModeloRoles::mdlMostrarPermisosPorRol($id));
            $permisosIds = array_map(function($p) {
                return (int)$p["id_permiso"];
            }, $permisos);

            $rol["permisos"] = $permisos;
            $rol["permisosIds"] = $permisosIds;

            RespuestaHelper::exito($rol, "Información del rol y permisos obtenida correctamente");
        } catch (Exception $e) {
            error_log("Error en ControladorRoles::ctrObtener: " . $e->getMessage());
            RespuestaHelper::error("Error interno al obtener los datos del rol", 500);
        }
    }

    /*=============================================
    API REST: CREAR ROL Y ASIGNAR PERMISOS (POST /api/roles)
    =============================================*/
    public static function ctrCrear(array $datos): void {
        $nombre    = trim($datos["nombre"] ?? "");
        $rolCodigo = strtolower(trim($datos["rol"] ?? $datos["codigo"] ?? $datos["tipo"] ?? ""));
        $estado    = trim($datos["estado"] ?? "Activo");
        $permisos  = $datos["permisos"] ?? [];

        if (empty($nombre)) {
            RespuestaHelper::error("El nombre del rol es obligatorio", 400);
        }

        if (empty($rolCodigo)) {
            $rolCodigo = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $nombre));
        }

        if (!in_array($estado, ["Activo", "Inactivo"], true)) {
            $estado = "Activo";
        }

        if (!is_array($permisos)) {
            $permisos = [];
        }

        $existeNombre = ModeloRoles::mdlMostrarRoles("roles", "nombre", $nombre);
        if ($existeNombre) {
            RespuestaHelper::error("Ya existe un rol registrado con ese nombre", 409);
        }

        $idRolNuevo = ModeloRoles::mdlCrearRol([
            "rol"    => $rolCodigo,
            "nombre" => $nombre,
            "estado" => $estado
        ], $permisos);

        if ($idRolNuevo) {
            RespuestaHelper::exito(["id_rol" => $idRolNuevo], "Rol y matriz de permisos registrados exitosamente", 201);
        } else {
            RespuestaHelper::error("No fue posible registrar el rol en la base de datos", 500);
        }
    }

    /*=============================================
    API REST: ACTUALIZAR ROL Y MATRIZ DE PERMISOS (PUT /api/roles/{id})
    =============================================*/
    public static function ctrActualizar(int $id, array $datos): void {
        $rolExistente = ModeloRoles::mdlMostrarRoles("roles", "id_rol", $id);
        if (!$rolExistente) {
            RespuestaHelper::error("El rol que intenta actualizar no existe", 404);
        }

        $nombre    = trim($datos["nombre"] ?? "");
        $rolCodigo = strtolower(trim($datos["rol"] ?? $datos["codigo"] ?? $datos["tipo"] ?? $rolExistente["rol"] ?? ""));
        $estado    = trim($datos["estado"] ?? $rolExistente["estado"] ?? "Activo");
        $permisos  = $datos["permisos"] ?? [];

        if (empty($nombre)) {
            RespuestaHelper::error("El nombre del rol es obligatorio para actualizar", 400);
        }

        if ($id === 1 && $estado !== "Activo") {
            RespuestaHelper::error("El rol Administrador principal del sistema debe permanecer Activo", 400);
        }

        if (!in_array($estado, ["Activo", "Inactivo"], true)) {
            $estado = "Activo";
        }

        $otroConNombre = ModeloRoles::mdlMostrarRoles("roles", "nombre", $nombre);
        if ($otroConNombre && (int)$otroConNombre["id_rol"] !== $id) {
            RespuestaHelper::error("Ya existe otro rol con el nombre especificado", 409);
        }

        if (!is_array($permisos)) {
            $permisos = [];
        }

        $actualizado = ModeloRoles::mdlEditarRol($id, [
            "rol"    => $rolCodigo ?: strtolower($nombre),
            "nombre" => $nombre,
            "estado" => $estado
        ], $permisos);

        if ($actualizado) {
            if (isset($_SESSION["rol"]) && (int)$_SESSION["rol"] === $id) {
                self::ctrCargarPermisosSesion($id);
            }
            RespuestaHelper::exito(["id_rol" => $id], "Rol y permisos actualizados correctamente", 200);
        } else {
            RespuestaHelper::error("No fue posible actualizar la información del rol", 500);
        }
    }

    /*=============================================
    API REST: CAMBIAR ESTADO DE ROL (PATCH /api/roles/{id}/estado)
    =============================================*/
    public static function ctrCambiarEstado(int $id, string $nuevoEstado): void {
        if (!in_array($nuevoEstado, ["Activo", "Inactivo"], true)) {
            RespuestaHelper::error("El estado especificado no es válido (debe ser Activo o Inactivo)", 400);
        }

        if ($id === 1 && $nuevoEstado !== "Activo") {
            RespuestaHelper::error("El rol Administrador principal del sistema no puede ser desactivado", 400);
        }

        $rolExistente = ModeloRoles::mdlMostrarRoles("roles", "id_rol", $id);
        if (!$rolExistente) {
            RespuestaHelper::error("El rol a modificar no existe", 404);
        }

        $resultado = ModeloRoles::mdlActualizarEstado($id, $nuevoEstado);

        if ($resultado) {
            RespuestaHelper::exito(["nuevoEstado" => $nuevoEstado], "Estado del rol actualizado correctamente");
        } else {
            RespuestaHelper::error("No fue posible cambiar el estado del rol", 500);
        }
    }

    /*=============================================
    API REST: ELIMINAR ROL (DELETE /api/roles/{id})
    =============================================*/
    public static function ctrEliminar(int $id): void {
        if ($id === 1) {
            RespuestaHelper::error("El rol Administrador principal del sistema está protegido y no puede eliminarse", 400);
        }

        $rolExistente = ModeloRoles::mdlMostrarRoles("roles", "id_rol", $id);
        if (!$rolExistente) {
            RespuestaHelper::error("El rol que intenta eliminar no existe", 404);
        }

        $resultado = ModeloRoles::mdlEliminarRol($id);

        if ($resultado === "ok") {
            RespuestaHelper::exito([], "El rol y sus asignaciones de permisos fueron eliminados correctamente", 200);
        } elseif ($resultado === "en_uso") {
            RespuestaHelper::error("No se puede eliminar este rol porque tiene usuarios asignados. Reasigne los usuarios o desactive el rol.", 409);
        } else {
            RespuestaHelper::error("Error interno al intentar eliminar el rol", 500);
        }
    }

    /*=============================================
    RBAC: CARGAR PERMISOS DEL ROL EN LA SESIÓN ACTUAL (RF-N13)
    =============================================*/
    public static function ctrCargarPermisosSesion(int $idRol): array {
        $permisosFilas = ModeloRoles::mdlMostrarPermisosPorRol($idRol);
        $nombresPermisos = [];

        foreach ($permisosFilas as $p) {
            if (!empty($p["nombre"])) {
                $nombresPermisos[] = $p["nombre"];
            }
        }

        $_SESSION["permisos"] = $nombresPermisos;

        $rolInfo = ModeloRoles::mdlMostrarRoles("roles", "id_rol", $idRol);
        if ($rolInfo) {
            $_SESSION["nombre_rol"] = $rolInfo["nombre"];
            $_SESSION["codigo_rol"] = $rolInfo["rol"] ?? $rolInfo["nombre"];
            $_SESSION["estado_rol"] = $rolInfo["estado"];
        }

        return $nombresPermisos;
    }

    /*=============================================
    RBAC: VERIFICAR SI EL USUARIO TIENE UN PERMISO (RF-N13 / RF-N14)
    =============================================*/
    public static function ctrTienePermiso($permisoRequerido): bool {
        if (!isset($_SESSION["iniciarSesion"]) || $_SESSION["iniciarSesion"] !== "ok") {
            return false;
        }

        $idRol = isset($_SESSION["rol"]) ? (int)$_SESSION["rol"] : 0;
        if (!isset($_SESSION["permisos"]) || !is_array($_SESSION["permisos"])) {
            self::ctrCargarPermisosSesion($idRol);
        }

        if ($idRol === 1 || (isset($_SESSION["codigo_rol"]) && strtoupper((string)$_SESSION["codigo_rol"]) === "ADMIN")) {
            return true;
        }

        if (is_array($permisoRequerido)) {
            foreach ($permisoRequerido as $perm) {
                if (in_array($perm, $_SESSION["permisos"], true)) {
                    return true;
                }
            }
            return false;
        }

        return in_array((string)$permisoRequerido, $_SESSION["permisos"], true);
    }

    /*=============================================
    COMPATIBILIDAD HEREDADA
    =============================================*/
    public static function ctrMostrarRoles($campo = null, $valor = null) {
        return ModeloRoles::mdlMostrarRoles("roles", $campo, $valor);
    }

    public static function ctrMostrarPermisos(): array {
        return array_map([self::class, "enriquecerPermiso"], ModeloRoles::mdlMostrarPermisos());
    }

    public static function ctrMostrarPermisosPorRol(int $idRol): array {
        return array_map([self::class, "enriquecerPermiso"], ModeloRoles::mdlMostrarPermisosPorRol($idRol));
    }
}
