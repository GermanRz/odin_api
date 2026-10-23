<?php

require_once __DIR__ . "/../modelos/tramites.modelo.php";
require_once __DIR__ . "/../api/helpers/respuesta.helper.php";

/**
 * Controlador RESTful para Catálogo y Parametrización de Trámites — Proyecto ODIN
 * 
 * Atiende solicitudes HTTP asíncronas para la administración de tipos de trámites
 * y la entrega de métricas operativas de la bandeja de trabajo.
 */
class ControladorTramites
{
    /**
     * Retorna el listado completo de tipos de trámites en formato JSON.
     */
    public static function ctrListar(): void
    {
        try {
            $tramites = ModeloTramites::mdlMostrarTramites();
            RespuestaHelper::exito($tramites, "Listado de trámites obtenido correctamente", 200);
        } catch (Exception $e) {
            error_log("Error en ControladorTramites::ctrListar: " . $e->getMessage());
            RespuestaHelper::error("Error al obtener la lista de tipos de trámite", 500);
        }
    }

    /**
     * Retorna los datos detallados de un trámite por ID.
     */
    public static function ctrObtener(int $id): void
    {
        try {
            $tramite = ModeloTramites::mdlObtenerTramite($id);
            if (!$tramite) {
                RespuestaHelper::error("El trámite solicitado no existe", 404);
                return;
            }
            RespuestaHelper::exito($tramite, "Detalle del tipo de trámite", 200);
        } catch (Exception $e) {
            error_log("Error en ControladorTramites::ctrObtener: " . $e->getMessage());
            RespuestaHelper::error("Error al obtener el tipo de trámite", 500);
        }
    }

    /**
     * Registra un nuevo tipo de trámite tras validar los campos obligatorios.
     */
    public static function ctrCrear(array $body): void
    {
        try {
            $nombre = trim($body["nombre"] ?? "");
            $descripcion = trim($body["descripcion"] ?? "");

            if (empty($nombre)) {
                RespuestaHelper::error("El nombre del trámite es obligatorio.", 400);
                return;
            }

            if (empty($descripcion)) {
                RespuestaHelper::error("La descripción del trámite es obligatoria.", 400);
                return;
            }

            $diasRespuesta = isset($body["dias_respuesta"]) ? (int)$body["dias_respuesta"] : 15;
            if ($diasRespuesta < 0 || $diasRespuesta > 365) {
                RespuestaHelper::error("Los días de respuesta deben encontrarse entre 0 y 365 días.", 400);
                return;
            }

            $prioridadesValidas = ["BAJA", "MEDIA", "ALTA", "URGENTE"];
            $prioridad = strtoupper(trim($body["prioridad_default"] ?? "MEDIA"));
            if (!in_array($prioridad, $prioridadesValidas, true)) {
                $prioridad = "MEDIA";
            }

            $idDependencia = !empty($body["id_dependencia_responsable"]) ? (int)$body["id_dependencia_responsable"] : null;
            $idEstadoInicial = !empty($body["id_estado_inicial"]) ? (int)$body["id_estado_inicial"] : 1;
            $requiereRespuesta = !empty($body["requiere_respuesta"]) && ($body["requiere_respuesta"] === true || $body["requiere_respuesta"] === 1 || $body["requiere_respuesta"] === "1" || $body["requiere_respuesta"] === "true") ? 1 : 0;
            $activo = !isset($body["activo"]) || ($body["activo"] === true || $body["activo"] === 1 || $body["activo"] === "1" || $body["activo"] === "true") ? 1 : 0;

            $datos = [
                "nombre"                      => $nombre,
                "descripcion"                 => $descripcion,
                "id_dependencia_responsable"  => $idDependencia,
                "id_estado_inicial"           => $idEstadoInicial,
                "dias_respuesta"              => $diasRespuesta,
                "prioridad_default"           => $prioridad,
                "requiere_respuesta"          => $requiereRespuesta,
                "activo"                      => $activo
            ];

            $nuevoId = ModeloTramites::mdlCrearTramite($datos);
            if ($nuevoId) {
                $datos["id_tramite"] = $nuevoId;
                RespuestaHelper::exito($datos, "Tipo de trámite creado exitosamente", 201);
            } else {
                RespuestaHelper::error("No fue posible registrar el tipo de trámite", 500);
            }
        } catch (Exception $e) {
            error_log("Error en ControladorTramites::ctrCrear: " . $e->getMessage());
            RespuestaHelper::error("Error interno al procesar el registro del trámite", 500);
        }
    }

