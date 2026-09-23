  <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <!-- Brand Logo -->
      <a href="inicio" class="brand-link">
          <img src="vistas/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
          <span class="brand-text font-weight-light">Odin</span>
      </a>

      <!-- Sidebar -->
      <div class="sidebar">
          <!-- Sidebar user (optional) -->
          <div class="user-panel mt-3 pb-3 mb-3 d-flex">
              <div class="image">
                  <img src="vistas/dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
              </div>
              <div class="info">
                  <a href="#" class="d-block"><?php echo isset($_SESSION["nombre"]) ? $_SESSION["nombre"] : "Usuario"; ?></a>
              </div>
          </div>

          <!-- Sidebar Menu -->
          <nav class="mt-2">
              <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                  <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->


                  <!-- *******************************INICIO******************************* -->
                  <li class="nav-item">
                      <a href="inicio" class="nav-link">
                          <i class="nav-icon fas fa-home"></i>
                          <p>
                              Inicio
                          </p>
                      </a>
                  </li>

                  <!-- ******************************* CONTROL DE ACCESO ******************************* -->
                  <li class="nav-item">
                      <a href="#" class="nav-link">
                          <i class="nav-icon fas fa-users"></i>
                          <p>
                              Control de Acceso
                              <i class="fas fa-angle-left right"></i>
                          </p>
                      </a>
                      <ul class="nav nav-treeview">
                          <li class="nav-item">
                              <a href="gestion_usuarios" class="nav-link">
                                  <i class="far fa-circle nav-icon"></i>
                                  <p>Gestión de usuarios</p>
                              </a>
                          </li>
                          <li class="nav-item">
                              <a href="roles_permisos" class="nav-link">
                                  <i class="far fa-circle nav-icon"></i>
                                  <p>Roles y permisos</p>
                              </a>
                          </li>
                      </ul>
                  </li>

                  <!-- ******************************* DEPENDENCIAS ******************************* -->

                  <li class="nav-item">
                      <a href="dependencias" class="nav-link">
                          <i class="nav-icon fas fa-building"></i>
                          <p>
                              Dependencias
                          </p>
                      </a>
                  </li>

                  <!-- ******************************* RADICACION ******************************* -->

                  <li class="nav-item">
                      <a href="radicacion" class="nav-link">
                          <i class="nav-icon fas fa-file"></i>
                          <p>
                              Radicación
                          </p>
                      </a>
                  </li>

                  <!-- ******************************* GESTION DOCUMENTAL ******************************* -->
                  <li class="nav-item">
                      <a href="#" class="nav-link">
                          <i class="nav-icon fas fa-eye"></i>
                          <p>
                              Gestión documental
                              <i class="fas fa-angle-left right"></i>
                          </p>
                      </a>

                      <ul class="nav nav-treeview">
                          <li class="nav-item">
                              <a href="consulta_seguimiento" class="nav-link">
                                  <i class="far fa-circle nav-icon"></i>
                                  <p>Consulta y seguimiento</p>
                              </a>
                          </li>

                          <li class="nav-item">
                              <a href="series_documentales" class="nav-link">
                                  <i class="far fa-circle nav-icon"></i>
                                  <p>Series documentales</p>
                              </a>
                          </li>

                          <li class="nav-item">
                              <a href="subseries_documentales" class="nav-link">
                                  <i class="far fa-circle nav-icon"></i>
                                  <p>Subseries documentales</p>
                              </a>
                          </li>                          

                      </ul>
                  </li>

                  <!-- ******************************* TRAMITES ******************************* -->

                  <li class="nav-item">
                      <a href="gestion_tramites" class="nav-link">
                          <i class="nav-icon fas fa-handshake"></i>
                          <p>
                              Gestión de trámites
                          </p>
                      </a>
                  </li> 
                  
                  <!-- ******************************* REPORTES ******************************* -->

                  <li class="nav-item">
                      <a href="reportes" class="nav-link">
                          <i class="nav-icon fas fa-chart-bar"></i>
                          <p>
                              Reportes y estadísticas
                          </p>
                      </a>
                  </li>                  



              </ul>
          </nav>
          <!-- /.sidebar-menu -->
      </div>
      <!-- /.sidebar -->
  </aside>