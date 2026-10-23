<!-- Content Header (Page header) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>
          <i class="fas fa-search-location mr-2 text-primary"></i> Consulta y Seguimiento
        </h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="inicio"><i class="fas fa-home mr-1"></i> Inicio</a></li>
          <li class="breadcrumb-item active">Consulta y Seguimiento</li>
          <li class="breadcrumb-item">
            <a href="radicacion" class="btn btn-sm btn-primary text-white ml-2">
              <i class="fas fa-plus-circle mr-1"></i> Nuevo Radicado
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

    <!-- Tarjeta Principal con la Tabla de Radicados -->
    <div class="card card-primary card-outline shadow-sm">
      <div class="card-body">
        <div class="table-responsive">
          <table id="tblConsultaSeguimiento" class="table table-bordered table-hover table-striped dt-responsive nowrap" style="width: 100%;">
            <thead class="thead-light">
              <tr>
                <th style="width: 13%; text-align: center;">Acciones</th>
                <th>Radicado</th>
                <th>Tipo</th>
                <th>Remitente / Entidad</th>
                <th>Dependencia & Responsable</th>
                <th>Fecha Radicado</th>
                <th>Estado Operativo</th>
                <th>Semáforo Plazos (RF-30)</th>
              </tr>
            </thead>
            <tbody>
              <!-- Renderizado dinámico vía DataTables AJAX desde api/seguimiento -->
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</section>

<!-- ================================================================= -->
<!-- MODALES DEL SISTEMA (ADMINLTE 3 / BOOTSTRAP 4)                    -->
<!-- ================================================================= -->

