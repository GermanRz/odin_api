<?php
$permisoUsuarios     = ControladorRoles::ctrTienePermiso("admin_usuarios");
$permisoRoles        = ControladorRoles::ctrTienePermiso("admin_roles");
$permisoDependencias = ControladorRoles::ctrTienePermiso("ver_dependencias");
$permisoRadicar      = ControladorRoles::ctrTienePermiso(["crear_radicado", "editar_radicado", "ver_radicados"]);
$permisoConsulta     = ControladorRoles::ctrTienePermiso("ver_radicados");
$permisoCcd          = ControladorRoles::ctrTienePermiso("admin_ccd");
$permisoTramites     = ControladorRoles::ctrTienePermiso(["ver_tramites", "gestionar_tramites", "asignar_tramites", "trasladar_radicado", "finalizar_radicado"]);
$permisoReportes     = ControladorRoles::ctrTienePermiso("ver_reportes");
$rutaActual          = $_GET["ruta"] ?? "inicio";
?>
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <!-- Brand Logo -->
      <a href="inicio" class="brand-link">
          <img src="vistas/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
          <span class="brand-text font-weight-light">Odin</span>
      </a>

      <!-- Sidebar -->
      <div class="sidebar">
          <!-- Sidebar user (optional) -->
<div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center" id="btnPerfilSidebar" style="cursor: pointer;" title="Haga clic para ver o editar su perfil" data-toggle="modal" data-target="#modal-perfilUsuario">
              <div class="image">
                  <img src="vistas/dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="Foto de perfil">
              </div>
              <div class="info">
                  <a href="javascript:void(0)" class="d-block" id="perfilNombreSidebar" data-toggle="modal" data-target="#modal-perfilUsuario"><?php echo isset($_SESSION["nombre"]) ? htmlspecialchars($_SESSION["nombre"]) : "Usuario"; ?></a>
                  <span class="badge badge-success"><?php echo isset($_SESSION["nombre_rol"]) ? htmlspecialchars($_SESSION["nombre_rol"]) : "Activo"; ?></span>
              </div>
          </div>

          <!-- Sidebar Menu -->
          <nav class="mt-2">
              <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                  <!-- ******************************* INICIO ******************************* -->
                  <li class="nav-item">
                      <a href="inicio" class="nav-link <?php echo ($rutaActual === 'inicio') ? 'active' : ''; ?>">
                          <i class="nav-icon fas fa-home"></i>
                          <p>Inicio</p>
                      </a>
                  </li>

                  <!-- ******************************* CONTROL DE ACCESO ******************************* -->
                  <?php if ($permisoUsuarios || $permisoRoles) : ?>
                  <li class="nav-item <?php echo in_array($rutaActual, ['gestion_usuarios', 'roles_permisos', 'roles_permisosForm'], true) ? 'menu-open' : ''; ?>">
                      <a href="#" class="nav-link <?php echo in_array($rutaActual, ['gestion_usuarios', 'roles_permisos', 'roles_permisosForm'], true) ? 'active' : ''; ?>">
                          <i class="nav-icon fas fa-users"></i>
                          <p>
                              Control de Acceso
                              <i class="fas fa-angle-left right"></i>
                          </p>
                      </a>
                      <ul class="nav nav-treeview">
                          <?php if ($permisoUsuarios) : ?>
                          <li class="nav-item">
                              <a href="gestion_usuarios" class="nav-link <?php echo ($rutaActual === 'gestion_usuarios') ? 'active' : ''; ?>">
                                  <i class="far fa-circle nav-icon"></i>
                                  <p>Gestión de usuarios</p>
                              </a>
                          </li>
                          <?php endif; ?>

                          <?php if ($permisoRoles) : ?>
                          <li class="nav-item">
                              <a href="roles_permisos" class="nav-link <?php echo in_array($rutaActual, ['roles_permisos', 'roles_permisosForm'], true) ? 'active' : ''; ?>">
                                  <i class="far fa-circle nav-icon"></i>
                                  <p>Roles y permisos</p>
                              </a>
                          </li>
                          <?php endif; ?>
                      </ul>
                  </li>
                  <?php endif; ?>

                  <!-- ******************************* DEPENDENCIAS ******************************* -->
                  <?php if ($permisoDependencias) : ?>
                  <li class="nav-item">
                      <a href="dependencias" class="nav-link <?php echo in_array($rutaActual, ['dependencias', 'dependenciasForm'], true) ? 'active' : ''; ?>">
                          <i class="nav-icon fas fa-building"></i>
                          <p>Dependencias</p>
                      </a>
                  </li>
                  <?php endif; ?>

                  <!-- ******************************* RADICACION ******************************* -->
                  <?php if ($permisoRadicar) : ?>
                  <li class="nav-item">
                      <a href="radicacion" class="nav-link <?php echo ($rutaActual === 'radicacion') ? 'active' : ''; ?>">
                          <i class="nav-icon fas fa-file"></i>
                          <p>Radicación</p>
                      </a>
                  </li>
                  <?php endif; ?>

                  <!-- ******************************* GESTION DOCUMENTAL ******************************* -->
                  <?php if ($permisoConsulta || $permisoCcd) : ?>
                  <li class="nav-item <?php echo in_array($rutaActual, ['consulta_seguimiento', 'series_documentales', 'subseries_documentales'], true) ? 'menu-open' : ''; ?>">
                      <a href="#" class="nav-link <?php echo in_array($rutaActual, ['consulta_seguimiento', 'series_documentales', 'subseries_documentales'], true) ? 'active' : ''; ?>">
                          <i class="nav-icon fas fa-eye"></i>
                          <p>
                              Gestión documental
                              <i class="fas fa-angle-left right"></i>
                          </p>
                      </a>

                      <ul class="nav nav-treeview">
                          <?php if ($permisoConsulta) : ?>
                          <li class="nav-item">
                              <a href="consulta_seguimiento" class="nav-link <?php echo ($rutaActual === 'consulta_seguimiento') ? 'active' : ''; ?>">
                                  <i class="far fa-circle nav-icon"></i>
                                  <p>Consulta y seguimiento</p>
                              </a>
                          </li>
                          <?php endif; ?>

                          <?php if ($permisoCcd) : ?>
                          <li class="nav-item">
                              <a href="series_documentales" class="nav-link <?php echo ($rutaActual === 'series_documentales') ? 'active' : ''; ?>">
                                  <i class="far fa-circle nav-icon"></i>
                                  <p>Series documentales</p>
                              </a>
                          </li>

                          <li class="nav-item">
                              <a href="subseries_documentales" class="nav-link <?php echo ($rutaActual === 'subseries_documentales') ? 'active' : ''; ?>">
                                  <i class="far fa-circle nav-icon"></i>
                                  <p>Subseries documentales</p>
                              </a>
                          </li>
                          <?php endif; ?>
                      </ul>
                  </li>
                  <?php endif; ?>

                  <!-- ******************************* TRAMITES ******************************* -->
                  <?php if ($permisoTramites) : ?>
                  <li class="nav-item">
                      <a href="gestion_tramites" class="nav-link <?php echo ($rutaActual === 'gestion_tramites') ? 'active' : ''; ?>">
                          <i class="nav-icon fas fa-handshake"></i>
                          <p>Gestión de trámites</p>
                      </a>
                  </li>
                  <?php endif; ?>

                  <!-- ******************************* REPORTES ******************************* -->
                  <?php if ($permisoReportes) : ?>
                  <li class="nav-item">
                      <a href="reportes" class="nav-link <?php echo ($rutaActual === 'reportes') ? 'active' : ''; ?>">
                          <i class="nav-icon fas fa-chart-bar"></i>
                          <p>Reportes y estadísticas</p>
                      </a>
                  </li>
                  <?php endif; ?>

              </ul>
          </nav>
          <!-- /.sidebar-menu -->
      </div>
      <!-- /.sidebar -->
  </aside>
