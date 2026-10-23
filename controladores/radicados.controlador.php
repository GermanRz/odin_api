<?php

require_once __DIR__ . "/../modelos/radicados.modelo.php";
require_once __DIR__ . "/../servicios/archivo.servicio.php";
require_once __DIR__ . "/../api/helpers/respuesta.helper.php";

/**
 * Controlador RESTful para RadicaciÃ³n Documental â€” Proyecto ODIN
 * 
 * Gestiona el ciclo completo de radicaciÃ³n, validaciÃ³n de datos de entrada,
 * verificaciÃ³n binaria de documentos MIME y coordinaciÃ³n con el servicio
 * de archivos y el modelo de persistencia.
 */
class ControladorRadicados
{
    /**
     * Procesa la radicaciÃ³n de un documento de entrada/salida/interno con adjuntos.
     * Recibe payload de formulario (POST) y archivos adjuntos ($_FILES).
     */
    public static function ctrRadicar(array $post, array $files): void
    {
        $archivosGuardadosTemporalmente = [];

        try {
            // 1. Validar campos mÃ­nimos del radicado
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

            // 2. Manejo de Ley de Transparencia (RadicaciÃ³n AnÃ³nima RF-09)
            $esAnonimo = !empty($post["radicacionAnonima"]) || (isset($post["es_anonimo"]) && ($post["es_anonimo"] === true || $post["es_anonimo"] === "1" || $post["es_anonimo"] === "true"));

            $datosRemitente = null;
            $remitenteNombre = "ANÃ“NIMO";

            if (!$esAnonimo) {
                $nombreSol = trim($post["nombreSolicitante"] ?? ($post["remitente"] ?? ""));
                $numDoc    = trim($post["numeroDocumento"] ?? ($post["num_identificacion"] ?? ""));
                $correoSol = trim($post["correoSolicitante"] ?? ($post["correo"] ?? ""));
                $tipoDoc   = trim($post["tipoDocumento"] ?? ($post["tipo_identificacion"] ?? "CC"));
                $telSol    = trim($post["telefonoSolicitante"] ?? ($post["telefono"] ?? ""));
                $ciuSol    = trim($post["ciudadSolicitante"] ?? ($post["direccion"] ?? ""));

                if (empty($nombreSol) || empty($numDoc)) {
                    RespuestaHelper::error("Para radicaciones no anÃ³nimas, el nombre y nÃºmero de identificaciÃ³n del solicitante son obligatorios.", 400);
                    return;
                }

                if (!empty($correoSol) && !filter_var($correoSol, FILTER_VALIDATE_EMAIL)) {
                    RespuestaHelper::error("La direcciÃ³n de correo electrÃ³nico del solicitante no tiene un formato vÃ¡lido.", 400);
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
                RespuestaHelper::error("Debe adjuntar al menos un documento digital vÃ¡lido (PDF, Excel o imagen) para radicar.", 400);
                return;
            }

            // Prefijo preventivo temporal para el nombrado de archivos antes de la asignaciÃ³n del consecutivo
            $prefijoTemp = "TEMP_" . date("YmdHis");
            $metadatosDocumentos = [];

            foreach ($listaArchivos as $archivoIndividual) {
                // ValidaciÃ³n estricta con ArchivoServicio (MIME binario real, lÃ­mite de peso, hash SHA-256)
                $metaDoc = ArchivoServicio::procesarArchivo($archivoIndividual, $prefijoTemp, "DOC");
                $archivosGuardadosTemporalmente[] = $metaDoc["ruta_archivo"];
                $metadatosDocumentos[] = $metaDoc;
            }

            // 4. Capturar clasificaciÃ³n CCD (Series y Subseries)
            $idSerie = !empty($post["serieDocumental"]) ? (int)$post["serieDocumental"] : (!empty($post["id_serie"]) ? (int)$post["id_serie"] : null);
            $idSubserie = !empty($post["subserieDocumental"]) ? (int)$post["subserieDocumental"] : (!empty($post["id_subserie"]) ? (int)$post["id_subserie"] : null);
            $codigoSerie = !empty($post["codigo_serie"]) ? trim($post["codigo_serie"]) : null;
            $codigoSubserie = !empty($post["codigo_subserie"]) ? trim($post["codigo_subserie"]) : null;

            if ($idSerie && !$codigoSerie) {
                $db = Conexion::conectar();
                $stmtS = $db->prepare("SELECT codigo_serie FROM ccd_series WHERE id_serie = :id");
                $stmtS->execute([":id" => $idSerie]);
                $codigoSerie = $stmtS->fetchColumn() ?: null;
            }
            if ($idSubserie && !$codigoSubserie) {
                $db = Conexion::conectar();
                $stmtSub = $db->prepare("SELECT codigo_subserie FROM ccd_subseries WHERE id_subserie = :id");
                $stmtSub->execute([":id" => $idSubserie]);
                $codigoSubserie = $stmtSub->fetchColumn() ?: null;
            }

            // 5. Estructurar datos para persistencia en base de datos
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
                "remitente"        => $remitenteNombre,
                "id_serie"         => $idSerie,
                "id_subserie"      => $idSubserie,
                "codigo_serie"     => $codigoSerie,
                "codigo_subserie"  => $codigoSubserie
            ];

            // 6. Persistencia atÃ³mica
            $resultado = ModeloRadicados::mdlRadicarDocumento($datosRadicado, $metadatosDocumentos, $datosRemitente);

            RespuestaHelper::exito(
                $resultado,
                "Documento radicado exitosamente con el nÃºmero " . $resultado["numero_radicado"],
                201
            );
        } catch (InvalidArgumentException $ie) {
            // Error de validaciÃ³n de negocio o de archivo (MIME, peso)
            self::limpiarArchivosTemporales($archivosGuardadosTemporalmente);
            RespuestaHelper::error($ie->getMessage(), 400);
        } catch (Exception $e) {
            // Error interno de base de datos o sistema
            self::limpiarArchivosTemporales($archivosGuardadosTemporalmente);
            error_log("Error en ControladorRadicados::ctrRadicar: " . $e->getMessage());
            RespuestaHelper::error("OcurriÃ³ un error interno al registrar la radicaciÃ³n: " . $e->getMessage(), 500);
        }
    }

