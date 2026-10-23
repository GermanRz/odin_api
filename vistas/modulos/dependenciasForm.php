<!-- Content Header (Encabezado de la página) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 id="tituloFormularioDep"><i class="fas fa-building mr-2"></i> Crear Nueva Dependencia</h1>
        <p class="text-muted mb-0">
          RF-40 · RF-41 · RNF-14 — Registro y actualización de dependencias institucionales.
        </p>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item">
            <a href="dependencias" class="btn btn-secondary btn-sm">
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
          <i class="fas fa-building mr-2"></i>
          Información de la Dependencia
        </h3>
      </div>

      <form id="frmGuardarDependencia">
        <input type="hidden" id="idDependenciaFormulario" value="0">

        <div class="card-body">
          <div class="row">

            <!-- Nombre -->
            <div class="col-md-6 form-group">
              <label for="nombreDependencia">Nombre de la Dependencia <span class="text-danger">*</span></label>
              <input type="text"
                     class="form-control"
                     id="nombreDependencia"
                     placeholder="Ejemplo: Talento Humano"
                     required>
            </div>

            <!-- Código / Sigla -->
            <div class="col-md-3 form-group">
              <label for="codigoDependencia">Código / Sigla <span class="text-danger">*</span></label>
              <input type="text"
                     class="form-control"
                     id="codigoDependencia"
                     placeholder="Ejemplo: TH-05"
                     required>
            </div>

            <!-- Tipo -->
            <div class="col-md-3 form-group">
              <label for="tipoDependencia">Tipo de Unidad</label>
              <select class="form-control" id="tipoDependencia">
                <option value="operativa" selected>Operativa</option>
                <option value="administrativa">Administrativa</option>
                <option value="directiva">Directiva</option>
              </select>
            </div>

            <!-- Descripción -->
            <div class="col-md-9 form-group">
              <label for="descripcionDependencia">Descripción</label>
              <textarea class="form-control"
                        id="descripcionDependencia"
                        rows="3"
                        placeholder="Ingrese las funciones o descripción de la dependencia institucional"></textarea>
            </div>

            <!-- Estado -->
            <div class="col-md-3 form-group">
              <label for="estadoDependencia">Estado</label>
              <select class="form-control" id="estadoDependencia">
                <option value="Activo" selected>Activo</option>
                <option value="Inactivo">Inactivo</option>
              </select>
            </div>

          </div>
        </div>

        <div class="card-footer d-flex justify-content-between">
          <a href="dependencias" class="btn btn-danger">
            <i class="fas fa-times mr-1"></i> Cancelar
          </a>
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i> Guardar Cambios
          </button>
        </div>
      </form>
    </div>

  </div>
</section>

<!-- Inclusión modular del script del cliente -->
<script src="vistas/js/dependencias.js"></script>
