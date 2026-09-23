<?php
require_once dirname(__DIR__) . "/modelos/conexion.php";
$hash = password_hash('clave123', PASSWORD_DEFAULT);
$stmt = Conexion::conectar()->prepare("UPDATE usuarios SET password = :password");
$stmt->bindParam(":password", $hash, PDO::PARAM_STR);
if ($stmt->execute()) {
    echo "SUCCESS: All passwords updated to encrypted 'clave123'\n";
    echo "Hash used: " . $hash . "\n";
} else {
    echo "ERROR: Failed to update passwords\n";
}