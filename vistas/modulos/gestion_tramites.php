<!-- =============================================
PÁGINA: GESTIÓN DE TRÁMITES — SISTEMA ODIN
Bandeja Operativa + Catálogo de Tipos de Trámite
Normativa: RF-26, RF-28, RF-29, RF-32, RF-35
============================================= -->

<!-- 1. Encabezado de Página (Breadcrumbs y Título) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1><i class="fas fa-tasks mr-2 text-primary"></i> Gestión de Trámites</h1>
      </div>
      <div class="col-sm-6 text-right">
        <ol class="breadcrumb float-sm-right mb-0">
          <li class="breadcrumb-item"><a href="inicio"><i class="fas fa-home mr-1"></i> Inicio</a></li>
          <li class="breadcrumb-item active">Gestión de Trámites</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<!-- 2. Contenido Principal -->
<section class="content">
  <div class="container-fluid">

    <!-- Alerta Documental Dinámica de Vencidos (RF-32) -->
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" id="alertaVencidos" style="display: none;" role="alert">
      <div class="d-flex align-items-center justify-content-between flex-wrap">
        <div>
          <i class="icon fas fa-exclamation-triangle mr-2"></i>
          <strong>Alerta Documental:</strong> Existen <span id="contadorVencidosAlerta" class="badge badge-light text-danger font-weight-bold px-2 py-1 mx-1">0</span> trámite(s) o radicado(s) vencido(s) que requieren atención inmediata.
        </div>
        <button type="button" class="btn btn-light btn-sm font-weight-bold mt-2 mt-md-0" id="btnFiltrarVencidosAlerta">
          <i class="fas fa-filter mr-1 text-danger"></i> Ver trámites vencidos
        </button>
      </div>
    </div>

    <!-- Indicadores Cuantitativos / KPIs Clickeables -->
    <div class="row" id="contenedorKpis">
      <div class="col-lg-2 col-md-4 col-sm-6 col-12">
        <div class="small-box bg-warning elevation-2 kpi-tarjeta" data-kpi="pendientes" style="cursor: pointer;" title="Filtrar trámites pendientes">
          <div class="inner">
            <h3 id="kpiPendientes">0</h3>
            <p class="font-weight-bold mb-0">Pendientes</p>
          </div>
          <div class="icon">
            <i class="fas fa-clock"></i>
          </div>
          <a href="javascript:void(0)" class="small-box-footer">Filtrar <i class="fas fa-arrow-circle-right"></i></a>
        </div>
      </div>

      <div class="col-lg-2 col-md-4 col-sm-6 col-12">
        <div class="small-box bg-info elevation-2 kpi-tarjeta" data-kpi="en_proceso" style="cursor: pointer;" title="Filtrar trámites en proceso">
          <div class="inner">
            <h3 id="kpiEnProceso">0</h3>
            <p class="font-weight-bold mb-0">En Proceso</p>
          </div>
          <div class="icon">
            <i class="fas fa-spinner fa-spin-hover"></i>
          </div>
          <a href="javascript:void(0)" class="small-box-footer">Filtrar <i class="fas fa-arrow-circle-right"></i></a>
        </div>
      </div>

      <div class="col-lg-3 col-md-4 col-sm-6 col-12">
        <div class="small-box bg-success elevation-2 kpi-tarjeta" data-kpi="finalizados" style="cursor: pointer;" title="Filtrar trámites finalizados">
          <div class="inner">
            <h3 id="kpiFinalizados">0</h3>
            <p class="font-weight-bold mb-0">Finalizados</p>
          </div>
          <div class="icon">
            <i class="fas fa-check-circle"></i>
          </div>
          <a href="javascript:void(0)" class="small-box-footer">Filtrar <i class="fas fa-arrow-circle-right"></i></a>
        </div>
      </div>

      <div class="col-lg-2 col-md-6 col-sm-6 col-12">
        <div class="small-box bg-danger elevation-2 kpi-tarjeta" data-kpi="vencidos" style="cursor: pointer;" title="Filtrar trámites vencidos">
          <div class="inner">
            <h3 id="kpiVencidos">0</h3>
            <p class="font-weight-bold mb-0">Vencidos</p>
          </div>
          <div class="icon">
            <i class="fas fa-calendar-times"></i>
          </div>
          <a href="javascript:void(0)" class="small-box-footer">Filtrar <i class="fas fa-arrow-circle-right"></i></a>
        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-12 col-12">
        <div class="small-box bg-primary elevation-2 kpi-tarjeta" data-kpi="todos" style="cursor: pointer;" title="Mostrar todos los trámites">
          <div class="inner">
            <h3 id="kpiTotal">0</h3>
            <p class="font-weight-bold mb-0">Total Radicados</p>
          </div>
          <div class="icon">
            <i class="fas fa-layer-group"></i>
          </div>
          <a href="javascript:void(0)" class="small-box-footer">Ver todos <i class="fas fa-arrow-circle-right"></i></a>
        </div>
      </div>
    </div>

    <!-- Navegación de Pestañas Unificadas (Bandeja Operativa + Catálogo) -->
    <div class="card card-primary card-outline card-outline-tabs shadow-sm">
      <div class="card-header p-0 border-bottom-0">
        <ul class="nav nav-tabs" id="tabsGestionTramites" role="tablist">
          <li class="nav-item">
            <a class="nav-link active font-weight-bold py-3 px-4" id="tab-bandeja-link" data-toggle="pill" href="#tab-bandeja" role="tab" aria-controls="tab-bandeja" aria-selected="true">
              <i class="fas fa-inbox mr-2"></i> Bandeja Operativa de Trámites
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link font-weight-bold py-3 px-4" id="tab-catalogo-link" data-toggle="pill" href="#tab-catalogo" role="tab" aria-controls="tab-catalogo" aria-selected="false">
              <i class="fas fa-cogs mr-2"></i> Catálogo de Tipos de Trámite
            </a>
          </li>
        </ul>
      </div>

      <div class="card-body">
        <div class="tab-content" id="tabsGestionTramitesContent">

          <!-- ========================================================
               PESTAÑA 1: BANDEJA OPERATIVA (Seguimiento de Radicados)
          ======================================================== -->
          <div class="tab-pane fade show active" id="tab-bandeja" role="tabpanel" aria-labelledby="tab-bandeja-link">

            <!-- Filtros Avanzados (RF-35) -->
            <div class="card card-outline card-secondary mb-3 shadow-none border">
              <div class="card-header py-2">
                <h3 class="card-title text-muted font-weight-bold" style="font-size: 0.95rem;">
                  <i class="fas fa-filter mr-1 text-primary"></i> Filtros de Búsqueda y Clasificación
                </h3>
                <div class="card-tools">
                  <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Minimizar filtros">
                    <i class="fas fa-minus"></i>
                  </button>
                </div>
              </div>

              <div class="card-body pb-2">
                <form id="frmFiltrosBandeja">
                  <div class="row">
                    <!-- Buscador general por texto -->
                    <div class="col-lg-3 col-md-6 form-group">
                      <label for="filtroTexto" class="small text-uppercase font-weight-bold text-muted">Buscar Radicado / Asunto</label>
                      <div class="input-group">
                        <div class="input-group-prepend">
                          <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" class="form-control" id="filtroTexto" placeholder="Ej: E-2026-000001 o Solicitud...">
                      </div>
                    </div>

                    <!-- Filtro por Estado -->
                    <div class="col-lg-2 col-md-6 form-group">
                      <label for="filtroEstado" class="small text-uppercase font-weight-bold text-muted">Estado</label>
                      <select class="form-control" id="filtroEstado">
                        <option value="">Todos los estados</option>
                      </select>
                    </div>

                    <!-- Filtro por Dependencia -->
                    <div class="col-lg-2 col-md-6 form-group">
                      <label for="filtroDependencia" class="small text-uppercase font-weight-bold text-muted">Dependencia</label>
                      <select class="form-control" id="filtroDependencia">
                        <option value="">Todas las dependencias</option>
                      </select>
                    </div>

                    <!-- Filtro por Tipo de Trámite -->
                    <div class="col-lg-2 col-md-6 form-group">
                      <label for="filtroTramite" class="small text-uppercase font-weight-bold text-muted">Tipo de Trámite</label>
                      <select class="form-control" id="filtroTramite">
                        <option value="">Todos los tipos</option>
                      </select>
                    </div>

                    <!-- Filtro por Vencimiento -->
                    <div class="col-lg-3 col-md-6 form-group">
                      <label for="filtroVencimiento" class="small text-uppercase font-weight-bold text-muted">Criterio Vencimiento</label>
                      <select class="form-control" id="filtroVencimiento">
                        <option value="">Todos los vencimientos</option>
                        <option value="vencidos">Vencidos (Atención prioritaria)</option>
                        <option value="proximos">Próximos a vencer (&le; 3 días)</option>
                        <option value="en_plazo">En plazo regular (&gt; 3 días)</option>
                      </select>
                    </div>

                    <!-- Filtro Fecha Desde -->
                    <div class="col-lg-3 col-md-6 form-group">
                      <label for="filtroFechaDesde" class="small text-uppercase font-weight-bold text-muted">Fecha Radicación Desde</label>
                      <input type="date" class="form-control" id="filtroFechaDesde">
                    </div>

                    <!-- Filtro Fecha Hasta -->
                    <div class="col-lg-3 col-md-6 form-group">
                      <label for="filtroFechaHasta" class="small text-uppercase font-weight-bold text-muted">Fecha Radicación Hasta</label>
                      <input type="date" class="form-control" id="filtroFechaHasta">
                    </div>

                    <!-- Botones de Acción de Filtros -->
                    <div class="col-lg-6 col-md-12 form-group d-flex align-items-end justify-content-lg-end">
                      <button type="submit" class="btn btn-primary mr-2" id="btnAplicarFiltros">
                        <i class="fas fa-search mr-1"></i> Filtrar
                      </button>
                      <button type="button" class="btn btn-default" id="btnLimpiarFiltros">
                        <i class="fas fa-undo mr-1"></i> Limpiar Filtros
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>

            <!-- Barra de Acciones Masivas / por Lote (Mass Actions) -->
            <div class="d-flex align-items-center justify-content-between flex-wrap bg-light p-2 rounded mb-3 border" id="barraAccionesLote">
              <div class="d-flex align-items-center flex-wrap mb-2 mb-md-0">
                <span class="mr-2 font-weight-bold text-muted small"><i class="fas fa-tasks mr-1"></i> ACCIONES EN LOTE:</span>
                <button type="button" class="btn btn-outline-primary btn-sm mr-2 mb-1" id="btnAccionAsignar" disabled title="Asignar usuario responsable a los radicados seleccionados">
                  <i class="fas fa-user-tag mr-1"></i> Asignar
                </button>
                <button type="button" class="btn btn-outline-warning btn-sm mr-2 mb-1" id="btnAccionEstado" disabled title="Cambiar estado a los radicados seleccionados">
                  <i class="fas fa-exchange-alt mr-1"></i> Cambiar Estado
                </button>
                <button type="button" class="btn btn-outline-info btn-sm mr-2 mb-1" id="btnAccionReasignar" disabled title="Reasignar nuevo usuario y dependencia">
                  <i class="fas fa-share-alt mr-1"></i> Reasignar
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm mr-2 mb-1" id="btnAccionCerrar" disabled title="Cerrar y finalizar radicados seleccionados">
                  <i class="fas fa-check-circle mr-1"></i> Finalizar / Cerrar
                </button>
                <span class="badge badge-secondary py-1 px-2" id="badgeSeleccionados">0 seleccionados</span>
              </div>
              <div>
                <a href="radicacion" class="btn btn-success btn-sm font-weight-bold">
                  <i class="fas fa-plus mr-1"></i> Nuevo Trámite / Radicado
                </a>
              </div>
            </div>

            <!-- Tabla de Bandeja Operativa DataTables AJAX -->
            <div class="table-responsive">
              <table id="tblBandejaTramites" class="table table-bordered table-hover table-striped dt-responsive nowrap" style="width: 100%;">
                <thead class="thead-light">
                  <tr>
                    <th style="width: 30px; text-align: center;">
                      <input type="checkbox" id="checkAllTramites" title="Seleccionar todos">
                    </th>
                    <th>Radicado</th>
                    <th>Asunto</th>
                    <th>Trámite</th>
                    <th>Estado</th>
                    <th>Dependencia</th>
                    <th>Responsable</th>
                    <th>Prioridad</th>
                    <th>Vencimiento</th>
                    <th>Plazo / Restante</th>
                    <th style="width: 80px; text-align: center;">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Renderizado dinámico vía DataTables AJAX desde api/radicados -->
                </tbody>
              </table>
            </div>

          </div><!-- /.tab-pane bandeja -->


          <!-- ========================================================
               PESTAÑA 2: CATÁLOGO DE TIPOS DE TRÁMITE (RF-26, RF-28, RF-29)
          ======================================================== -->
          <div class="tab-pane fade" id="tab-catalogo" role="tabpanel" aria-labelledby="tab-catalogo-link">

            <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
              <div>
                <h4 class="font-weight-bold mb-1 text-dark">
                  <i class="fas fa-book mr-1 text-primary"></i> Catálogo y Parametrización de Tipos de Trámite
                </h4>
                <p class="text-muted small mb-0">Gestión de términos legales de respuesta, dependencias responsables y prioridades predeterminadas.</p>
              </div>
              <button type="button" class="btn btn-success font-weight-bold mt-2 mt-md-0" id="btnAbrirModalNuevoTipo">
                <i class="fas fa-plus mr-1"></i> Nuevo Tipo de Trámite
              </button>
            </div>

            <!-- Tabla de Catálogo DataTables AJAX -->
            <div class="table-responsive">
              <table id="tblCatalogoTramites" class="table table-bordered table-striped dt-responsive nowrap" style="width: 100%;">
                <thead class="thead-light">
                  <tr>
                    <th style="width: 60px;">ID</th>
                    <th>Nombre del Trámite</th>
                    <th>Días Respuesta</th>
                    <th>Prioridad Defecto</th>
                    <th>Requiere Respuesta</th>
                    <th>Dependencia Responsable</th>
                    <th>Estado</th>
                    <th style="width: 100px; text-align: center;">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Renderizado dinámico vía DataTables AJAX desde api/tramites -->
                </tbody>
              </table>
            </div>

          </div><!-- /.tab-pane catalogo -->

        </div><!-- /.tab-content -->
      </div><!-- /.card-body -->
    </div><!-- /.card -->

  </div><!-- /.container-fluid -->
