<?php

require_once __DIR__ . "/../modelos/radicados.modelo.php";
require_once __DIR__ . "/../servicios/archivo.servicio.php";
require_once __DIR__ . "/../api/helpers/respuesta.helper.php";

/**
 * Controlador RESTful para Radicación Documental — Proyecto ODIN
 * 
 * Gestiona el ciclo completo de radicación, validación de datos de entrada,
 * verificación binaria de documentos MIME y coordinación con el servicio
 * de archivos y el modelo de persistencia.
 */
class ControladorRadicados
{
    /**
     * Procesa la radicación de un documento de entrada/salida/interno con adjuntos.
     * Recibe payload de formulario (POST) y archivos adjuntos ($_FILES).
     */
    public static function ctrRadicar(array $post, array $files): void
    {
        $archivosGuardadosTemporalmente = [];

        try {
            // 1. Validar campos mínimos del radicado
            $asunto = trim($post["asuntoDocumento"] ?? ($post["asunto"] ?? ""));
            if (empty($asunto)) {
                RespuestaHelper::error("El asunto del documento es obligatorio.", 400);
                return;
            }

            $tipoRadicacionRaw = trim($post["tipoRadicacion"] ?? ($post["tipo_radicado"] ?? "entrada"));
            $tipoRadicacionMap = [
                "entrada" => "ENTRADA",
                "salida"  => "SALIDA",
                "interna" => "INTERNO",
                "interno" => "INTERNO"
            ];
            $tipoRadicado = $tipoRadicacionMap[strtolower($tipoRadicacionRaw)] ?? "ENTRADA";

            $medioRecepcion = trim($post["canalRecepcion"] ?? ($post["medio_recepcion"] ?? "Ventanilla"));
            $prioridad = strtoupper(trim($post["prioridad"] ?? "MEDIA"));
            if (!in_array($prioridad, ["BAJA", "MEDIA", "ALTA", "URGENTE"], true)) {
                $prioridad = "MEDIA";
            }

            $idTramite     = !empty($post["tramite"]) ? (int)$post["tramite"] : (!empty($post["id_tramite"]) ? (int)$post["id_tramite"] : null);
            $idEstado      = !empty($post["estadoInicial"]) ? (int)$post["estadoInicial"] : (!empty($post["id_estado"]) ? (int)$post["id_estado"] : 1);
            $idDependencia = !empty($post["dependenciaDestino"]) 
                                ? (int)$post["dependenciaDestino"] 
                                : (!empty($post["dependenciaResponsable"]) 
                                    ? (int)$post["dependenciaResponsable"] 
                                    : (!empty($post["id_dependencia"]) ? (int)$post["id_dependencia"] : null));
            $idUsuario     = !empty($post["responsable"]) ? (int)$post["responsable"] : (!empty($post["id_usuario"]) ? (int)$post["id_usuario"] : 1);

            $fechaLimite = !empty($post["fechaLimite"]) ? trim($post["fechaLimite"]) : (!empty($post["fecha_limite"]) ? trim($post["fecha_limite"]) : null);

            // 2. Manejo de Ley de Transparencia (Radicación Anónima RF-09)
            $esAnonimo = !empty($post["radicacionAnonima"]) || (isset($post["es_anonimo"]) && ($post["es_anonimo"] === true || $post["es_anonimo"] === "1" || $post["es_anonimo"] === "true"));

            $datosRemitente = null;
            $remitenteNombre = "ANÓNIMO";

            if (!$esAnonimo) {
                $nombreSol = trim($post["nombreSolicitante"] ?? ($post["remitente"] ?? ""));
                $numDoc    = trim($post["numeroDocumento"] ?? ($post["num_identificacion"] ?? ""));
                $correoSol = trim($post["correoSolicitante"] ?? ($post["correo"] ?? ""));
                $tipoDoc   = trim($post["tipoDocumento"] ?? ($post["tipo_identificacion"] ?? "CC"));
                $telSol    = trim($post["telefonoSolicitante"] ?? ($post["telefono"] ?? ""));
                $ciuSol    = trim($post["ciudadSolicitante"] ?? ($post["direccion"] ?? ""));

                if (empty($nombreSol) || empty($numDoc)) {
                    RespuestaHelper::error("Para radicaciones no anónimas, el nombre y número de identificación del solicitante son obligatorios.", 400);
                    return;
                }

                if (!empty($correoSol) && !filter_var($correoSol, FILTER_VALIDATE_EMAIL)) {
                    RespuestaHelper::error("La dirección de correo electrónico del solicitante no tiene un formato válido.", 400);
                    return;
                }

                $remitenteNombre = $nombreSol;
                $datosRemitente = [
                    "tipo_identificacion" => strtoupper($tipoDoc),
                    "num_identificacion"  => $numDoc,
                    "nombre"              => $nombreSol,
                    "apellido"            => "",
                    "nombre_completo"     => $nombreSol,
                    "correo"              => $correoSol,
                    "telefono"            => $telSol,
                    "direccion"           => $ciuSol
                ];
            }

            // 3. Normalizar y validar archivos adjuntos
            $listaArchivos = self::normalizarArchivosSubidos($files);
            if (empty($listaArchivos)) {
                RespuestaHelper::error("Debe adjuntar al menos un documento digital válido (PDF, Excel o imagen) para radicar.", 400);
                return;
            }

            // Prefijo preventivo temporal para el nombrado de archivos antes de la asignación del consecutivo
            $prefijoTemp = "TEMP_" . date("YmdHis");
            $metadatosDocumentos = [];

            foreach ($listaArchivos as $archivoIndividual) {
                // Validación estricta con ArchivoServicio (MIME binario real, límite de peso, hash SHA-256)
                $metaDoc = ArchivoServicio::procesarArchivo($archivoIndividual, $prefijoTemp, "DOC");
                $archivosGuardadosTemporalmente[] = $metaDoc["ruta_archivo"];
                $metadatosDocumentos[] = $metaDoc;
            }

            // 4. Estructurar datos para persistencia en base de datos
            $datosRadicado = [
                "asunto"           => $asunto,
                "tipo_radicado"    => $tipoRadicado,
                "medio_recepcion"  => $medioRecepcion,
                "prioridad"        => $prioridad,
                "confidencialidad" => "PUBLICO",
                "id_tramite"       => $idTramite,
                "id_estado"        => $idEstado,
                "id_dependencia"   => $idDependencia,
                "id_usuario"       => $idUsuario,
                "fecha_limite"     => $fechaLimite,
                "es_anonimo"       => $esAnonimo,
                "remitente"        => $remitenteNombre
            ];

            // 5. Persistencia atómica
            $resultado = ModeloRadicados::mdlRadicarDocumento($datosRadicado, $metadatosDocumentos, $datosRemitente);

            RespuestaHelper::exito(
                $resultado,
                "Documento radicado exitosamente con el número " . $resultado["numero_radicado"],
                201
            );
        } catch (InvalidArgumentException $ie) {
            // Error de validación de negocio o de archivo (MIME, peso)
            self::limpiarArchivosTemporales($archivosGuardadosTemporalmente);
            RespuestaHelper::error($ie->getMessage(), 400);
        } catch (Exception $e) {
            // Error interno de base de datos o sistema
            self::limpiarArchivosTemporales($archivosGuardadosTemporalmente);
            error_log("Error en ControladorRadicados::ctrRadicar: " . $e->getMessage());
            RespuestaHelper::error("Ocurrió un error interno al registrar la radicación: " . $e->getMessage(), 500);
        }
    }

