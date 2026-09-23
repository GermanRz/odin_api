<?php


class ControladorRoles {

    public static function ctrMostrarRoles() {
        $tabla = "roles";
        $respuesta = ModeloRoles::mdlMostrarRoles($tabla);
        return $respuesta;
    } // End of ctrMostrarRoles

} // End of class ControladorRoles