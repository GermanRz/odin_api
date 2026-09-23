<?php

require_once "../controladores/usuarios.controlador.php";
require_once "../modelos/usuarios.modelo.php";

class AjaxUsuarios {

    public $idUsuario;
    public $estadoUsuario;

    public function ajaxActivarUsuario() {
        $estadoUsuario = $this->estadoUsuario;
        $idUsuario = $this->idUsuario;
        $respuesta = ControladorUsuarios::ctrActivarUsuario($estadoUsuario, $idUsuario);       
        echo json_encode($respuesta);
    }

    public function ajaxMostrarUsuario() {
        $campo = "id_usuario";
        $valor = $this->idUsuario;
        $respuesta = ControladorUsuarios::ctrMostrarUsuario( $campo, $valor );       
        echo json_encode($respuesta);
    }

} // End of class AjaxUsuarios


if(isset($_POST["idUsuario"])) {
    $activarUsuario = new AjaxUsuarios();
    $activarUsuario->idUsuario = $_POST["idUsuario"];
    $activarUsuario->estadoUsuario = $_POST["nuevoEstadoUsuario"];
    $activarUsuario->ajaxActivarUsuario();
}

if(isset($_POST["idUsuarioEditar"])) {
    $mostrarUsuario = new AjaxUsuarios();
    $mostrarUsuario->idUsuario = $_POST["idUsuarioEditar"];
    $mostrarUsuario->ajaxMostrarUsuario();
}
