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

  // Asegurar que los permisos RBAC estén cargados en la sesión activa
  if (!isset($_SESSION["permisos"]) && isset($_SESSION["rol"])) {
    ControladorRoles::ctrCargarPermisosSesion((int)$_SESSION["rol"]);
  }

  echo '<body class="hold-transition sidebar-mini">';
  echo '<!-- Site wrapper -->';
  echo '<div class="wrapper">';

    include "modulos/encabezado.php";
    include "modulos/menu.php";

    echo '<div class="content-wrapper">';

    if (isset($_GET["ruta"])) {

      $ruta = $_GET["ruta"];
      $rutasValidas = [
        "contacto",
        "inicio",
        "gestion_usuarios",
        "roles_permisos",
        "roles_permisosForm",
        "dependencias",
        "dependenciasForm",
        "radicacion",
        "consulta_seguimiento",
        "series_documentales",
        "subseries_documentales",
        "gestion_tramites",
        "reportes",
        "error403",
        "salir"
      ];

      // Mapa de protección de rutas por permisos granulares RBAC (RF-N13 / RF-N14)
      $permisosPorRuta = [
        "gestion_usuarios"       => "admin_usuarios",
        "roles_permisos"         => "admin_roles",
        "roles_permisosForm"     => "admin_roles",
        "dependencias"           => "ver_dependencias",
        "dependenciasForm"       => "ver_dependencias",
        "radicacion"             => ["crear_radicado", "editar_radicado", "ver_radicados"],
        "consulta_seguimiento"   => "ver_radicados",
        "series_documentales"    => "admin_ccd",
        "subseries_documentales" => "admin_ccd",
        "gestion_tramites"       => ["ver_tramites", "gestionar_tramites", "asignar_tramites", "trasladar_radicado", "finalizar_radicado"],
        "reportes"               => "ver_reportes"
      ];

      if (in_array($ruta, $rutasValidas, true)) {
        if (isset($permisosPorRuta[$ruta]) && !ControladorRoles::ctrTienePermiso($permisosPorRuta[$ruta])) {
          include "modulos/error403.php";
        } else {
          include "modulos/" . $ruta . ".php";
        }
      } else {
        include "modulos/error404.php";
      }

    } else {
      include "modulos/inicio.php";
    }

    echo '</div>';

    include "modulos/footer.php";

  echo '</div>';
  echo '<!-- ./wrapper -->';

} else {

  echo '<body class="hold-transition login-page">';
  include "modulos/login.php";

}
?>

</body>

</html>
