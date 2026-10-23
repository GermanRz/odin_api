<?php
require_once __DIR__ . '/../modelos/conexion.php';
$db = Conexion::conectar();
$stmt = $db->query("SELECT u.id_usuario, u.nombre, u.correo, u.estado, u.id_rol, r.nombre as rol, u.id_dependencia, d.nombre as dependencia FROM usuarios u LEFT JOIN roles r ON r.id_rol = u.id_rol LEFT JOIN dependencias d ON d.id_dependencia = u.id_dependencia");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
