<?php
require_once __DIR__ . '/../modelos/conexion.php';
try {
    $db = Conexion::conectar();
    echo "=== TRAMITES ===\n";
    $stmt = $db->query("SELECT id_tramite, nombre, id_dependencia_responsable, id_usuario_responsable FROM tramites");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

    echo "=== CATALOGOS RADICACION (dependencias) ===\n";
    require_once __DIR__ . '/../modelos/radicados.modelo.php';
    $cat = ModeloRadicados::mdlObtenerCatalogos();
    print_r($cat['dependencias']);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