</section>

<!-- ========================================================
     MODALES BOOTSTRAP 4 (ADMINLTE 3)
======================================================== -->

<!-- Modal 1: Formulario Crear / Editar Tipo de Trámite (RF-28, RF-29) -->
<div class="modal fade" id="modalTipoTramite" tabindex="-1" role="dialog" aria-labelledby="modalTipoTramiteLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title font-weight-bold" id="modalTipoTramiteLabel">
          <i class="fas fa-file-alt mr-2"></i> Nuevo Tipo de Trámite
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="frmTipoTramite">
        <div class="modal-body">
          <input type="hidden" id="tramiteId" name="id_tramite" value="">

          <div class="card card-outline card-primary shadow-none border mb-3">
            <div class="card-header py-2">
              <h3 class="card-title text-muted font-weight-bold small text-uppercase">Información General</h3>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-12 form-group">
                  <label for="tramiteNombre">Nombre del Trámite <span class="text-danger">(*)</span></label>
                  <input type="text" class="form-control" id="tramiteNombre" name="nombre" placeholder="Ej: Derecho de Petición / Solicitud de Información" required maxlength="200">
                </div>

                <div class="col-md-12 form-group">
                  <label for="tramiteDescripcion">Descripción y Criterios de Uso <span class="text-danger">(*)</span></label>
                  <textarea class="form-control" id="tramiteDescripcion" name="descripcion" rows="3" placeholder="Detalle los criterios normativos y casos de uso de este trámite..." required></textarea>
                </div>
              </div>
            </div>
          </div>

          <div class="card card-outline card-info shadow-none border mb-0">
            <div class="card-header py-2">
              <h3 class="card-title text-muted font-weight-bold small text-uppercase">Plazos de Respuesta y Parametrización</h3>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6 form-group">
                  <label for="tramiteDiasRespuesta">Días de Respuesta Legal <span class="text-danger">(*)</span></label>
                  <div class="input-group">
                    <input type="number" class="form-control" id="tramiteDiasRespuesta" name="dias_respuesta" min="0" max="365" value="15" required>
                    <div class="input-group-append">
                      <span class="input-group-text">Días</span>
                    </div>
                  </div>
                  <small class="form-text text-muted">Término de ley para cálculo de semáforo y vencimiento.</small>
                </div>

                <div class="col-md-6 form-group">
                  <label for="tramitePrioridad">Prioridad Predeterminada</label>
                  <select class="form-control" id="tramitePrioridad" name="prioridad_default">
                    <option value="BAJA">Baja</option>
                    <option value="MEDIA" selected>Media</option>
                    <option value="ALTA">Alta</option>
                    <option value="URGENTE">Urgente</option>
                  </select>
                </div>

                <div class="col-md-6 form-group">
                  <label for="tramiteDependencia">Dependencia Responsable Predeterminada</label>
                  <select class="form-control" id="tramiteDependencia" name="id_dependencia_responsable">
                    <option value="">Sin asignar (Opcional)</option>
                  </select>
                </div>

                <div class="col-md-6 form-group">
                  <label for="tramiteEstadoInicial">Estado Inicial del Trámite</label>
                  <select class="form-control" id="tramiteEstadoInicial" name="id_estado_inicial">
                    <option value="">Por defecto del sistema (Pendiente)</option>
                  </select>
                </div>

                <div class="col-md-6 form-group">
                  <div class="custom-control custom-checkbox mt-2">
                    <input type="checkbox" class="custom-control-input" id="tramiteRequiereRespuesta" name="requiere_respuesta" checked>
                    <label class="custom-control-label" for="tramiteRequiereRespuesta">Requiere respuesta formal documentada</label>
                  </div>
                </div>

                <div class="col-md-6 form-group">
                  <div class="custom-control custom-switch mt-2">
                    <input type="checkbox" class="custom-control-input" id="tramiteActivo" name="activo" checked>
                    <label class="custom-control-label" for="tramiteActivo">Trámite Activo para Radicación</label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary" id="btnGuardarTipoTramite">
            <i class="fas fa-save mr-1"></i> Guardar Trámite
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal 2: Asignar Responsable en Lote -->
<div class="modal fade" id="modalAsignarResponsable" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title font-weight-bold"><i class="fas fa-user-tag mr-2"></i> Asignar Usuario Responsable</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="frmAsignarResponsable">
        <div class="modal-body">
          <p class="text-muted small">Esta asignación se aplicará a los <span class="font-weight-bold text-primary totalRadicadosModal">0</span> trámite(s) seleccionado(s).</p>
          <div class="form-group">
            <label for="selectAsignarUsuario">Usuario Responsable <span class="text-danger">(*)</span></label>
            <select class="form-control" id="selectAsignarUsuario" required>
              <option value="">Seleccione usuario...</option>
            </select>
          </div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-check mr-1"></i> Confirmar Asignación</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal 3: Cambiar Estado en Lote -->
