<!-- Content Header (Encabezado de la página) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 id="tituloFormularioRol"><i class="fas fa-user-shield mr-2"></i> Crear Nuevo Rol</h1>
        <p class="text-muted mb-0">
          RF-N16 · RF-43 · RF-44 — Configuración del rol y matriz visual de asignación de permisos granulares (RBAC).
        </p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item">
            <a href="roles_permisos" class="btn btn-secondary btn-sm">
              <i class="fas fa-arrow-left mr-1"></i> Volver al listado
            </a>
          </li>
        </ol>
      </div>
    </div>
  </div>
</section>

<!-- Main content -->
<section class="content">
  <div class="container-fluid">

    <div class="card card-primary card-outline shadow">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-user-shield mr-2"></i>
          Información del Rol y Matriz de Permisos
        </h3>
      </div>

      <form id="frmGuardarRol">
        <input type="hidden" id="idRolFormulario" value="0">

        <div class="card-body">
          <div class="row">

            <!-- Nombre del rol -->
            <div class="col-md-4 form-group">
              <label for="nombreRol">Nombre del Rol <span class="text-danger">*</span></label>
              <input type="text"
                     class="form-control"
                     id="nombreRol"
                     placeholder="Ejemplo: Supervisor Documental"
                     required>
            </div>

            <!-- Código / Tipo de rol -->
            <div class="col-md-4 form-group">
              <label for="codigoRol">Código / Tipo de Rol <span class="text-danger">*</span></label>
              <select class="form-control" id="codigoRol" required>
                <option value="admin">ADMIN — Administrador</option>
                <option value="operario" selected>OPERARIO / RADICADOR — Radicación</option>
                <option value="gestor">GESTOR — Gestión de Trámites</option>
                <option value="consulta">CONSULTA — Consulta y Reportes</option>
                <option value="supervisor">SUPERVISOR — Supervisión</option>
                <option value="invitado">INVITADO — Acceso Básico</option>
              </select>
            </div>

            <!-- Estado -->
            <div class="col-md-4 form-group">
              <label for="estadoRol">Estado <span class="text-danger">*</span></label>
              <select class="form-control" id="estadoRol">
                <option value="Activo" selected>Activo</option>
                <option value="Inactivo">Inactivo</option>
              </select>
            </div>

          </div>

          <hr>

          <!-- Matriz de Permisos RBAC (RF-N13 / RF-N16) -->
          <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
            <div>
              <h5 class="mb-0">
                <i class="fas fa-key text-primary mr-1"></i>
                Matriz de Permisos Granulares (RBAC)
              </h5>
              <small class="text-muted">
                Seleccione los permisos funcionales que tendrá habilitados este rol en el sistema ODIN.
              </small>
            </div>
            <div class="mt-2 mt-sm-0">
              <button type="button" class="btn btn-outline-primary btn-xs mr-1" id="btnSeleccionarTodosPermisos">
                <i class="fas fa-check-double mr-1"></i> Seleccionar todos
              </button>
              <button type="button" class="btn btn-outline-secondary btn-xs" id="btnLimpiarPermisos">
                <i class="fas fa-eraser mr-1"></i> Limpiar selección
              </button>
            </div>
          </div>

          <div class="row" id="contenedorMatrizPermisos">
            <div class="col-12 text-center py-4 text-muted">
              <i class="fas fa-spinner fa-spin mr-1"></i> Cargando catálogo de permisos RBAC desde la API...
            </div>
          </div>

        </div>

        <div class="card-footer d-flex justify-content-between">
          <a href="roles_permisos" class="btn btn-danger">
            <i class="fas fa-times mr-1"></i> Cancelar
          </a>
          <button type="submit" class="btn btn-primary" id="btnSubmitRol">
            <i class="fas fa-save mr-1"></i> Guardar Rol y Permisos
          </button>
        </div>
      </form>
    </div>

  </div>
</section>

<!-- Inclusión modular del script del cliente -->
<script src="vistas/js/roles.js"></script>
