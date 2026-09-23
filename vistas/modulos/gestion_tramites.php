  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Gestión de Trámites</h1>
        </div>
        <div class="col-sm-6 text-right">
          <a href="radicacion" class="btn btn-success">
            <i class="fas fa-plus"></i> Nuevo Trámite
          </a>
        </div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">

      <div class="row">
        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3>0</h3>
              <p>Pendientes</p>
            </div>
            <div class="icon"><i class="fas fa-clock"></i></div>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3>0</h3>
              <p>En proceso</p>
            </div>
            <div class="icon"><i class="fas fa-spinner"></i></div>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3>0</h3>
              <p>Finalizados</p>
            </div>
            <div class="icon"><i class="fas fa-check"></i></div>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="small-box bg-danger">
            <div class="inner">
              <h3>0</h3>
              <p>Vencidos</p>
            </div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Listado de Trámites</h3>
        </div>

        <div class="card-body">
          <div class="row mb-3">
            <div class="col-md-4"><input id="filtroBusqueda" class="form-control" placeholder="Buscar radicado o asunto"></div>
            <div class="col-md-3"><select id="filtroEstado" class="form-control">
                <option>Todos los estados</option>
              </select></div>
            <div class="col-md-3"><select id="filtroDependencia" class="form-control">
                <option>Todas las dependencias</option>
              </select></div>
            <div class="col-md-2"><button class="btn btn-primary btn-block">Filtrar</button></div>
          </div>

          <table class="table table-bordered table-hover">
            <thead>
              <tr>
                <th>Radicado</th>
                <th>Asunto</th>
                <th>Trámite</th>
                <th>Estado</th>
                <th>Dependencia</th>
                <th>Responsable</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody id="tablaBody"></tbody>
          </table>
        </div>
      </div>

    </div>
  </section>