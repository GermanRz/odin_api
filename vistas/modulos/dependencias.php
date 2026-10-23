<!-- Content Header (Encabezado de la página) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1><i class="fas fa-building mr-2"></i> Gestión de Dependencias</h1>
        <p class="text-muted mb-0">Administre las dependencias organizacionales y asigne los usuarios responsables de cada área.</p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="inicio"><i class="fas fa-home mr-1"></i> Inicio</a></li>
          <li class="breadcrumb-item active">Dependencias</li>
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
            <h3 id="totalDependencias">--</h3>
            <p>Total Dependencias</p>
          </div>
          <div class="icon">
            <i class="fas fa-building"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-sm-6 col-12">
        <div class="small-box bg-success">
          <div class="inner">
            <h3 id="totalDependenciasActivas">--</h3>
            <p>Dependencias Activas</p>
          </div>
          <div class="icon">
            <i class="fas fa-check-circle"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-sm-6 col-12">
        <div class="small-box bg-warning">
          <div class="inner">
            <h3 id="totalDependenciasInactivas">--</h3>
            <p>Dependencias Inactivas</p>
          </div>
          <div class="icon">
            <i class="fas fa-pause-circle"></i>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-sm-6 col-12">
        <div class="small-box bg-primary">
          <div class="inner">
            <h3 id="totalConEncargado">--</h3>
            <p>Con Encargado Asignado</p>
          </div>
          <div class="icon">
            <i class="fas fa-user-shield"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- TARJETA PRINCIPAL CON TABLA AJAX -->
    <div class="card card-primary card-outline shadow-sm">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-sitemap mr-1"></i> Listado de Dependencias y Encargados
        </h3>
        <div class="card-tools ml-auto">
          <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal-agregarDependencia">
            <i class="fas fa-plus mr-1"></i> Nueva Dependencia
          </button>
        </div>
      </div>

      <div class="card-body">
        <div class="table-responsive">
          <table id="tblDependencias" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%">
            <thead>
              <tr>
                <th style="width: 50px;">ID</th>
                <th style="width: 100px;">Código</th>
                <th>Dependencia</th>
                <th style="width: 120px;">Tipo</th>
                <th>Encargado / Responsable</th>
                <th style="width: 100px;" class="text-center">Estado</th>
                <th style="width: 80px;" class="text-center">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <!-- Renderizado dinámico vía DataTables AJAX desde api/dependencias -->
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- ==============================================================
     MODAL: AGREGAR NUEVA DEPENDENCIA
============================================================== -->
<div class="modal fade" id="modal-agregarDependencia" tabindex="-1" role="dialog" aria-labelledby="modalAgregarDepLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form id="formAgregarDependencia" autocomplete="off">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="modalAgregarDepLabel">
            <i class="fas fa-building mr-1"></i> Registrar Nueva Dependencia
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <div class="row">
            <div class="col-md-8 form-group">
              <label for="nuevoNombre">Nombre de la Dependencia <span class="text-danger">*</span></label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-building"></i></span>
                </div>
                <input type="text" class="form-control" id="nuevoNombre" name="nuevoNombre" placeholder="Ej: Gestión Documental" required>
              </div>
            </div>

            <div class="col-md-4 form-group">
              <label for="nuevoCodigo">Código / Sigla <span class="text-danger">*</span></label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                </div>
                <input type="text" class="form-control" id="nuevoCodigo" name="nuevoCodigo" placeholder="Ej: GD-02" required style="text-transform: uppercase;">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 form-group">
              <label for="nuevoTipo">Tipo de Dependencia <span class="text-danger">*</span></label>
              <select class="form-control" id="nuevoTipo" name="nuevoTipo" required>
                <option value="operativa">Operativa</option>
                <option value="administrativa">Administrativa</option>
                <option value="directiva">Directiva</option>
              </select>
            </div>

            <div class="col-md-6 form-group">
              <label for="nuevoEstado">Estado Inicial <span class="text-danger">*</span></label>
              <select class="form-control" id="nuevoEstado" name="nuevoEstado" required>
                <option value="Activo">Activo</option>
                <option value="Inactivo">Inactivo</option>
              </select>
            </div>
          </div>

          <!-- SELECCIÓN DE ENCARGADO / RESPONSABLE -->
          <div class="row">
            <div class="col-md-12 form-group">
              <label for="nuevoResponsable">
                <i class="fas fa-user-shield text-primary mr-1"></i> Usuario Encargado del Área (Responsable)
              </label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-user-tie"></i></span>
                </div>
                <select class="form-control selectUsuariosEncargado" id="nuevoResponsable" name="nuevoResponsable">
                  <option value="">-- Sin encargado asignado (Opcional) --</option>
                </select>
              </div>
              <small class="form-text text-muted">
                Este usuario será cargado automáticamente como responsable cuando se seleccione esta dependencia en el módulo de Radicación.
              </small>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 form-group">
              <label for="nuevaDescripcion">Descripción / Funciones de la dependencia</label>
              <textarea class="form-control" id="nuevaDescripcion" name="nuevaDescripcion" rows="2" placeholder="Breve descripción de las actividades o trámites que atiende esta dependencia"></textarea>
            </div>
          </div>
        </div>

        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Cancelar
          </button>
          <button type="submit" class="btn btn-primary" id="btnGuardarDependencia">
            <i class="fas fa-save mr-1"></i> Guardar Dependencia
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ==============================================================
     MODAL: EDITAR DEPENDENCIA Y SU ENCARGADO