    /**
     * Consulta y retorna el listado de radicados registrados segÃºn el perfil del usuario autenticado.
     * - Si el usuario es Administrador, visualiza todos los radicados de la entidad.
     * - Si es un usuario regular, visualiza exclusivamente los radicados bajo su responsabilidad.
     */
    public static function ctrListar(): void
    {
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            // 1. Obtener identificador y rol del usuario (desde sesiÃ³n activa o parÃ¡metros GET para tests)
            $idUsuario = isset($_SESSION["id"]) 
                            ? (int)$_SESSION["id"] 
                            : (isset($_GET["id_usuario"]) ? (int)$_GET["id_usuario"] : null);

            $rolSession = $_SESSION["rol"] ?? ($_GET["rol"] ?? null);

            // 2. Determinar si el usuario tiene privilegios de Administrador
            $esAdmin = false;

            if (isset($_GET["es_admin"])) {
                $esAdmin = ($_GET["es_admin"] === "1" || $_GET["es_admin"] === "true");
            } elseif ($rolSession !== null) {
                // ComprobaciÃ³n rÃ¡pida por ID conocido (1 = Administrador, 35 = Admin) o slug directo
                if ($rolSession == 1 || $rolSession == 35 || strtolower((string)$rolSession) === "admin") {
                    $esAdmin = true;
                } else {
                    // VerificaciÃ³n en base de datos del rol si es un id numÃ©rico
                    $db = Conexion::conectar();
                    $stmtRol = $db->prepare("SELECT rol, nombre FROM roles WHERE id_rol = :id_rol LIMIT 1");
                    $stmtRol->bindValue(":id_rol", (int)$rolSession, PDO::PARAM_INT);
                    $stmtRol->execute();
                    $rolData = $stmtRol->fetch(PDO::FETCH_ASSOC);
                    if ($rolData && (strtolower($rolData["rol"]) === "admin" || stripos($rolData["nombre"], "admin") !== false)) {
                        $esAdmin = true;
                    }
                }
            }

            // 3. Capturar filtros opcionales de bÃºsqueda
            $filtros = [];
            if (!empty($_GET["id_estado"])) {
                $filtros["id_estado"] = (int)$_GET["id_estado"];
            }
            if (!empty($_GET["tipo_radicado"])) {
                $filtros["tipo_radicado"] = trim($_GET["tipo_radicado"]);
            }
            if (!empty($_GET["fecha"])) {
                $filtros["fecha"] = trim($_GET["fecha"]);
            }

            // 4. Invocar modelo de persistencia con sentencias preparadas
            $radicados = ModeloRadicados::mdlListarRadicados($idUsuario, $esAdmin, $filtros);

            RespuestaHelper::exito($radicados, "Listado de radicados obtenido correctamente");
        } catch (Exception $e) {
            error_log("Error en ControladorRadicados::ctrListar: " . $e->getMessage());
            RespuestaHelper::error("Error al obtener la lista de radicados.", 500);
        }
    }

    /**
     * Consulta y retorna el detalle de un radicado especÃ­fico.
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
            RespuestaHelper::error("Error al obtener la informaciÃ³n del radicado.", 500);
        }
    }

    /**
     * Retorna los catÃ¡logos de soporte para el formulario de radicaciÃ³n.
     */
    public static function ctrCatalogos(): void
    {
        try {
            $catalogos = ModeloRadicados::mdlObtenerCatalogos();
            RespuestaHelper::exito($catalogos, "CatÃ¡logos de radicaciÃ³n obtenidos correctamente");
        } catch (Exception $e) {
            error_log("Error en ControladorRadicados::ctrCatalogos: " . $e->getMessage());
            RespuestaHelper::error("Error al cargar los catÃ¡logos del sistema.", 500);
        }
    }

    /**
     * Normaliza la estructura multidimensional o simple de $_FILES a una lista homogÃ©nea.
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

    /**
     * Obtiene el identificador del usuario autenticado en sesiÃ³n o por defecto 1.
     */
    private static function obtenerIdUsuarioSesion(): int
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return !empty($_SESSION["id"]) ? (int)$_SESSION["id"] : (!empty($_SESSION["id_usuario"]) ? (int)$_SESSION["id_usuario"] : 1);
    }

    /**
     * Asigna un usuario responsable a un radicado individual.
     */
    public static function ctrAsignar(int $id, array $body): void
    {
        try {
            $idUsuario = (int)($body["usuario"] ?? $body["id_usuario"] ?? 0);
            if ($idUsuario <= 0) {
                RespuestaHelper::error("Debe especificar un identificador de usuario vÃ¡lido para la asignaciÃ³n.", 400);
                return;
            }

            $idModificador = self::obtenerIdUsuarioSesion();
            $resultado = ModeloRadicados::mdlAsignarResponsable($id, $idUsuario, $idModificador);

            if ($resultado) {
                RespuestaHelper::exito([
                    "id_radicado" => $id,
                    "id_usuario"  => $idUsuario
                ], "Responsable asignado exitosamente al radicado #{$id}", 200);
            } else {
                RespuestaHelper::error("No fue posible asignar el responsable al radicado.", 500);
            }
        } catch (Exception $e) {
            error_log("Error en ControladorRadicados::ctrAsignar: " . $e->getMessage());
            RespuestaHelper::error("Error interno al procesar la asignaciÃ³n.", 500);
        }
    }

    /**
     * Cambia de forma atÃ³mica el estado de un radicado individual.
     */
    public static function ctrCambiarEstado(int $id, array $body): void
    {
        try {
            $idEstado = (int)($body["estado"] ?? $body["id_estado"] ?? 0);
            if ($idEstado <= 0) {
                RespuestaHelper::error("Debe seleccionar un estado vÃ¡lido para actualizar el radicado.", 400);
                return;
            }

            $comentario = trim($body["comentario"] ?? ($body["observacion"] ?? ""));
            $idModificador = self::obtenerIdUsuarioSesion();
            $resultado = ModeloRadicados::mdlCambiarEstadoRadicado($id, $idEstado, $comentario, $idModificador);

            if ($resultado) {
                RespuestaHelper::exito([
                    "id_radicado" => $id,
                    "id_estado"   => $idEstado
                ], "Estado del radicado #{$id} actualizado correctamente", 200);
            } else {
                RespuestaHelper::error("No fue posible actualizar el estado del radicado.", 500);
            }
        } catch (Exception $e) {
            error_log("Error en ControladorRadicados::ctrCambiarEstado: " . $e->getMessage());
            RespuestaHelper::error("Error interno al actualizar el estado.", 500);
        }
    }

    /**
     * Reasigna un radicado a un nuevo responsable y/o nueva dependencia.
     */
    public static function ctrReasignar(int $id, array $body): void
    {
        try {
            $idUsuarioNuevo = (int)($body["usuarioNuevo"] ?? $body["id_usuario"] ?? 0);
            if ($idUsuarioNuevo <= 0) {
                RespuestaHelper::error("Debe especificar el nuevo usuario responsable.", 400);
                return;
            }

            $idDepNueva = !empty($body["dependenciaNueva"]) ? (int)$body["dependenciaNueva"] : (!empty($body["id_dependencia"]) ? (int)$body["id_dependencia"] : null);
            $comentario = trim($body["observacion"] ?? ($body["comentario"] ?? ""));
            $idModificador = self::obtenerIdUsuarioSesion();

            $resultado = ModeloRadicados::mdlReasignarRadicado($id, $idUsuarioNuevo, $idDepNueva, $comentario, $idModificador);

            if ($resultado) {
                RespuestaHelper::exito([
                    "id_radicado"    => $id,
                    "id_usuario"     => $idUsuarioNuevo,
                    "id_dependencia" => $idDepNueva
                ], "Radicado #{$id} reasignado exitosamente", 200);
            } else {
                RespuestaHelper::error("No fue posible reasignar el radicado.", 500);
            }
        } catch (Exception $e) {
            error_log("Error en ControladorRadicados::ctrReasignar: " . $e->getMessage());
            RespuestaHelper::error("Error interno al procesar la reasignaciÃ³n.", 500);
        }
    }

    /**
     * Cierra o finaliza un radicado con observaciÃ³n formal.
     */
    public static function ctrCerrar(int $id, array $body): void
    {
        try {
            $observacion = trim($body["observacion"] ?? "Cierre del radicado desde la plataforma");
            $idCierre = self::obtenerIdUsuarioSesion();

            $resultado = ModeloRadicados::mdlCerrarRadicado($id, $observacion, $idCierre);

            if ($resultado) {
                RespuestaHelper::exito([
                    "id_radicado" => $id,
                    "cerrado"     => true
                ], "Radicado #{$id} finalizado y cerrado exitosamente", 200);
            } else {
                RespuestaHelper::error("No fue posible cerrar el radicado.", 500);
            }
        } catch (Exception $e) {
            error_log("Error en ControladorRadicados::ctrCerrar: " . $e->getMessage());
            RespuestaHelper::error("Error interno al cerrar el radicado.", 500);
        }
    }

    /**
     * Procesa acciones masivas (lote) sobre un conjunto de radicados seleccionados.
     */
    public static function ctrAccionLote(array $body): void
    {
        try {
            $accion = trim($body["accion"] ?? "");
            $ids = $body["ids"] ?? [];

            if (empty($accion) || !in_array($accion, ["asignar", "estado", "reasignar", "cerrar"], true)) {
                RespuestaHelper::error("AcciÃ³n en lote no vÃ¡lida. Acciones soportadas: asignar, estado, reasignar, cerrar.", 400);
                return;
            }

            if (!is_array($ids) || empty($ids)) {
                RespuestaHelper::error("Debe seleccionar al menos un radicado para ejecutar la acciÃ³n.", 400);
                return;
            }

            $idUsuario = self::obtenerIdUsuarioSesion();
            $resultado = ModeloRadicados::mdlAccionLote($accion, $ids, $body, $idUsuario);

            RespuestaHelper::exito($resultado, "OperaciÃ³n en lote procesada: {$resultado['exitosos']} exitoso(s), {$resultado['fallidos']} fallido(s)", 200);
        } catch (Exception $e) {
            error_log("Error en ControladorRadicados::ctrAccionLote: " . $e->getMessage());
            RespuestaHelper::error("Error interno al procesar la acciÃ³n por lotes.", 500);
        }
    }
}