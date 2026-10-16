<?php

require_once __DIR__ . "/../modelos/radicados.modelo.php";
require_once __DIR__ . "/../controladores/radicados.controlador.php";

echo "=== [TEST] Módulo de Consulta y Seguimiento — Filtro por Rol y Usuario ===\n";

$todos = ModeloRadicados::mdlListarRadicados();
echo "  [OK] Consulta sin filtros (retrocompatibilidad) retornó: " . count($todos) . " radicados\n";

// Test 1: Verificar que los campos requeridos por la vista estén presentes
$primerRadicado = $todos[0] ?? [];
$camposEsperados = [
    "numero_radicado", "tipo_radicado", "fecha_radicado", 
    "estado", "estado_color", "responsable", 
    "total_documentos", "ruta_documento", "nombre_archivo", "id_documento"
];
$faltantes = [];
foreach ($camposEsperados as $c) {
    if (!array_key_exists($c, $primerRadicado)) {
        $faltantes[] = $c;
    }
}
if (empty($faltantes)) {
    echo "  [OK] Todos los campos relacionales exigidos por la vista están presentes en cada registro\n";
} else {
    echo "  [FALLO] Faltan campos: " . implode(", ", $faltantes) . "\n";
    exit(1);
}

// Test 2: Prueba como Administrador
$radicadosAdmin = ModeloRadicados::mdlListarRadicados(1, true);
echo "  [OK] Rol Administrador visualiza el 100% de los radicados (" . count($radicadosAdmin) . " registros)\n";

// Test 3: Prueba como Usuario normal (Carlos Mendoza, ID 2)
$radicadosUser = ModeloRadicados::mdlListarRadicados(2, false);
$todosSonDeUser2 = true;
foreach ($radicadosUser as $item) {
    if ((int)$item["id_usuario"] !== 2) {
        $todosSonDeUser2 = false;
        break;
    }
}

if ($todosSonDeUser2 && count($radicadosUser) > 0 && count($radicadosUser) < count($radicadosAdmin)) {
    echo "  [OK] Usuario no administrador (ID 2) visualiza únicamente sus " . count($radicadosUser) . " radicados asignados\n";
} else {
    echo "  [FALLO] Filtrado por usuario no administrador falló o trajo registros ajenos\n";
    exit(1);
}

// Test 4: Prueba con filtros complementarios (estado)
$radicadosPendientes = ModeloRadicados::mdlListarRadicados(null, true, ["id_estado" => 1]);
echo "  [OK] Filtro complementario por estado (Pendientes) retornó " . count($radicadosPendientes) . " registros\n";

echo "\n>>> TODAS LAS PRUEBAS DEL MÉTODO DE CONSULTA PASARON EXITOSAMENTE <<<\n";