============================================================== -->
<div class="modal fade" id="modal-editarDependencia" tabindex="-1" role="dialog" aria-labelledby="modalEditarDepLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <form id="formEditarDependencia" autocomplete="off">
        <input type="hidden" id="editarIdDependencia" name="editarIdDependencia">

        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title" id="modalEditarDepLabel">
            <i class="fas fa-edit mr-1"></i> Modificar Dependencia y Encargado
          </h5>
          <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <div class="row">
            <div class="col-md-8 form-group">
              <label for="editarNombre">Nombre de la Dependencia <span class="text-danger">*</span></label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-building"></i></span>
                </div>
                <input type="text" class="form-control" id="editarNombre" name="editarNombre" required>
              </div>
            </div>

            <div class="col-md-4 form-group">
              <label for="editarCodigo">Código / Sigla <span class="text-danger">*</span></label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                </div>
                <input type="text" class="form-control" id="editarCodigo" name="editarCodigo" required style="text-transform: uppercase;">
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 form-group">
              <label for="editarTipo">Tipo de Dependencia <span class="text-danger">*</span></label>
              <select class="form-control" id="editarTipo" name="editarTipo" required>
                <option value="operativa">Operativa</option>
                <option value="administrativa">Administrativa</option>
                <option value="directiva">Directiva</option>
              </select>
            </div>

            <div class="col-md-6 form-group">
              <label for="editarEstado">Estado <span class="text-danger">*</span></label>
              <select class="form-control" id="editarEstado" name="editarEstado" required>
                <option value="Activo">Activo</option>
                <option value="Inactivo">Inactivo</option>
              </select>
            </div>
          </div>

          <!-- SELECCIÓN EDITABLE DEL ENCARGADO RESPONSABLE -->
          <div class="row">
            <div class="col-md-12 form-group">
              <label for="editarResponsable" class="font-weight-bold text-dark">
                <i class="fas fa-user-shield text-warning mr-1"></i> Usuario Encargado del Área (Responsable) — Editable
              </label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-user-tie"></i></span>
                </div>
                <select class="form-control selectUsuariosEncargado" id="editarResponsable" name="editarResponsable">
                  <option value="">-- Sin encargado asignado --</option>
                </select>
              </div>
              <small class="form-text text-muted">
                Puede cambiar o remover en cualquier momento el usuario encargado. Al guardar, los nuevos radicados de esta área se vincularán inmediatamente al usuario seleccionado.
              </small>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 form-group">
              <label for="editarDescripcion">Descripción / Funciones de la dependencia</label>
              <textarea class="form-control" id="editarDescripcion" name="editarDescripcion" rows="2"></textarea>
            </div>
          </div>
        </div>

        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Cancelar
          </button>
          <button type="submit" class="btn btn-warning" id="btnActualizarDependencia">
            <i class="fas fa-save mr-1"></i> Guardar Cambios
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Inclusión obligatoria del script cliente desacoplado -->
<script src="vistas/js/dependencias.js"></script>
