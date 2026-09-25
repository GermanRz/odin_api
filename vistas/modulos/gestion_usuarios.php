<!-- Content Header (Encabezado de la página) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1><i class="fas fa-users-cog mr-2"></i> Gestión de Usuarios</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="inicio"><i class="fas fa-home mr-1"></i> Inicio</a></li>
          <li class="breadcrumb-item active">Gestión de Usuarios</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<!-- Main content -->
<section class="content">
  <div class="container-fluid">

    <!-- Tarjetas de métricas informativas (Actualizadas asíncronamente) -->
    <div class="row">
      <div class="col-lg-3 col-sm-6 col-12">
        <div class="small-box bg-info">
          <div class="inner">
            <h3 id="totalUsuarios">--</h3>
            <p>Total Registrados</p>
          </div>
          <div class="icon">
            <i class="fas fa-users"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-sm-6 col-12">
        <div class="small-box bg-success">
          <div class="inner">
            <h3 id="totalUsuariosActivos">--</h3>
            <p>Usuarios Activos</p>
          </div>
          <div class="icon">
            <i class="fas fa-user-check"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-sm-6 col-12">
        <div class="small-box bg-warning">
          <div class="inner">
            <h3 id="totalUsuariosInactivos">--</h3>
            <p>Usuarios Inactivos</p>
          </div>
          <div class="icon">
            <i class="fas fa-user-times"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-sm-6 col-12">
        <div class="small-box bg-secondary">
          <div class="inner">
            <h3>ODIN</h3>
            <p>Control de Acceso</p>
          </div>
          <div class="icon">
            <i class="fas fa-shield-alt"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- TARJETA PRINCIPAL CON TABLA VACÍA PARA DATATABLES AJAX -->
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list mr-1"></i> Listado de Usuarios</h3>
        <div class="card-tools ml-auto">
          <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal-agregarUsuario">
            <i class="fas fa-user-plus mr-1"></i> Agregar Usuario
          </button>
        </div>
      </div>

      <div class="card-body">
        <table id="tblUsuarios" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Identificación</th>
              <th>Correo</th>
              <th>Rol</th>
              <th>Dependencia</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <!-- El contenido se renderiza dinámicamente vía AJAX desde vistas/js/usuarios.js -->
          </tbody>
        </table>
      </div>
    </div>

  </div>
</section>

<!-- =============================================
MODAL: AGREGAR USUARIO
============================================= -->
<div class="modal fade" id="modal-agregarUsuario" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-user-plus mr-1"></i> Registrar Nuevo Usuario</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="frmAgregarUsuario">
        <div class="modal-body">
          <div class="row">

            <!-- Tipo de Documento -->
            <div class="col-md-4 form-group">
              <label for="nuevoTipoDocumento">Tipo Documento (*)</label>
              <select class="form-control" id="nuevoTipoDocumento" required>
                <option value="CC">Cédula de Ciudadanía</option>
                <option value="TI">Tarjeta de Identidad</option>
                <option value="CE">Cédula de Extranjería</option>
                <option value="PA">Pasaporte</option>
              </select>
            </div>

            <!-- Número de Identificación -->
            <div class="col-md-8 form-group">
              <label for="nuevoNumeroIdentificacion">Número de Identificación (*)</label>
              <input type="text" class="form-control" id="nuevoNumeroIdentificacion" placeholder="Ej: 1098765432" required>
            </div>

            <!-- Nombre Completo -->
            <div class="col-md-6 form-group">
              <label for="nuevoNombre">Nombre Completo (*)</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-user"></i></span>
                </div>
                <input type="text" class="form-control" id="nuevoNombre" placeholder="Nombres y Apellidos" required>
              </div>
            </div>

            <!-- Correo Electrónico -->
            <div class="col-md-6 form-group">
              <label for="nuevoCorreo">Correo Electrónico (*)</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                </div>
                <input type="email" class="form-control" id="nuevoCorreo" placeholder="correo@ejemplo.com" required>
              </div>
            </div>

            <!-- Rol -->
            <div class="col-md-6 form-group">
              <label for="nuevoRol">Rol de Acceso (*)</label>
              <select class="form-control" id="nuevoRol" required>
                <option value="">Cargando roles...</option>
              </select>
            </div>

            <!-- Dependencia -->
            <div class="col-md-6 form-group">
              <label for="nuevaDependencia">Dependencia (*)</label>
              <select class="form-control" id="nuevaDependencia" required>
                <option value="">Cargando dependencias...</option>
              </select>
            </div>

            <!-- Dirección -->
            <div class="col-md-6 form-group">
              <label for="nuevaDireccion">Dirección</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                </div>
                <input type="text" class="form-control" id="nuevaDireccion" placeholder="Dirección de residencia">
              </div>
            </div>

            <!-- Teléfono -->
            <div class="col-md-6 form-group">
              <label for="nuevoTelefono">Teléfono</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-phone"></i></span>
                </div>
                <input type="text" class="form-control" id="nuevoTelefono" placeholder="Teléfono de contacto">
              </div>
            </div>

          </div>
        </div>

        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar Usuario</button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- =============================================
MODAL: EDITAR USUARIO
============================================= -->
<div class="modal fade" id="modal-editarUsuario" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-user-edit mr-1"></i> Modificar Usuario</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="frmEditarUsuario">
        <div class="modal-body">
          <input type="hidden" id="editarIdUsuario">

          <div class="row">

            <!-- Tipo Documento (Solo lectura) -->
            <div class="col-md-4 form-group">
              <label for="editarTipoDocumento">Tipo Documento</label>
              <input type="text" class="form-control" id="editarTipoDocumento" readonly>
            </div>

            <!-- Número de Identificación (Solo lectura) -->
            <div class="col-md-8 form-group">
              <label for="editarNumeroIdentificacion">Número de Identificación</label>
              <input type="text" class="form-control" id="editarNumeroIdentificacion" readonly>
            </div>

            <!-- Nombre Completo -->
            <div class="col-md-6 form-group">
              <label for="editarNombre">Nombre Completo (*)</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-user"></i></span>
                </div>
                <input type="text" class="form-control" id="editarNombre" required>
              </div>
            </div>

            <!-- Correo Electrónico -->
            <div class="col-md-6 form-group">
              <label for="editarCorreo">Correo Electrónico (*)</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                </div>
                <input type="email" class="form-control" id="editarCorreo" required>
              </div>
            </div>

            <!-- Rol -->
            <div class="col-md-6 form-group">
              <label for="editarRol">Rol de Acceso (*)</label>
              <select class="form-control" id="editarRol" required>
                <option value="">Cargando roles...</option>
              </select>
            </div>

            <!-- Dependencia -->
            <div class="col-md-6 form-group">
              <label for="editarDependencia">Dependencia (*)</label>
              <select class="form-control" id="editarDependencia" required>
                <option value="">Cargando dependencias...</option>
              </select>
            </div>

            <!-- Dirección -->
            <div class="col-md-6 form-group">
              <label for="editarDireccion">Dirección</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                </div>
                <input type="text" class="form-control" id="editarDireccion">
              </div>
            </div>

            <!-- Teléfono -->
            <div class="col-md-6 form-group">
              <label for="editarTelefono">Teléfono</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-phone"></i></span>
                </div>
                <input type="text" class="form-control" id="editarTelefono">
              </div>
            </div>

          </div>
        </div>

        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Actualizar Usuario</button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- Inclusión modular del script del cliente -->
<script src="vistas/js/usuarios.js"></script>