    /**
     * Consulta y retorna el listado de radicados registrados.
     */
    public static function ctrListar(): void
    {
        try {
            $radicados = ModeloRadicados::mdlListarRadicados();
            RespuestaHelper::exito($radicados, "Listado de radicados obtenido correctamente");
        } catch (Exception $e) {
            error_log("Error en ControladorRadicados::ctrListar: " . $e->getMessage());
            RespuestaHelper::error("Error al obtener la lista de radicados.", 500);
        }
    }

    /**
     * Consulta y retorna el detalle de un radicado específico.
     */
    public static function ctrObtener(int $id): void
    {
        try {
            $radicado = ModeloRadicados::mdlObtenerRadicadoPorId($id);
            if (!$radicado) {
                RespuestaHelper::error("El radicado solicitado no existe.", 404);
                return;
            }
            RespuestaHelper::exito($radicado, "Detalle del radicado obtenido correctamente");
        } catch (Exception $e) {
            error_log("Error en ControladorRadicados::ctrObtener: " . $e->getMessage());
            RespuestaHelper::error("Error al obtener la información del radicado.", 500);
        }
    }

    /**
     * Retorna los catálogos de soporte para el formulario de radicación.
     */
    public static function ctrCatalogos(): void
    {
        try {
            $catalogos = ModeloRadicados::mdlObtenerCatalogos();
            RespuestaHelper::exito($catalogos, "Catálogos de radicación obtenidos correctamente");
        } catch (Exception $e) {
            error_log("Error en ControladorRadicados::ctrCatalogos: " . $e->getMessage());
            RespuestaHelper::error("Error al cargar los catálogos del sistema.", 500);
        }
    }

    /**
     * Normaliza la estructura multidimensional o simple de $_FILES a una lista homogénea.
     */
    private static function normalizarArchivosSubidos(array $files): array
    {
        $normalizados = [];

        // Buscar campos comunes: 'adjuntos', 'adjuntos[]', 'archivo', 'documentos'
        foreach ($files as $clave => $item) {
            if (!isset($item["name"])) {
                continue;
            }

            if (is_array($item["name"])) {
                $total = count($item["name"]);
                for ($i = 0; $i < $total; $i++) {
                    if ($item["error"][$i] === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }
                    $normalizados[] = [
                        "name"     => $item["name"][$i],
                        "type"     => $item["type"][$i],
                        "tmp_name" => $item["tmp_name"][$i],
                        "error"    => $item["error"][$i],
                        "size"     => $item["size"][$i]
                    ];
                }
            } else {
                if ($item["error"] !== UPLOAD_ERR_NO_FILE) {
                    $normalizados[] = $item;
                }
            }
        }

        return $normalizados;
    }

    /**
     * Limpia del almacenamiento los archivos procesados en caso de fallo posterior en BD.
     */
    private static function limpiarArchivosTemporales(array $rutasRelativas): void
    {
        foreach ($rutasRelativas as $ruta) {
            ArchivoServicio::revertirArchivo($ruta);
        }
    }
}
