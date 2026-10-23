<?php

/**
 * Router Principal y Front Controller de la API RESTful de ODIN.
 * 
 * Gestiona peticiones HTTP, cabeceras CORS, análisis semántico de rutas
 * y despacho desacoplado hacia los controladores del sistema.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Clases auxiliares y dependencias
require_once __DIR__ . "/helpers/respuesta.helper.php";
require_once __DIR__ . "/../modelos/conexion.php";
require_once __DIR__ . "/../modelos/usuarios.modelo.php";
require_once __DIR__ . "/../modelos/roles.modelo.php";
require_once __DIR__ . "/../modelos/dependencias.modelo.php";
require_once __DIR__ . "/../modelos/radicados.modelo.php";
require_once __DIR__ . "/../modelos/seguimiento.modelo.php";

require_once __DIR__ . "/../controladores/usuarios.controlador.php";
require_once __DIR__ . "/../controladores/roles.controlador.php";
require_once __DIR__ . "/../controladores/dependencias.controlador.php";
require_once __DIR__ . "/../controladores/radicados.controlador.php";
require_once __DIR__ . "/../controladores/seguimiento.controlador.php";

// 2. Cabeceras CORS y configuración HTTP
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Respuesta inmediata a peticiones pre-flight de CORS
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// 3. Captura del método HTTP y fragmentos de ruta
$metodo = $_SERVER["REQUEST_METHOD"];
$ruta = $_GET["ruta"] ?? "";
$partesRuta = array_values(array_filter(explode("/", trim($ruta, "/")), function($segmento) {
    return $segmento !== "";
}));

$recurso = $partesRuta[0] ?? "";
$param1  = $partesRuta[1] ?? null;
$param2  = $partesRuta[2] ?? null;

// 4. Captura del payload de entrada (JSON o formulario tradicional)
$inputRaw = file_get_contents("php://input");
$body = json_decode($inputRaw, true);

if (!is_array($body) && !empty($_POST)) {
    $body = $_POST;
}
if (!is_array($body)) {
    $body = [];
}

// 5. Enrutamiento por recurso

/*=============================================
RECURSO: USUARIOS (/api/usuarios)
=============================================*/
if ($recurso === "usuarios") {

    // GET /api/usuarios/stats -> Estadísticas para tarjetas
    if ($metodo === "GET" && $param1 === "stats") {
        ControladorUsuarios::ctrEstadisticas();
    }

    // GET /api/usuarios -> Listado general
    if ($metodo === "GET" && $param1 === null) {
        ControladorUsuarios::ctrListar();
    }

    // GET /api/usuarios/{id} -> Detalle de usuario
    if ($metodo === "GET" && is_numeric($param1)) {
        ControladorUsuarios::ctrObtener((int)$param1);
    }

    // POST /api/usuarios -> Crear nuevo usuario
    if ($metodo === "POST" && $param1 === null) {
        ControladorUsuarios::ctrCrear($body);
    }

    // PUT /api/usuarios/{id} -> Actualizar usuario
    if ($metodo === "PUT" && is_numeric($param1) && $param2 === null) {
        ControladorUsuarios::ctrActualizar((int)$param1, $body);
    }

    // PATCH /api/usuarios/{id}/estado -> Cambio atómico de estado
    if ($metodo === "PATCH" && is_numeric($param1) && $param2 === "estado") {
        $nuevoEstado = $body["estado"] ?? "";
        ControladorUsuarios::ctrCambiarEstado((int)$param1, $nuevoEstado);
    }

    RespuestaHelper::error("Método no permitido para este recurso", 405);
}

/*=============================================
RECURSO: RADICADOS (/api/radicados)
=============================================*/
if ($recurso === "radicados") {

    // GET /api/radicados/catalogos -> Listas desplegables
    if ($metodo === "GET" && $param1 === "catalogos") {
        ControladorRadicados::ctrCatalogos();
    }

    // GET /api/radicados -> Listado general
    if ($metodo === "GET" && $param1 === null) {
        ControladorRadicados::ctrListar();
    }

    // GET /api/radicados/remitente/{documento} -> Buscar remitente por documento
    if ($metodo === "GET" && $param1 === "remitente" && $param2 !== null) {
        $tipo = $_GET["tipo"] ?? null;
        ControladorRadicados::ctrBuscarRemitente($param2, $tipo);
    }

    // GET /api/radicados/{id} -> Detalle de radicado con documentos
    if ($metodo === "GET" && is_numeric($param1) && $param2 === null) {
        ControladorRadicados::ctrObtener((int)$param1);
    }

    // GET /api/radicados/{id}/timeline (o traza)
    if ($metodo === "GET" && is_numeric($param1) && in_array($param2, ["timeline", "traza", "historial"], true)) {
        ControladorSeguimiento::ctrObtenerTrazabilidad((int)$param1);
    }

    // GET /api/radicados/{id}/descargar -> Descargar archivo digital
    if ($metodo === "GET" && is_numeric($param1) && $param2 === "descargar") {
        ControladorSeguimiento::ctrDescargar((int)$param1);
    }

    // POST /api/radicados/{id}/traza (o movimiento)
    if ($metodo === "POST" && is_numeric($param1) && in_array($param2, ["traza", "movimiento", "transicion"], true)) {
        ControladorSeguimiento::ctrRegistrarMovimiento((int)$param1, $body);
    }

    // POST /api/radicados -> Radicar documento con adjuntos
    if ($metodo === "POST" && $param1 === null) {
        $payload = !empty($_POST) ? $_POST : $body;
        ControladorRadicados::ctrRadicar($payload, $_FILES);
    }

    RespuestaHelper::error("Método no permitido para este recurso", 405);
}

