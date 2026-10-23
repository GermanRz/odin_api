<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sistema ODIN</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="vistas/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="vistas/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="vistas/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="vistas/plugins/fontawesome-free/css/all.min.css">

  <!-- SweetAlert -->
  <link rel="stylesheet" href="vistas/plugins/sweetalert2/sweetalert2.min.css">
  <link rel="stylesheet" href="vistas/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">

  <!-- Theme style -->
  <link rel="stylesheet" href="vistas/dist/css/adminlte.min.css">

  <!-- jQuery -->
  <script src="vistas/plugins/jquery/jquery.min.js"></script>
  <!-- Bootstrap 4 -->
  <script src="vistas/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- AdminLTE App -->
  <script src="vistas/dist/js/adminlte.min.js"></script>
  <script src="vistas/plugins/datatables/jquery.dataTables.min.js"></script>
  <script src="vistas/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
  <script src="vistas/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
  <script src="vistas/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
  <script src="vistas/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
  <script src="vistas/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
  <script src="vistas/plugins/jszip/jszip.min.js"></script>
  <script src="vistas/plugins/pdfmake/pdfmake.min.js"></script>
  <script src="vistas/plugins/pdfmake/vfs_fonts.js"></script>
  <script src="vistas/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
  <script src="vistas/plugins/datatables-buttons/js/buttons.print.min.js"></script>
  <script src="vistas/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>
  <!-- SweetAlert2 para notificaciones -->
  <script src="vistas/plugins/sweetalert2/sweetalert2.min.js"></script>

</head>

<?php
if (isset($_SESSION["iniciarSesion"]) && $_SESSION["iniciarSesion"] == "ok") {

  echo '<body class="hold-transition sidebar-mini">';
  echo '<!-- Site wrapper -->';
  echo '<div class="wrapper">';

    include "modulos/encabezado.php";
    include "modulos/menu.php";

    echo '<div class="content-wrapper">';

    // Verificación de seguridad: Detectar si la clave activa es igual al número de cédula
    if (!isset($_SESSION["clavePorDefecto"]) && isset($_SESSION["id"])) {
        $uSesionActiva = ModeloUsuarios::mdlMostrarUsuario("id_usuario", $_SESSION["id"]);
        if ($uSesionActiva) {
            $_SESSION["clavePorDefecto"] = password_verify((string)$uSesionActiva["num_identificacion"], (string)$uSesionActiva["password"]);
        }
    }

    if (!empty($_SESSION["clavePorDefecto"])) {
        include "modulos/alerta_clave_defecto.php";
    }

    if (isset($_GET["ruta"])) {

      if (
        $_GET["ruta"] == "contacto" ||
        $_GET["ruta"] == "inicio" ||
        $_GET["ruta"] == "gestion_usuarios" ||
        $_GET["ruta"] == "roles_permisos" ||
        $_GET["ruta"] == "dependencias" ||
        $_GET["ruta"] == "radicacion" ||
        $_GET["ruta"] == "consulta_seguimiento" ||
        $_GET["ruta"] == "series_documentales" ||
        $_GET["ruta"] == "subseries_documentales" ||
        $_GET["ruta"] == "gestion_tramites" ||
        $_GET["ruta"] == "reportes" ||
        $_GET["ruta"] == "roles_permisosForm" ||
        $_GET["ruta"] == "dependenciasForm" ||
        $_GET["ruta"] == "salir"
      ) {
        include "modulos/" . $_GET["ruta"] . ".php";
      } else {
        include "modulos/error404.php";
      }

    } else {
      include "modulos/inicio.php";
    }

    echo '</div>';

    include "modulos/footer.php";
    include "modulos/perfil_modal.php";

  echo '</div>';
  echo '<!-- ./wrapper -->';
  echo '<script src="vistas/js/perfil.js"></script>';

} else {

  echo '<body class="hold-transition login-page">';
  include "modulos/login.php";

}
?>

</body>

</html>