<div class="modal fade" id="modalCambiarEstado" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title font-weight-bold"><i class="fas fa-exchange-alt mr-2"></i> Cambiar Estado del Trámite</h5>
        <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="frmCambiarEstado">
        <div class="modal-body">
          <p class="text-muted small">El nuevo estado se registrará para los <span class="font-weight-bold text-primary totalRadicadosModal">0</span> trámite(s) seleccionado(s).</p>
          <div class="form-group">
            <label for="selectCambioEstado">Nuevo Estado <span class="text-danger">(*)</span></label>
            <select class="form-control" id="selectCambioEstado" required>
              <option value="">Seleccione estado...</option>
            </select>
          </div>
          <div class="form-group">
            <label for="observacionCambioEstado">Observación / Justificación</label>
            <textarea class="form-control" id="observacionCambioEstado" rows="3" placeholder="Motivo del cambio de estado..."></textarea>
          </div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-warning font-weight-bold"><i class="fas fa-check mr-1"></i> Actualizar Estado</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal 4: Reasignar Usuario y Dependencia en Lote -->
<div class="modal fade" id="modalReasignar" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title font-weight-bold"><i class="fas fa-share-alt mr-2"></i> Reasignar Trámite</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="frmReasignar">
        <div class="modal-body">
          <p class="text-muted small">Nuevo responsable y dependencia para los <span class="font-weight-bold text-white bg-info px-2 py-0.5 rounded totalRadicadosModal">0</span> trámite(s) seleccionado(s).</p>
          <div class="form-group">
            <label for="selectReasignarUsuario">Nuevo Usuario Responsable <span class="text-danger">(*)</span></label>
            <select class="form-control" id="selectReasignarUsuario" required>
              <option value="">Seleccione usuario...</option>
            </select>
          </div>
          <div class="form-group">
            <label for="selectReasignarDependencia">Nueva Dependencia</label>
            <select class="form-control" id="selectReasignarDependencia">
              <option value="">Mantener dependencia actual o seleccionar...</option>
            </select>
          </div>
          <div class="form-group">
            <label for="observacionReasignar">Motivo de Reasignación</label>
            <textarea class="form-control" id="observacionReasignar" rows="2" placeholder="Motivo de la reasignación de área o funcionario..."></textarea>
          </div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-info font-weight-bold"><i class="fas fa-check mr-1"></i> Confirmar Reasignación</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal 5: Cerrar / Finalizar Trámite en Lote -->