/*=============================================
<<<<<<< HEAD
RECURSO: REMITENTES (/api/remitentes)
=============================================*/
if ($recurso === "remitentes") {

    // GET /api/remitentes/{documento} -> Buscar remitente externo por número de documento
    if ($metodo === "GET" && $param1 !== null) {
        $tipo = $_GET["tipo"] ?? null;
        ControladorRadicados::ctrBuscarRemitente($param1, $tipo);
=======
RECURSO: SEGUIMIENTO Y TRAZABILIDAD (/api/seguimiento)
=============================================*/
if ($recurso === "seguimiento") {

    // GET /api/seguimiento/catalogos -> Listas desplegables para filtros
    if ($metodo === "GET" && $param1 === "catalogos") {
        ControladorSeguimiento::ctrCatalogos();
    }

    // GET /api/seguimiento/publico/{codigo} -> Consulta ciudadana y radicación anónima
    if ($metodo === "GET" && $param1 === "publico" && !empty($param2)) {
        ControladorSeguimiento::ctrConsultaPublica($param2);
    }

    // GET /api/seguimiento -> Consulta con filtros avanzados AGN y semáforo RF-30
    if ($metodo === "GET" && $param1 === null) {
        ControladorSeguimiento::ctrListar();
    }

    // GET /api/seguimiento/{id} -> Detalle completo del radicado
    if ($metodo === "GET" && is_numeric($param1) && $param2 === null) {
        ControladorSeguimiento::ctrObtener((int)$param1);
    }

    // GET /api/seguimiento/{id}/timeline (o traza / historial) -> Línea de tiempo
    if ($metodo === "GET" && is_numeric($param1) && in_array($param2, ["timeline", "traza", "historial"], true)) {
        ControladorSeguimiento::ctrObtenerTrazabilidad((int)$param1);
    }

    // GET /api/seguimiento/{id}/descargar -> Descargar archivo digital
    if ($metodo === "GET" && is_numeric($param1) && $param2 === "descargar") {
        ControladorSeguimiento::ctrDescargar((int)$param1);
    }

    // POST /api/seguimiento/{id}/movimiento (o traza / transicion) -> Registrar hito
    if ($metodo === "POST" && is_numeric($param1) && in_array($param2, ["movimiento", "traza", "transicion"], true)) {
        ControladorSeguimiento::ctrRegistrarMovimiento((int)$param1, $body);
>>>>>>> 4daa95a (refactor(seguimiento): simplificar vista a tabla directa con filtro en vista y acceso a metadatos desde historial)
    }

    RespuestaHelper::error("Método no permitido para este recurso", 405);
}

/*=============================================
<<<<<<< HEAD
RECURSOS AUXILIARES: ROLES, DEPENDENCIAS, TRAMITES, ESTADOS
=======
RECURSOS AUXILIARES: ROLES, DEPENDENCIAS, TRAMITES, ESTADOS, SERIES, SUBSERIES
>>>>>>> 4daa95a (refactor(seguimiento): simplificar vista a tabla directa con filtro en vista y acceso a metadatos desde historial)
=============================================*/
if ($recurso === "roles" && $metodo === "GET") {
    $roles = ModeloRoles::mdlMostrarRoles("roles");
    RespuestaHelper::exito($roles, "Listado de roles obtenido correctamente");
}

if ($recurso === "dependencias" && $metodo === "GET") {
    // GET /api/dependencias/{id}/responsable -> Encargado del área de la dependencia
    if (is_numeric($param1) && $param2 === "responsable") {
        ControladorRadicados::ctrObtenerResponsableDependencia((int)$param1);
    }

    $dependencias = ModeloDependencias::mdlMostrarDependencias("dependencias");
    RespuestaHelper::exito($dependencias, "Listado de dependencias obtenido correctamente");
}

if ($recurso === "tramites" && $metodo === "GET") {
    $catalogos = ModeloRadicados::mdlObtenerCatalogos();
    RespuestaHelper::exito($catalogos["tramites"], "Listado de trámites obtenido correctamente");
}

if ($recurso === "estados" && $metodo === "GET") {
    $catalogos = ModeloRadicados::mdlObtenerCatalogos();
    RespuestaHelper::exito($catalogos["estados"], "Listado de estados obtenido correctamente");
}

if ($recurso === "series" && $metodo === "GET") {
    $catalogos = ModeloRadicados::mdlObtenerCatalogos();
    RespuestaHelper::exito($catalogos["series"], "Listado de series documentales obtenido correctamente");
}

if ($recurso === "subseries" && $metodo === "GET") {
    $catalogos = ModeloRadicados::mdlObtenerCatalogos();
    $subseries = $catalogos["subseries"];
    if (is_numeric($param1)) {
        $subseries = array_values(array_filter($subseries, function($s) use ($param1) {
            return (int)$s["id_serie"] === (int)$param1;
        }));
    }
    RespuestaHelper::exito($subseries, "Listado de subseries documentales obtenido correctamente");
}

// 6. Si ningún recurso coincide
RespuestaHelper::error("Endpoint no encontrado", 404);