    /**
     * Actualiza un tipo de trámite existente.
     */
    public static function ctrActualizar(int $id, array $body): void
    {
        try {
            $tramiteExistente = ModeloTramites::mdlObtenerTramite($id);
            if (!$tramiteExistente) {
                RespuestaHelper::error("El tipo de trámite a actualizar no existe", 404);
                return;
            }

            $nombre = trim($body["nombre"] ?? "");
            $descripcion = trim($body["descripcion"] ?? "");

            if (empty($nombre)) {
                RespuestaHelper::error("El nombre del trámite es obligatorio.", 400);
                return;
            }

            if (empty($descripcion)) {
                RespuestaHelper::error("La descripción del trámite es obligatoria.", 400);
                return;
            }

            $diasRespuesta = isset($body["dias_respuesta"]) ? (int)$body["dias_respuesta"] : (int)$tramiteExistente["dias_respuesta"];
            if ($diasRespuesta < 0 || $diasRespuesta > 365) {
                RespuestaHelper::error("Los días de respuesta deben encontrarse entre 0 y 365 días.", 400);
                return;
            }

            $prioridadesValidas = ["BAJA", "MEDIA", "ALTA", "URGENTE"];
            $prioridad = strtoupper(trim($body["prioridad_default"] ?? $tramiteExistente["prioridad_default"]));
            if (!in_array($prioridad, $prioridadesValidas, true)) {
                $prioridad = "MEDIA";
            }

            $idDependencia = !empty($body["id_dependencia_responsable"]) ? (int)$body["id_dependencia_responsable"] : null;
            $idEstadoInicial = !empty($body["id_estado_inicial"]) ? (int)$body["id_estado_inicial"] : null;
            $requiereRespuesta = isset($body["requiere_respuesta"]) && ($body["requiere_respuesta"] === true || $body["requiere_respuesta"] === 1 || $body["requiere_respuesta"] === "1" || $body["requiere_respuesta"] === "true") ? 1 : 0;
            $activo = isset($body["activo"]) && ($body["activo"] === true || $body["activo"] === 1 || $body["activo"] === "1" || $body["activo"] === "true") ? 1 : 0;

            $datos = [
                "nombre"                      => $nombre,
                "descripcion"                 => $descripcion,
                "id_dependencia_responsable"  => $idDependencia,
                "id_estado_inicial"           => $idEstadoInicial,
                "dias_respuesta"              => $diasRespuesta,
                "prioridad_default"           => $prioridad,
                "requiere_respuesta"          => $requiereRespuesta,
                "activo"                      => $activo
            ];

            $resultado = ModeloTramites::mdlActualizarTramite($id, $datos);
            if ($resultado) {
                $datos["id_tramite"] = $id;
                RespuestaHelper::exito($datos, "Tipo de trámite actualizado exitosamente", 200);
            } else {
                RespuestaHelper::error("No fue posible actualizar el tipo de trámite", 500);
            }
        } catch (Exception $e) {
            error_log("Error en ControladorTramites::ctrActualizar: " . $e->getMessage());
            RespuestaHelper::error("Error interno al actualizar el trámite", 500);
        }
    }

    /**
     * Alterna o asigna el estado activo de un tipo de trámite.
     */
    public static function ctrCambiarEstado(int $id, array $body): void
    {
        try {
            $tramite = ModeloTramites::mdlObtenerTramite($id);
            if (!$tramite) {
                RespuestaHelper::error("El trámite solicitado no existe", 404);
                return;
            }

            // Si se pasa estado en el body, usarlo, de lo contrario invertir el actual
            if (isset($body["activo"])) {
                $nuevoActivo = ($body["activo"] === true || $body["activo"] === 1 || $body["activo"] === "1" || $body["activo"] === "Activo") ? 1 : 0;
            } else {
                $nuevoActivo = (int)$tramite["activo"] === 1 ? 0 : 1;
            }

            $resultado = ModeloTramites::mdlCambiarEstado($id, $nuevoActivo);
            if ($resultado) {
                $estadoTexto = $nuevoActivo === 1 ? "Activo" : "Inactivo";
                RespuestaHelper::exito([
                    "id_tramite" => $id,
                    "activo"     => $nuevoActivo,
                    "estado"     => $estadoTexto
                ], "Estado del trámite actualizado a: " . $estadoTexto, 200);
            } else {
                RespuestaHelper::error("No fue posible cambiar el estado del trámite", 500);
            }
        } catch (Exception $e) {
            error_log("Error en ControladorTramites::ctrCambiarEstado: " . $e->getMessage());
            RespuestaHelper::error("Error interno al cambiar el estado del trámite", 500);
        }
    }

    /**
     * Retorna estadísticas cuantitativas de la bandeja de trámites para alimentar los KPIs.
     */
    public static function ctrEstadisticas(): void
    {
        try {
            $stats = ModeloTramites::mdlObtenerEstadisticasTramites();
            RespuestaHelper::exito($stats, "Estadísticas de trámites obtenidas correctamente", 200);
        } catch (Exception $e) {
            error_log("Error en ControladorTramites::ctrEstadisticas: " . $e->getMessage());
            RespuestaHelper::error("Error al obtener las estadísticas operativas", 500);
        }
    }
}
