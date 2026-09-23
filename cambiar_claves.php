<?php
// Script protegido para mantenimiento exclusivo del desarrollador
if (php_sapi_name() !== 'cli' && (!isset($_GET['confirmar']) || $_GET['confirmar'] !== 'si')) {
    die("<div style='font-family: Arial; padding: 20px; color: #721c24; background: #f8d7da; max-width: 600px; margin: 40px auto; border-radius: 5px;'>
        <h3>Acceso Restringido</h3>
        <p>Este script de mantenimiento modifica todas las contraseñas de la base de datos. Para ejecutarlo desde el navegador, agregue <code>?confirmar=si</code> a la URL o ejecútelo desde la consola.</p>
        <a href='index.php'>Volver al inicio</a>
    </div>");
}

require_once "modelos/conexion.php";

try {
    $conexion = Conexion::conectar();
    
    // Actualizar todas las contraseñas con hash seguro compatible con password_verify
    $nuevaClave = "clave123";
    $hashClave = password_hash($nuevaClave, PASSWORD_DEFAULT);
    $stmt = $conexion->prepare("UPDATE usuarios SET password = :password");
    $stmt->bindParam(":password", $hashClave, PDO::PARAM_STR);

    if ($stmt->execute()) {
        $filas = $stmt->rowCount();
        echo "<div style='font-family: Arial, sans-serif; padding: 20px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 5px; max-width: 600px; margin: 50px auto;'>";
        echo "<h2>¡Éxito!</h2>";
        echo "<p>Se actualizaron <strong>$filas</strong> usuarios con hash seguro (contraseña: <code>clave123</code>)</p>";
        echo "<a href='index.php' style='color: #155724; font-weight: bold;'>Volver al Login</a>";
        echo "</div>";
    } else {
        echo "<div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; max-width: 600px; margin: 50px auto;'>";
        echo "<h2>Error</h2>";
        echo "<p>No se pudo ejecutar la actualización en la base de datos.</p>";
        echo "</div>";
    }

} catch (Exception $e) {
    echo "<div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; max-width: 600px; margin: 50px auto;'>";
    echo "<h2>Error de Conexión</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
