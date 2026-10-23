<?php

require_once __DIR__ . "/../modelos/dependencias.modelo.php";
require_once __DIR__ . "/../api/helpers/respuesta.helper.php";

/**
 * Controlador de Dependencias — Proyecto ODIN
 * 
 * Gestiona peticiones RESTful y operaciones de dependencias organizacionales,
 * incluyendo la asignación y edición del usuario encargado / responsable.
 */
class ControladorDependencias
{
    /**
     * Endpoint REST: GET /api/dependencias
     * Lista todas las dependencias registradas con su encargado asignado.
     */
    public static function ctrListar(): void
    {
        try {
            $dependencias = ModeloDependencias::mdlMostrarDependencias();
            RespuestaHelper::exito($dependencias, "Listado de dependencias obtenido correctamente");
        } catch (Exception $e) {
            error_log("Error en ControladorDependencias::ctrListar: " . $e->getMessage());
            RespuestaHelper::error("Error al obtener el listado de dependencias", 500);
        }
    }

    /**
     * Endpoint REST: GET /api/dependencias/{id}
     * Obtiene el detalle de una dependencia específica.
     */
    public static function ctrObtener(int $id): void
    {
        if ($id <= 0) {
            RespuestaHelper::error("El identificador de dependencia proporcionado es inválido.", 400);
            return;
        }

        try {
            $dependencia = ModeloDependencias::mdlMostrarDependenciaPorId($id);
            if ($dependencia) {
                RespuestaHelper::exito($dependencia, "Dependencia obtenida correctamente");
            } else {
                RespuestaHelper::error("No se encontró la dependencia solicitada.", 404);
            }
        } catch (Exception $e) {
            error_log("Error en ControladorDependencias::ctrObtener: " . $e->getMessage());
            RespuestaHelper::error("Error al consultar la dependencia", 500);
        }
    }

    /**
     * Endpoint REST: POST /api/dependencias
     * Registra una nueva dependencia en el sistema con su usuario encargado.
     */
    public static function ctrCrear(array $body): void
    {
        $nombre = trim($body["nombre"] ?? "");
        if (empty($nombre)) {
            RespuestaHelper::error("El nombre de la dependencia es obligatorio.", 400);
            return;
        }

        try {
            $idResponsable = null;
            if (!empty($body["id_usuario_responsable"])) {
                $idResponsable = (int)$body["id_usuario_responsable"];
            }

            $datos = [
                "nombre"                 => $nombre,
                "dependencia"            => !empty($body["dependencia"]) ? trim($body["dependencia"]) : $nombre,
                "codigo"                 => !empty($body["codigo"]) ? trim($body["codigo"]) : null,
                "descripcion"            => !empty($body["descripcion"]) ? trim($body["descripcion"]) : null,
                "tipo"                   => !empty($body["tipo"]) ? trim($body["tipo"]) : "operativa",
                "estado"                 => !empty($body["estado"]) ? trim($body["estado"]) : "Activo",
                "id_usuario_responsable" => $idResponsable
            ];

            $idNueva = ModeloDependencias::mdlCrearDependencia($datos);
            if ($idNueva) {
                $depCreada = ModeloDependencias::mdlMostrarDependenciaPorId($idNueva);
                RespuestaHelper::exito($depCreada, "Dependencia registrada exitosamente", 201);
            } else {
                RespuestaHelper::error("No fue posible guardar la nueva dependencia.", 500);
            }
        } catch (Exception $e) {
            error_log("Error en ControladorDependencias::ctrCrear: " . $e->getMessage());
            RespuestaHelper::error("Error al procesar la creación de la dependencia", 500);
        }
    }

    /**
     * Endpoint REST: PUT /api/dependencias/{id}
     * Actualiza los datos de una dependencia, incluyendo la reasignación de su encargado.
     */
    public static function ctrActualizar(int $id, array $body): void
    {
        if ($id <= 0) {
            RespuestaHelper::error("Identificador de dependencia inválido.", 400);
            return;
        }

        $nombre = trim($body["nombre"] ?? "");
        if (empty($nombre)) {
            RespuestaHelper::error("El nombre de la dependencia es obligatorio.", 400);
            return;
        }

        try {
            $idResponsable = null;
            if (isset($body["id_usuario_responsable"]) && $body["id_usuario_responsable"] !== "" && $body["id_usuario_responsable"] !== null) {
                $idResponsable = (int)$body["id_usuario_responsable"];
            }

            $datos = [
                "nombre"                 => $nombre,
                "dependencia"            => !empty($body["dependencia"]) ? trim($body["dependencia"]) : $nombre,
                "codigo"                 => !empty($body["codigo"]) ? trim($body["codigo"]) : null,
                "descripcion"            => !empty($body["descripcion"]) ? trim($body["descripcion"]) : null,
                "tipo"                   => !empty($body["tipo"]) ? trim($body["tipo"]) : "operativa",
                "estado"                 => !empty($body["estado"]) ? trim($body["estado"]) : "Activo",
                "id_usuario_responsable" => $idResponsable
            ];

            $actualizado = ModeloDependencias::mdlActualizarDependencia($id, $datos);
            if ($actualizado) {
                $depActualizada = ModeloDependencias::mdlMostrarDependenciaPorId($id);
                RespuestaHelper::exito($depActualizada, "Dependencia y encargado actualizados exitosamente");
            } else {
                RespuestaHelper::error("No fue posible actualizar la información de la dependencia.", 500);
            }
        } catch (Exception $e) {
            error_log("Error en ControladorDependencias::ctrActualizar: " . $e->getMessage());
            RespuestaHelper::error("Error al actualizar la dependencia", 500);
        }
    }

    /**
     * Endpoint REST: PATCH /api/dependencias/{id}/estado
     * Cambio atómico del estado operativo de la dependencia.
     */
    public static function ctrCambiarEstado(int $id, string $nuevoEstado): void
    {
        if ($id <= 0) {
            RespuestaHelper::error("Identificador de dependencia inválido.", 400);
            return;
        }

        $estadoNormalizado = ucfirst(strtolower(trim($nuevoEstado)));
        if (!in_array($estadoNormalizado, ["Activo", "Inactivo"], true)) {
            RespuestaHelper::error("El estado especificado no es válido (use 'Activo' o 'Inactivo').", 400);
            return;
        }

        try {
            $ok = ModeloDependencias::mdlCambiarEstado($id, $estadoNormalizado);
            if ($ok) {
                RespuestaHelper::exito(["id_dependencia" => $id, "estado" => $estadoNormalizado], "Estado de dependencia actualizado correctamente");
            } else {
                RespuestaHelper::error("No fue posible actualizar el estado de la dependencia.", 500);
            }
        } catch (Exception $e) {
            error_log("Error en ControladorDependencias::ctrCambiarEstado: " . $e->getMessage());
            RespuestaHelper::error("Error al cambiar estado de la dependencia", 500);
        }
    }

    /**
     * Método de compatibilidad para código legacy / llamadas PHP directas.
     */
    public static function ctrMostrarDependencias($tabla = "dependencias")
    {
        return ModeloDependencias::mdlMostrarDependencias();
    }
}
