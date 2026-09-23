<?php

require_once "conexion.php";

class ModeloRoles {

    public static function mdlMostrarRoles($tabla) {

        $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla");

        $stmt->execute();
        return $stmt->fetchAll();

    } // End of mdlMostrarRoles

} // End of class ModeloRoles