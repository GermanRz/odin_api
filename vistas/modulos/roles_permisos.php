<!-- Content Header (Encabezado de la página) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1><i class="fas fa-user-shield mr-2"></i> Gestión de Roles y Permisos</h1>
        <p class="text-muted mb-0">
          Administre los perfiles de acceso y la matriz de permisos granulares (RBAC) del sistema.
        </p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="inicio"><i class="fas fa-home mr-1"></i> Inicio</a></li>
          <li class="breadcrumb-item active">Roles y permisos</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<!-- Main content -->
<section class="content">
  <div class="container-fluid">

    <!-- Tarjetas de métricas informativas (Actualizadas asíncronamente vía API REST) -->
    <div class="row">
      <div class="col-lg-4 col-sm-6 col-12">
        <div class="small-box bg-info">
          <div class="inner">
            <h3 id="kpiTotalRoles">--</h3>
            <p>Total de roles registrados</p>
          </div>
          <div class="icon">
            <i class="fas fa-user-shield"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-sm-6 col-12">
        <div class="small-box bg-success">
          <div class="inner">
            <h3 id="kpiRolesActivos">--</h3>
            <p>Roles activos</p>
          </div>
          <div class="icon">
            <i class="fas fa-check-circle"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-4 col-sm-12 col-12">
        <div class="small-box bg-primary">
          <div class="inner">
            <h3 id="kpiTotalPermisos">--</h3>
            <p>Permisos granulares en catálogo (RBAC)</p>
          </div>
          <div class="icon">
            <i class="fas fa-key"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- TARJETA PRINCIPAL CON TABLA VACÍA PARA DATATABLES AJAX -->
    <div class="card card-primary card-outline shadow">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="card-title">
          <i class="fas fa-user-shield mr-2"></i>
          Listado de Roles y Permisos Asignados
        </h3>
        <div class="card-tools ml-auto">
          <a href="roles_permisosForm" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i> Nuevo rol
          </a>
        </div>
      </div>

      <div class="card-body">
        <table id="tblRoles" class="table table-bordered table-hover table-striped dt-responsive nowrap" style="width:100%">
          <thead class="bg-primary text-white">
            <tr>
              <th width="70">ID</th>
              <th width="140">Código / Tipo</th>
              <th>Nombre del Rol</th>
              <th>Descripción</th>
              <th width="140" class="text-center">Permisos</th>
              <th width="110" class="text-center">Usuarios</th>
              <th width="120" class="text-center">Estado</th>
              <th width="220" class="text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <!-- El contenido se inyecta dinámicamente vía AJAX desde vistas/js/roles.js -->
          </tbody>
          <tfoot>
            <tr>
              <th>ID</th>
              <th>Código / Tipo</th>
              <th>Nombre del Rol</th>
              <th>Descripción</th>
              <th class="text-center">Permisos</th>
              <th class="text-center">Usuarios</th>
              <th class="text-center">Estado</th>
              <th class="text-center">Acciones</th>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

  </div>
</section>

<!-- =============================================
MODAL: VISUALIZAR PERMISOS DEL ROL (RBAC)
============================================= -->
<div class="modal fade" id="modal-verPermisosRol" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h4 class="modal-title">
          <i class="fas fa-key mr-2"></i>
          Permisos del Rol: <span id="modalPermisosNombreRol"></span>
        </h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <p class="text-muted mb-3">
          <strong>Descripción:</strong> <span id="modalPermisosDescRol"></span>
        </p>
        <div class="row" id="listaPermisosModal">
          <!-- Renderizado dinámico de permisos granulares -->
        </div>
      </div>

      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        <a href="roles_permisosForm" id="btnIrEditarPermisosRol" class="btn btn-primary btn-sm">
          <i class="fas fa-edit mr-1"></i> Editar permisos de este rol
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Inclusión modular del script del cliente -->
<script src="vistas/js/roles.js"></script>