<div class="modal fade" id="modalCerrarTramite" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title font-weight-bold"><i class="fas fa-check-circle mr-2"></i> Cerrar / Finalizar Trámite</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="frmCerrarTramite">
        <div class="modal-body">
          <div class="alert alert-warning py-2 small">
            <i class="fas fa-info-circle mr-1"></i> Esta acción registrará fecha y hora de cierre formal para los <strong class="totalRadicadosModal">0</strong> trámite(s) seleccionados.
          </div>
          <div class="form-group">
            <label for="observacionCierre">Observación de Cierre / Respuesta Notificada</label>
            <textarea class="form-control" id="observacionCierre" rows="3" placeholder="Indique la conclusión del trámite o número de respuesta emitida..."></textarea>
          </div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger font-weight-bold"><i class="fas fa-lock mr-1"></i> Confirmar Cierre</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal 6: Vista Detallada de Radicado -->
<div class="modal fade" id="modalDetalleRadicado" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title font-weight-bold">
          <i class="fas fa-info-circle mr-2 text-info"></i> Detalle de Trámite: <span id="detalleNumeroRadicado" class="badge badge-light text-dark font-weight-bold ml-1">-</span>
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="cuerpoDetalleRadicado">
        <div class="text-center py-4 text-muted">
          <i class="fas fa-spinner fa-spin fa-2x"></i>
          <p class="mt-2 mb-0">Cargando información del radicado...</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- 3. Vinculación del script del módulo -->
<script src="vistas/js/gestion_tramites.js"></script>
