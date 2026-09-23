<?php

require_once "conexion.php";

class ModeloDependencias {

    public static function mdlMostrarDependencias($tabla) {

        $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla");

        $stmt->execute();
        return $stmt->fetchAll();

    } // End of mdlMostrarDependencias

} // End of class ModeloDependencias