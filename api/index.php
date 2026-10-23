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
require_once __DIR__ . "/../modelos/tramites.modelo.php";
require_once __DIR__ . "/../modelos/radicados.modelo.php";

require_once __DIR__ . "/../controladores/usuarios.controlador.php";
require_once __DIR__ . "/../controladores/roles.controlador.php";
require_once __DIR__ . "/../controladores/dependencias.controlador.php";
require_once __DIR__ . "/../controladores/tramites.controlador.php";
require_once __DIR__ . "/../controladores/radicados.controlador.php";

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

    // GET /api/radicados/{id} -> Detalle de radicado con documentos
    if ($metodo === "GET" && is_numeric($param1)) {
        ControladorRadicados::ctrObtener((int)$param1);
    }

    // POST /api/radicados/lote -> Acciones masivas sobre múltiples radicados
    if ($metodo === "POST" && $param1 === "lote") {
        ControladorRadicados::ctrAccionLote($body);
    }

    // POST /api/radicados -> Radicar documento con adjuntos
    if ($metodo === "POST" && $param1 === null) {
        $payload = !empty($_POST) ? $_POST : $body;
        ControladorRadicados::ctrRadicar($payload, $_FILES);
    }

    // PATCH /api/radicados/{id}/asignar -> Asignar responsable individual
    if ($metodo === "PATCH" && is_numeric($param1) && $param2 === "asignar") {
        ControladorRadicados::ctrAsignar((int)$param1, $body);
    }

    // PATCH /api/radicados/{id}/estado -> Cambiar estado individual
    if ($metodo === "PATCH" && is_numeric($param1) && $param2 === "estado") {
        ControladorRadicados::ctrCambiarEstado((int)$param1, $body);
    }

    // POST /api/radicados/{id}/reasignar -> Reasignar usuario y dependencia
    if ($metodo === "POST" && is_numeric($param1) && $param2 === "reasignar") {
        ControladorRadicados::ctrReasignar((int)$param1, $body);
    }

    // PATCH /api/radicados/{id}/cerrar -> Cerrar y finalizar radicado
    if ($metodo === "PATCH" && is_numeric($param1) && $param2 === "cerrar") {
        ControladorRadicados::ctrCerrar((int)$param1, $body);
    }

    RespuestaHelper::error("Método no permitido para este recurso", 405);
}

/*=============================================
RECURSO: TRAMITES (/api/tramites)
=============================================*/
if ($recurso === "tramites") {

    // GET /api/tramites/stats -> Métricas e indicadores cuantitativos
    if ($metodo === "GET" && $param1 === "stats") {
        ControladorTramites::ctrEstadisticas();
    }

    // GET /api/tramites -> Listado completo del catálogo
    if ($metodo === "GET" && $param1 === null) {
        ControladorTramites::ctrListar();
    }

    // GET /api/tramites/{id} -> Detalle de un trámite
    if ($metodo === "GET" && is_numeric($param1)) {
        ControladorTramites::ctrObtener((int)$param1);
    }

    // POST /api/tramites -> Crear nuevo tipo de trámite
    if ($metodo === "POST" && $param1 === null) {
        ControladorTramites::ctrCrear($body);
    }

    // PUT /api/tramites/{id} -> Actualizar trámite
    if ($metodo === "PUT" && is_numeric($param1) && $param2 === null) {
        ControladorTramites::ctrActualizar((int)$param1, $body);
    }

    // PATCH /api/tramites/{id}/estado -> Alternar activo / inactivo
    if ($metodo === "PATCH" && is_numeric($param1) && $param2 === "estado") {
        ControladorTramites::ctrCambiarEstado((int)$param1, $body);
    }

    RespuestaHelper::error("Método no permitido para este recurso", 405);
}

/*=============================================
RECURSOS AUXILIARES: ROLES, DEPENDENCIAS, ESTADOS, SERIES, SUBSERIES
=============================================*/
if ($recurso === "roles" && $metodo === "GET") {
    $roles = ModeloRoles::mdlMostrarRoles("roles");
    RespuestaHelper::exito($roles, "Listado de roles obtenido correctamente");
}

if ($recurso === "dependencias" && $metodo === "GET") {
    $dependencias = ModeloDependencias::mdlMostrarDependencias("dependencias");
    RespuestaHelper::exito($dependencias, "Listado de dependencias obtenido correctamente");
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
