<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "controladores/plantilla.controlador.php";

require_once "controladores/usuarios.controlador.php";
require_once "controladores/roles.controlador.php";
require_once "controladores/dependencias.controlador.php";

require_once "modelos/usuarios.modelo.php";
require_once "modelos/roles.modelo.php";
require_once "modelos/dependencias.modelo.php";




$plantilla = new ControladorPlantilla();
$plantilla->ctrTraerPlantilla();