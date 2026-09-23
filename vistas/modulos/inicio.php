    <!-- Content Header -->
    <section class="content-header">
      <div class="container-fluid">

        <div class="row mb-2">

          <div class="col-sm-6">
            <h1>
              <i class="fas fa-tachometer-alt mr-2"></i>
              Dashboard
            </h1>
          </div>

          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item">
                <a href="inicio">Inicio</a>
              </li>
              <li class="breadcrumb-item active">
                Dashboard
              </li>
            </ol>
          </div>

        </div>

      </div>
    </section>

    <!-- Main content -->
    <section class="content">

      <div class="container-fluid">

        <!-- ================= KPI ================= -->

        <div class="row">

          <div class="col-lg-3 col-6">

            <div class="small-box bg-primary">

              <div class="inner">
                <h3>1254</h3>
                <p>Documentos</p>
              </div>

              <div class="icon">
                <i class="fas fa-folder-open"></i>
              </div>

            </div>

          </div>

          <div class="col-lg-3 col-6">

            <div class="small-box bg-warning">

              <div class="inner">
                <h3>42</h3>
                <p>Pendientes</p>
              </div>

              <div class="icon">
                <i class="fas fa-clock"></i>
              </div>

            </div>

          </div>

          <div class="col-lg-3 col-6">

            <div class="small-box bg-success">

              <div class="inner">
                <h3>890</h3>
                <p>Finalizados</p>
              </div>

              <div class="icon">
                <i class="fas fa-check-circle"></i>
              </div>

            </div>

          </div>

          <div class="col-lg-3 col-6">

            <div class="small-box bg-danger">

              <div class="inner">
                <h3>8</h3>
                <p>Vencidos</p>
              </div>

              <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
              </div>

            </div>

          </div>

        </div>

        <!-- ================= FILA PRINCIPAL ================= -->

        <div class="row">

          <!-- Columna izquierda -->

          <section class="col-lg-8">

            <!-- Flujo documental -->

            <div class="card card-primary">

              <div class="card-header">

                <h3 class="card-title">
                  <i class="fas fa-chart-line mr-2"></i>
                  Flujo Documental
                </h3>

              </div>

              <div class="card-body">

                <canvas id="dashboardChart"
                  style="height:350px">
                </canvas>

              </div>

            </div>

            <!-- Últimos trámites -->

            <div class="card">

              <div class="card-header">

                <h3 class="card-title">

                  <i class="fas fa-list mr-2"></i>

                  Últimos Trámites

                </h3>

              </div>

              <div class="card-body table-responsive p-0">

                <table class="table table-hover">

                  <thead>

                    <tr>

                      <th>Radicado</th>
                      <th>Asunto</th>
                      <th>Estado</th>
                      <th>Responsable</th>

                    </tr>

                  </thead>

                  <tbody id="tablaDashboard">

                  </tbody>

                </table>

              </div>

            </div>

          </section>

          <!-- Sidebar Dashboard -->

          <section class="col-lg-4">

            <!-- Accesos rápidos -->

            <div class="card card-info">

              <div class="card-header">

                <h3 class="card-title">

                  Accesos rápidos

                </h3>

              </div>

              <div class="card-body">

                <div class="row">

                  <div class="col-6">

                    <a href="radicacion"
                      class="btn btn-app bg-success">

                      <i class="fas fa-plus-circle"></i>

                      Radicar

                    </a>

                  </div>

                  <div class="col-6">

                    <a href="gestion_tramites"
                      class="btn btn-app bg-primary">

                      <i class="fas fa-folder-open"></i>

                      Trámites

                    </a>

                  </div>

                  <div class="col-6">

                    <a href="consulta_seguimiento"
                      class="btn btn-app bg-warning">

                      <i class="fas fa-search"></i>

                      Consulta

                    </a>

                  </div>

                  <div class="col-6">

                    <a href="reportes"
                      class="btn btn-app bg-danger">

                      <i class="fas fa-chart-bar"></i>

                      Reportes

                    </a>

                  </div>

                </div>

              </div>

            </div>

            <!-- Actividad -->

            <div class="card card-outline card-secondary">

              <div class="card-header">

                <h3 class="card-title">

                  Actividad Reciente

                </h3>

              </div>

              <div class="card-body">

                <div class="timeline">

                  <!-- Aquí carga el historial -->

                </div>

              </div>

            </div>

          </section>

        </div>

      </div>

    </section>