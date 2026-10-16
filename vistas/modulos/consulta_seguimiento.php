<!-- Content Header (Page header) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Consulta y Seguimiento</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="radicacion"> <button type="button" class="btn btn-block btn-default">Nuevo Documento</button></a></li>
          <!-- <li class="breadcrumb-item active">Inicio</li> -->
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>


<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-search-location mr-1"></i> Listado y Seguimiento de Radicados
        </h3>
      </div>

      <div class="card-body">
        <!-- Buscador principal -->
        <div class="input-group input-group-lg mb-3">
          <div class="input-group-prepend">
            <span class="input-group-text bg-white">
              <i class="fas fa-search" aria-hidden="true"></i>
            </span>
          </div>
          <input type="search" id="inputBuscarRadicado" class="form-control" placeholder="Buscar por número de radicado, asunto, remitente o responsable...">
        </div>

        <!-- Filtros complementarios -->
        <div class="row mb-3">
          <div class="col-md-4 mb-2">
            <label for="filtroTipoDocumento" class="small text-muted font-weight-bold">Tipo de radicación:</label>
            <select id="filtroTipoDocumento" class="form-control">
              <option value="">Todos los tipos</option>
              <option value="ENTRADA">Entrada</option>
              <option value="SALIDA">Salida</option>
              <option value="INTERNO">Interno</option>
            </select>
          </div>

          <div class="col-md-4 mb-2">
            <label for="filtroEstado" class="small text-muted font-weight-bold">Estado del trámite:</label>
            <select id="filtroEstado" class="form-control">
              <option value="">Todos los estados</option>
            </select>
          </div>

          <div class="col-md-4 mb-2">
            <label for="fecha" class="small text-muted font-weight-bold">Fecha de radicación:</label>
            <input type="date" id="fecha" class="form-control">
          </div>
        </div>

        <!-- Tabla DataTables AJAX -->
        <div class="table-responsive">
          <table id="tblConsultaSeguimiento" class="table table-bordered table-striped dt-responsive nowrap" style="width: 100%;">
            <thead>
              <tr>
                <th style="width: 12%;">Acciones</th>
                <th>Radicado</th>
                <th>Tipo Documento</th>
                <th>Fecha Registro</th>
                <th>Estado</th>
                <th>Responsable</th>
              </tr>
            </thead>
            <tbody>
              <!-- Renderizado dinámico vía DataTables AJAX desde api/radicados -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Script de interacción cliente y consumo RESTful -->
<script src="vistas/js/consulta_seguimiento.js"></script>
