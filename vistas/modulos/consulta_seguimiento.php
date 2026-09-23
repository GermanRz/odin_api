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
  <div class="input-group input-group-lg">
    <span class="input-group-text bg-body">
      <i class="bi bi-search" aria-hidden="true"></i>
    </span>
    <input type="search" class="form-control" placeholder="Buscar Radicado, Asunto o Responsable" aria-label="Search the FAQ">
  </div>

  <div class="card-body">
    <!--begin::Row-->
    <div class="row">
      <!--begin::Col-->
      <div class="col-md-4">
        <select id="filtroTipoDocumento" class="form-control form-select-lg">
          <option value="">Tipo de documento</option>
          <option value="CC">Cédula de ciudadanía</option>
          <option value="CE">Cédula de extranjería</option>
          <option value="TI">Tarjeta de identidad</option>
        </select>
      </div>
      <!--end::Col-->
      <!--begin::Col-->
      <div class="col-md-4">
        <select id="filtroEstado" class="form-control form-select-lg">
          <option value="">Todos los estados</option>
          <option value="En tramite">En trámite</option>
          <option value="Pendiente">Pendiente</option>
          <option value="Finalizado">Finalizado</option>
        </select>
      </div>
      <div class="col-md-4">
        <input
          type="date"
          id="fecha"
          class="form-control">
      </div>

      <!--end::Row-->
    </div>
    <div class="card-body p-0">
      <table class="table table-striped projects">
        <thead>
          <tr>
            <th style="width: 15%;">Acciones</th>
            <th>Radicado</th>
            <th>Tipo Documento</th>
            <th>Fecha Registro</th>
            <th>Estado</th>
            <th>Responsable</th>
          </tr>
        </thead>

        <tbody>
          <tr>
            <!-- Acciones -->
            <td>
              <a class="btn btn-primary btn-sm mb-1" href="#">
                <i class="fas fa-download"></i>
                Descargar
              </a>
              <br>
              <a class="btn btn-info btn-sm" href="#">
                <i class="far fa-eye"></i>
                Visualizar
              </a>
            </td>

            <!-- Radicado -->
            <td>2023-ABC-001</td>

            <!-- Tipo Documento -->
            <td>Oficio de Comisión</td>

            <!-- Fecha -->
            <td>12/03/2024</td>

            <!-- Estado -->
            <td>
              <span class="badge bg-primary">En Trámite</span>
            </td>
            <td>Lucía Gómez</td>
          </tr>
          <tr>
            <td>
              <a class="btn btn-primary btn-sm mb-1" href="#">
                <i class="fas fa-download"></i>
                Descargar
              </a>
              <br>
              <a class="btn btn-info btn-sm" href="#">
                <i class="far fa-eye"></i>
                Visualizar
              </a>
            </td>
            <td>2023-ABC-002</td>
            <td>Resolución</td>
            <td>15/03/2024</td>
            <td>
              <span class="badge bg-success">Finalizado</span>
            </td>
            <td>Carlos Pérez</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</section>
