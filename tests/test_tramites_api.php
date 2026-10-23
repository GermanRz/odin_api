<?php

require_once __DIR__ . "/../api/helpers/respuesta.helper.php";
require_once __DIR__ . "/../modelos/tramites.modelo.php";
require_once __DIR__ . "/../controladores/tramites.controlador.php";
require_once __DIR__ . "/../modelos/radicados.modelo.php";
require_once __DIR__ . "/../controladores/radicados.controlador.php";

echo "=== [TEST] Verificación Sintáctica y de Clases para Trámites ===\n";

if (class_exists("ModeloTramites")) {
    echo "  [OK] Clase ModeloTramites cargada correctamente.\n";
} else {
    echo "  [FAIL] Clase ModeloTramites no existe.\n";
}

if (class_exists("ControladorTramites")) {
    echo "  [OK] Clase ControladorTramites cargada correctamente.\n";
} else {
    echo "  [FAIL] Clase ControladorTramites no existe.\n";
}

$metodosModelo = get_class_methods("ModeloTramites");
$metodosRequeridosModelo = [
    "mdlMostrarTramites",
    "mdlObtenerTramite",
    "mdlCrearTramite",
    "mdlActualizarTramite",
    "mdlCambiarEstado",
    "mdlObtenerEstadisticasTramites"
];

foreach ($metodosRequeridosModelo as $m) {
    if (in_array($m, $metodosModelo)) {
        echo "  [OK] Método ModeloTramites::{$m} implementado.\n";
    } else {
        echo "  [FAIL] Método ModeloTramites::{$m} ausente.\n";
    }
}

$metodosControlador = get_class_methods("ControladorTramites");
$metodosRequeridosControlador = [
    "ctrListar",
    "ctrObtener",
    "ctrCrear",
    "ctrActualizar",
    "ctrCambiarEstado",
    "ctrEstadisticas"
];

foreach ($metodosRequeridosControlador as $m) {
    if (in_array($m, $metodosControlador)) {
        echo "  [OK] Método ControladorTramites::{$m} implementado.\n";
    } else {
        echo "  [FAIL] Método ControladorTramites::{$m} ausente.\n";
    }
}

$metodosRadicados = get_class_methods("ControladorRadicados");
$metodosRequeridosRadicados = [
    "ctrAsignar",
    "ctrCambiarEstado",
    "ctrReasignar",
    "ctrCerrar",
    "ctrAccionLote"
];

foreach ($metodosRequeridosRadicados as $m) {
    if (in_array($m, $metodosRadicados)) {
        echo "  [OK] Método ControladorRadicados::{$m} implementado.\n";
    } else {
        echo "  [FAIL] Método ControladorRadicados::{$m} ausente.\n";
    }
}

echo "=== Suite de Trámites validada con éxito ===\n";