<!-- MODAL 1: LÍNEA DE TIEMPO (TIMELINE) E HISTORIAL DE AUDITORÍA AGN -->
<div class="modal fade" id="modal-timeline" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title font-weight-bold">
          <i class="fas fa-stream mr-2"></i> Historial y Trazabilidad de Radicación
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body bg-light">
        <!-- Encabezado Resumen del Radicado -->
        <div class="card mb-3 shadow-none border">
          <div class="card-body p-3">
            <div class="row align-items-center">
              <div class="col-md-7">
                <span class="badge badge-primary px-2 py-1 mb-1" id="modalTimelineTipo">ENTRADA</span>
                <h4 class="mb-1 font-weight-bold text-dark" id="modalTimelineRadicado">E-2026-000000</h4>
                <p class="text-muted small mb-0 text-truncate" id="modalTimelineAsunto" style="max-width: 420px;">Asunto del radicado</p>
              </div>
              <div class="col-md-5 text-md-right mt-2 mt-md-0">
                <div id="modalTimelineBadgeEstado" class="mb-1"></div>
                <div id="modalTimelineBadgeSemaforo"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Componente Timeline Nativo AdminLTE 3 -->
        <h6 class="font-weight-bold text-muted mb-3">
          <i class="fas fa-history mr-1"></i> Trazabilidad Cronológica Paso a Paso (AGN - Ley 594 de 2000):
        </h6>
        <div class="timeline" id="contenedorTimelineAdminLTE">
          <!-- Hitos inyectados dinámicamente con AdminLTE 3 Timeline -->
        </div>

      </div>

      <div class="modal-footer justify-content-between bg-white">
        <div>
          <!-- Botón de acceso directo a la vista de Metadatos requerido -->
          <button type="button" class="btn btn-info btn-sm" id="btnModalVerMetadatos">
            <i class="fas fa-eye mr-1"></i> Ver Metadatos del Radicado
          </button>
          <button type="button" class="btn btn-success btn-sm ml-1" id="btnModalAbrirRegistrarTraza">
            <i class="fas fa-plus mr-1"></i> Registrar Avance
          </button>
          <button type="button" class="btn btn-outline-primary btn-sm ml-1" id="btnModalImprimirHojaRuta">
            <i class="fas fa-print mr-1"></i> Imprimir Hoja de Ruta
          </button>
        </div>
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL 2: REGISTRAR MOVIMIENTO / TRASLADO / CAMBIO DE ESTADO -->
<div class="modal fade" id="modal-registrar-traza" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title font-weight-bold">
          <i class="fas fa-exchange-alt mr-2"></i> Registrar Movimiento Operativo
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="frmRegistrarTraza">
        <input type="hidden" id="trazaIdRadicado" value="">

        <div class="modal-body">
          <div class="alert alert-light border small text-muted mb-3">
            <i class="fas fa-shield-alt text-primary mr-1"></i> Todo movimiento ingresado queda registrado de forma inmutable en el historial y auditoría del sistema con su usuario, hora exacta y dependencia.
          </div>

          <!-- Tipo de Acción -->
          <div class="form-group">
            <label for="trazaAccion" class="font-weight-bold small">Tipo de Acción (*):</label>
            <select id="trazaAccion" class="form-control form-control-sm" required>
              <option value="observacion">Anotación / Observación de Seguimiento</option>
              <option value="cambio_estado">Cambio de Estado Operativo</option>
              <option value="traslado">Traslado Interdependencias</option>
              <option value="devolucion">Devolución por Requisitos / Anexos</option>
              <option value="finalizacion">Respuesta Oficial y Finalización</option>
            </select>
          </div>

          <!-- Nuevo Estado -->
          <div class="form-group" id="grpTrazaNuevoEstado">
            <label for="trazaNuevoEstado" class="font-weight-bold small">Nuevo Estado Operativo:</label>
            <select id="trazaNuevoEstado" class="form-control form-control-sm">
              <option value="">Mantener estado actual</option>
              <!-- Cargado vía AJAX -->
            </select>
          </div>

          <!-- Nueva Dependencia -->
          <div class="form-group" id="grpTrazaNuevaDependencia">
            <label for="trazaNuevaDependencia" class="font-weight-bold small">Trasladar a Dependencia:</label>
            <select id="trazaNuevaDependencia" class="form-control form-control-sm">
              <option value="">Mantener dependencia actual</option>
              <!-- Cargado vía AJAX -->
            </select>
          </div>

          <!-- Observación Obligatoria -->
          <div class="form-group mb-0">
            <label for="trazaObservacion" class="font-weight-bold small">Observación o Justificación Archivística (*):</label>
            <textarea id="trazaObservacion" class="form-control" rows="3" placeholder="Detalle el motivo del movimiento, oficio o respuesta..." required></textarea>
            <small class="form-text text-muted">Exigido por la Ley 594 de 2000 para la validez de la trazabilidad.</small>
          </div>
        </div>

        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success btn-sm font-weight-bold" id="btnGuardarTraza">
            <i class="fas fa-save mr-1"></i> Registrar Movimiento
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL 3: DETALLE EXHAUSTIVO Y METADATOS AGN DEL RADICADO -->
<div class="modal fade" id="modal-detalle-radicado" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title font-weight-bold">
          <i class="fas fa-file-invoice mr-2 text-info"></i> Metadatos y Detalle del Radicado (AGN)
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body p-3">
        <!-- Ficha de Metadatos -->
        <div class="row">
          <div class="col-md-6 mb-3">
            <div class="p-2 border rounded bg-light">
              <span class="text-muted d-block small font-weight-bold">NÚMERO DE RADICADO:</span>
              <h5 class="mb-0 text-primary font-weight-bold" id="detNumeroRadicado">-</h5>
            </div>
          </div>
          <div class="col-md-3 mb-3">
            <div class="p-2 border rounded bg-light">
              <span class="text-muted d-block small font-weight-bold">TIPO DOCUMENTAL:</span>
              <span class="badge badge-info text-uppercase font-weight-bold" id="detTipoRadicado">-</span>
            </div>
          </div>
          <div class="col-md-3 mb-3">
            <div class="p-2 border rounded bg-light">
              <span class="text-muted d-block small font-weight-bold">ESTADO OPERATIVO:</span>
              <div id="detEstadoBadge">-</div>
            </div>
          </div>
        </div>

        <div class="card card-outline card-secondary mb-3 shadow-none">
          <div class="card-header py-2 bg-light">
            <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-info-circle mr-1"></i> Información General y Remitente</h6>
          </div>
          <div class="card-body p-3">
            <div class="row">
              <div class="col-md-6 mb-2">
                <span class="text-muted small d-block font-weight-bold">REMITENTE / SOLICITANTE:</span>
                <span class="text-dark font-weight-bold" id="detRemitente">-</span>
              </div>
              <div class="col-md-6 mb-2">
                <span class="text-muted small d-block font-weight-bold">IDENTIFICACIÓN / CONTACTO:</span>
                <span class="text-dark" id="detIdentificacion">-</span>
              </div>
              <div class="col-md-6 mb-2">
                <span class="text-muted small d-block font-weight-bold">DEPENDENCIA DESTINO / ACTUAL:</span>
                <span class="text-dark" id="detDependencia">-</span>
              </div>
              <div class="col-md-6 mb-2">
                <span class="text-muted small d-block font-weight-bold">FUNCIONARIO RESPONSABLE:</span>
                <span class="text-dark" id="detResponsable">-</span>
              </div>
              <div class="col-md-6 mb-2">
                <span class="text-muted small d-block font-weight-bold">FECHA RADICACIÓN OFICIAL:</span>
                <span class="text-dark" id="detFechaRadicado">-</span>
              </div>
              <div class="col-md-6 mb-2">
                <span class="text-muted small d-block font-weight-bold">FECHA LÍMITE LEGAL:</span>
                <span class="text-dark" id="detFechaLimite">-</span>
              </div>
              <div class="col-12 mt-1">
                <span class="text-muted small d-block font-weight-bold">ASUNTO:</span>
                <p class="p-2 bg-light border rounded mb-0 text-dark" id="detAsunto">-</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Clasificación CCD (Cuadro de Clasificación Documental AGN) -->
        <div class="card card-outline card-secondary mb-3 shadow-none">
          <div class="card-header py-2 bg-light">
            <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-sitemap mr-1"></i> Clasificación Archivística CCD</h6>
          </div>
          <div class="card-body p-3">
            <div class="row">
              <div class="col-md-6">
                <span class="text-muted small d-block font-weight-bold">SERIE DOCUMENTAL:</span>
                <span class="text-dark" id="detSerieDocumental">Sin serie clasificada</span>
              </div>
              <div class="col-md-6">
                <span class="text-muted small d-block font-weight-bold">SUBSERIE DOCUMENTAL:</span>
                <span class="text-dark" id="detSubserieDocumental">Sin subserie clasificada</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Documentos y Soportes Digitales Adjuntos -->
        <div class="card card-outline card-secondary mb-0 shadow-none">
          <div class="card-header py-2 bg-light">
            <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-paperclip mr-1"></i> Documentos y Soportes Adjuntos</h6>
          </div>
          <div class="card-body p-2">
            <div class="table-responsive">
              <table class="table table-sm table-bordered table-striped mb-0">
                <thead class="thead-light">
                  <tr>
                    <th>Nombre Archivo</th>
                    <th>Tipo / Formato</th>
                    <th>Tamaño</th>
                    <th>Integridad (SHA-256)</th>
                    <th style="width: 15%; text-align: center;">Acción</th>
                  </tr>
                </thead>
                <tbody id="detTablaDocumentos">
                  <tr><td colspan="5" class="text-center text-muted">Sin documentos</td></tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

      <div class="modal-footer justify-content-between bg-white">
        <button type="button" class="btn btn-outline-info btn-sm" id="btnModalVolverHistorial">
          <i class="fas fa-stream mr-1"></i> Ver Historial / Trazabilidad
        </button>
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Estilos específicos complementarios -->
<style>
  .timeline::before { left: 24px; }
  .timeline > div > .timeline-item { margin-left: 55px; }
  .timeline > div > i { left: 14px; width: 28px; height: 28px; line-height: 28px; }
</style>

<!-- Script de interacción cliente y consumo RESTful -->
<script src="vistas/js/consulta_seguimiento.js"></script>
