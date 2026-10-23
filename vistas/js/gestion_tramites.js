/**
 * Módulo de Gestión de Trámites — Sistema ODIN
 * 
 * Lógica Cliente 100% Desacoplada:
 * - DataTables AJAX para Bandeja Operativa y Catálogo de Trámites.
 * - Indicadores KPI interactivos y Alerta Documental de Vencidos (RF-32).
 * - Filtros avanzados multicriterio (RF-35).
 * - Acciones masivas / por lote (Asignar, Cambiar Estado, Reasignar, Cerrar).
 * - Parametrización y CRUD de Tipos de Trámite (RF-26, RF-28, RF-29).
 * - Notificaciones con SweetAlert2.
 */

const API_RADICADOS_URL = "api/radicados";
const API_TRAMITES_URL  = "api/tramites";
const API_ESTADOS_URL   = "api/estados";
const API_DEPS_URL      = "api/dependencias";
const API_USUARIOS_URL  = "api/usuarios";

let tablaBandeja  = null;
let tablaCatalogo = null;
let seleccionados = new Set();
let catalogoTramitesGlobal = [];
let catalogoEstadosGlobal  = [];
let catalogoDepsGlobal     = [];
let catalogoUsuariosGlobal = [];

$(document).ready(function() {
    inicializarCatalogosSelectores();
    inicializarTablaBandeja();
    inicializarTablaCatalogo();
    cargarEstadisticasKpis();
    configurarEventosFiltros();
    configurarEventosLote();
    configurarEventosCatalogo();
    configurarEventosKpis();
});

/*=============================================
1. CARGA DE CATÁLOGOS AUXILIARES
=============================================*/
function inicializarCatalogosSelectores() {
    // 1. Estados
    fetch(API_ESTADOS_URL)
        .then(r => r.json())
        .then(res => {
            if (res.success && Array.isArray(res.data)) {
                catalogoEstadosGlobal = res.data;
                const options = res.data.map(e => `<option value="${e.id_estado}">${e.nombre}</option>`).join("");
                $("#filtroEstado").append(options);
                $("#tramiteEstadoInicial").append(options);
                $("#selectCambioEstado").append(options);
            }
        })
        .catch(err => console.error("Error al cargar estados:", err));

    // 2. Dependencias
    fetch(API_DEPS_URL)
        .then(r => r.json())
        .then(res => {
            if (res.success && Array.isArray(res.data)) {
                catalogoDepsGlobal = res.data;
                const options = res.data.map(d => `<option value="${d.id_dependencia}">${d.nombre}</option>`).join("");
                $("#filtroDependencia").append(options);
                $("#tramiteDependencia").append(options);
                $("#selectReasignarDependencia").append(options);
            }
        })
        .catch(err => console.error("Error al cargar dependencias:", err));

    // 3. Trámites
    fetch(API_TRAMITES_URL)
        .then(r => r.json())
        .then(res => {
            if (res.success && Array.isArray(res.data)) {
                catalogoTramitesGlobal = res.data;
                const options = res.data.map(t => `<option value="${t.id_tramite}">${t.nombre}</option>`).join("");
                $("#filtroTramite").append(options);
            }
        })
        .catch(err => console.error("Error al cargar trámites:", err));

    // 4. Usuarios
    fetch(API_USUARIOS_URL)
        .then(r => r.json())
        .then(res => {
            if (res.success && Array.isArray(res.data)) {
                catalogoUsuariosGlobal = res.data;
                const options = res.data.map(u => `<option value="${u.id_usuario}">${u.nombre} (${u.num_identificacion || 'ID #' + u.id_usuario})</option>`).join("");
                $("#selectAsignarUsuario").append(options);
                $("#selectReasignarUsuario").append(options);
            }
        })
        .catch(err => console.error("Error al cargar usuarios:", err));
}

/*=============================================
2. CÁLCULO DE DÍAS Y SEMÁFORO DE VENCIMIENTO
=============================================*/
function calcularDiasRestantes(fechaVencimiento) {
    if (!fechaVencimiento) return null;
    const fechaVenc = new Date(fechaVencimiento.replace(/-/g, "/"));
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);
    fechaVenc.setHours(0, 0, 0, 0);

    const diferenciaMs = fechaVenc.getTime() - hoy.getTime();
    return Math.round(diferenciaMs / (1000 * 60 * 60 * 24));
}

function renderizarBadgeSemaforo(dias, idEstado) {
    // Si ya está finalizado (estado 4, 6 o 7)
    if (idEstado == 4 || idEstado == 6 || idEstado == 7) {
        return `<span class="badge badge-success px-2 py-1"><i class="fas fa-check-double mr-1"></i>Cerrado</span>`;
    }

    if (dias === null || isNaN(dias)) {
        return `<span class="badge badge-light text-muted px-2 py-1">—</span>`;
    }

    if (dias < 0) {
        return `<span class="badge badge-danger px-2 py-1 font-weight-bold" title="Trámite vencido hace ${Math.abs(dias)} día(s)"><i class="fas fa-ban mr-1"></i>Vencido (${Math.abs(dias)} d)</span>`;
    } else if (dias <= 3) {
        return `<span class="badge badge-warning text-dark px-2 py-1 font-weight-bold" title="Próximo a vencer"><i class="fas fa-exclamation-triangle mr-1"></i>${dias} d rest.</span>`;
    } else {
        return `<span class="badge badge-success px-2 py-1 font-weight-bold" title="En término regular"><i class="fas fa-shield-alt mr-1"></i>${dias} d rest.</span>`;
    }
}

/*=============================================
3. INICIALIZAR DATATABLE DE BANDEJA OPERATIVA
=============================================*/
function inicializarTablaBandeja() {
    if ($.fn.DataTable.isDataTable("#tblBandejaTramites")) {
        $("#tblBandejaTramites").DataTable().destroy();
    }

    tablaBandeja = $("#tblBandejaTramites").DataTable({
        ajax: {
            url: API_RADICADOS_URL,
            type: "GET",
            dataSrc: function(json) {
                if (json && json.data) {
                    return json.data;
                }
                return [];
            },
            error: function() {
                Swal.fire({
                    icon: "error",
                    title: "Error de conexión",
                    text: "No fue posible cargar los datos de la bandeja operativa de trámites."
                });
            }
        },
        columns: [
            // 0. Checkbox para selección en lote
            {
                data: "id_radicado",
                orderable: false,
                searchable: false,
                className: "text-center",
                render: function(data) {
                    const checked = seleccionados.has(String(data)) ? "checked" : "";
                    return `<input type="checkbox" class="checkFilaTramite" value="${data}" ${checked}>`;
                }
            },
            // 1. Radicado
            {
                data: "numero_radicado",
                render: function(data, type, row) {
                    return `<a href="javascript:void(0)" class="font-weight-bold text-primary btnVerDetalle" data-id="${row.id_radicado}" title="Ver detalle del radicado">
                                <i class="fas fa-folder-open mr-1"></i>${data}
                            </a>`;
                }
            },
            // 2. Asunto
            {
                data: "asunto",
                render: function(data) {
                    return `<span class="d-inline-block text-truncate" style="max-width: 230px;" title="${data || ''}">${data || 'Sin asunto'}</span>`;
                }
            },
            // 3. Trámite
            {
                data: "tramite",
                render: function(data) {
                    return data ? `<span class="badge badge-light border font-weight-normal px-2 py-1">${data}</span>` : '<span class="text-muted small">No asignado</span>';
                }
            },
            // 4. Estado
            {
                data: "estado",
                render: function(data, type, row) {
                    const color = row.estado_color || "#6c757d";
                    return `<span class="badge text-white px-2 py-1 font-weight-bold" style="background-color: ${color};">${data || 'Sin estado'}</span>`;
                }
            },
            // 5. Dependencia
            {
                data: "dependencia",
                render: function(data) {
                    return data ? `<small class="font-weight-bold text-secondary"><i class="fas fa-building mr-1"></i>${data}</small>` : '<small class="text-muted">Sin dependencia</small>';
                }
            },
            // 6. Responsable
            {
                data: "responsable",
                render: function(data) {
                    return `<small class="text-dark"><i class="fas fa-user mr-1 text-muted"></i>${data || 'Sin asignar'}</small>`;
                }
            },
            // 7. Prioridad
            {
                data: "prioridad",
                render: function(data) {
                    const prio = (data || 'MEDIA').toUpperCase();
                    let badgeClass = "badge-info";
                    if (prio === "BAJA") badgeClass = "badge-secondary";
                    if (prio === "ALTA") badgeClass = "badge-warning text-dark";
                    if (prio === "URGENTE") badgeClass = "badge-danger";
                    return `<span class="badge ${badgeClass} px-2 py-1">${prio}</span>`;
                }
            },
            // 8. Vencimiento
            {
                data: "fecha_vencimiento",
                render: function(data, type, row) {
                    const fecha = data || row.fecha_limite;
                    if (!fecha) return '<span class="text-muted small">Sin fecha</span>';
                    return `<small class="font-weight-bold"><i class="far fa-calendar-alt mr-1"></i>${fecha}</small>`;
                }
            },
            // 9. Días Restantes / Semáforo
            {
                data: null,
                render: function(data, type, row) {
                    const fechaVenc = row.fecha_vencimiento || row.fecha_limite;
                    const dias = calcularDiasRestantes(fechaVenc);
                    return renderizarBadgeSemaforo(dias, row.id_estado);
                }
            },
            // 10. Acciones
            {
                data: null,
                orderable: false,
                searchable: false,
                className: "text-center",
                render: function(data, type, row) {
                    let btnDoc = "";
                    if (row.ruta_documento) {
                        btnDoc = `<a class="btn btn-xs btn-outline-info mr-1" href="${row.ruta_documento}" target="_blank" title="Ver documento adjunto">
                                    <i class="fas fa-file-pdf"></i>
                                  </a>`;
                    }
                    return `<div class="btn-group">
                                <button type="button" class="btn btn-xs btn-primary btnVerDetalle mr-1" data-id="${row.id_radicado}" title="Ver detalle completo">
                                    <i class="fas fa-eye"></i>
                                </button>
                                ${btnDoc}
                            </div>`;
                }
            }
        ],
        order: [[1, "desc"]],
        responsive: true,
        autoWidth: false,
        language: {
            sProcessing:     "Cargando bandeja de trámites...",
            sLengthMenu:     "Mostrar _MENU_ registros",
            sZeroRecords:    "No se encontraron trámites coincidentes con los filtros",
            sEmptyTable:     "No existen trámites registrados en la bandeja",
            sInfo:           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_",
            sInfoEmpty:      "Mostrando 0 de 0 trámites",
            sInfoFiltered:   "(filtrado de un total de _MAX_ registros)",
            sSearch:         "Buscar:",
            oPaginate: {
                sFirst:    "Primero",
                sLast:     "Último",
                sNext:     "Siguiente",
                sPrevious: "Anterior"
            }
        },
        drawCallback: function() {
            actualizarEstadoCheckboxes();
        }
    });

    // Filtros personalizados en DataTables
    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.sTableId !== "tblBandejaTramites") return true;

        const row = tablaBandeja.row(dataIndex).data();
        if (!row) return true;

        // 1. Filtro por texto
        const textoFiltro = $("#filtroTexto").val().toLowerCase().trim();
        if (textoFiltro) {
            const rad = (row.numero_radicado || "").toLowerCase();
            const asu = (row.asunto || "").toLowerCase();
            const rem = (row.remitente || "").toLowerCase();
            const res = (row.responsable || "").toLowerCase();
            if (!rad.includes(textoFiltro) && !asu.includes(textoFiltro) && !rem.includes(textoFiltro) && !res.includes(textoFiltro)) {
                return false;
            }
        }

        // 2. Filtro por estado
        const estadoFiltro = $("#filtroEstado").val();
        if (estadoFiltro && String(row.id_estado) !== String(estadoFiltro)) {
            return false;
        }

        // 3. Filtro por dependencia
        const depFiltro = $("#filtroDependencia").val();
        if (depFiltro && String(row.id_dependencia) !== String(depFiltro)) {
            return false;
        }

        // 4. Filtro por trámite
        const tramiteFiltro = $("#filtroTramite").val();
        if (tramiteFiltro && String(row.id_tramite) !== String(tramiteFiltro)) {
            return false;
        }

        // 5. Filtro por fecha desde / hasta
        const fechaDesde = $("#filtroFechaDesde").val();
        const fechaHasta = $("#filtroFechaHasta").val();
        if (fechaDesde || fechaHasta) {
            const fechaRad = (row.fecha_radicado || "").substring(0, 10);
            if (fechaDesde && fechaRad < fechaDesde) return false;
            if (fechaHasta && fechaRad > fechaHasta) return false;
        }

        // 6. Filtro por criterio de vencimiento
        const vencFiltro = $("#filtroVencimiento").val();
        if (vencFiltro) {
            const dias = calcularDiasRestantes(row.fecha_vencimiento || row.fecha_limite);
            const cerrado = (row.id_estado == 4 || row.id_estado == 6 || row.id_estado == 7);

            if (vencFiltro === "vencidos") {
                if (cerrado || dias === null || dias >= 0) return false;
            } else if (vencFiltro === "proximos") {
                if (cerrado || dias === null || dias < 0 || dias > 3) return false;
            } else if (vencFiltro === "en_plazo") {
                if (cerrado || dias === null || dias <= 3) return false;
            }
        }

        return true;
    });

    // Evento de checkboxes individuales
    $("#tblBandejaTramites").on("change", ".checkFilaTramite", function() {
        const id = $(this).val();
        if ($(this).is(":checked")) {
            seleccionados.add(id);
        } else {
            seleccionados.delete(id);
        }
        actualizarEstadoCheckboxes();
    });

    // Evento de seleccionar todos
    $("#checkAllTramites").on("change", function() {
        const checkAll = $(this).is(":checked");
        $("#tblBandejaTramites .checkFilaTramite").each(function() {
            const id = $(this).val();
            $(this).prop("checked", checkAll);
            if (checkAll) {
                seleccionados.add(id);
            } else {
                seleccionados.delete(id);
            }
        });
        actualizarEstadoCheckboxes();
    });

    // Evento Ver Detalle de Radicado
    $("#tblBandejaTramites").on("click", ".btnVerDetalle", function(e) {
        e.preventDefault();
        const id = $(this).data("id");
        abrirModalDetalleRadicado(id);
    });
}

function actualizarEstadoCheckboxes() {
    const totalVisibles = $("#tblBandejaTramites .checkFilaTramite").length;
    const marcadosVisibles = $("#tblBandejaTramites .checkFilaTramite:checked").length;

    $("#checkAllTramites").prop("checked", totalVisibles > 0 && totalVisibles === marcadosVisibles);

    const cantidad = seleccionados.size;
    $("#badgeSeleccionados").text(`${cantidad} seleccionado(s)`);
    $(".totalRadicadosModal").text(cantidad);

    const habilitar = cantidad > 0;
    $("#btnAccionAsignar").prop("disabled", !habilitar);
    $("#btnAccionEstado").prop("disabled", !habilitar);
    $("#btnAccionReasignar").prop("disabled", !habilitar);
    $("#btnAccionCerrar").prop("disabled", !habilitar);
}

/*=============================================
4. INICIALIZAR DATATABLE DE CATÁLOGO DE TRÁMITES
=============================================*/
function inicializarTablaCatalogo() {
    if ($.fn.DataTable.isDataTable("#tblCatalogoTramites")) {
        $("#tblCatalogoTramites").DataTable().destroy();
    }

    tablaCatalogo = $("#tblCatalogoTramites").DataTable({
        ajax: {
            url: API_TRAMITES_URL,
            type: "GET",
            dataSrc: "data",
            error: function() {
                Swal.fire("Error", "No fue posible cargar el catálogo de trámites.", "error");
            }
        },
        columns: [
            { data: "id_tramite", className: "text-center font-weight-bold" },
            {
                data: "nombre",
                render: function(data, type, row) {
                    return `<strong>${data}</strong>
                            <div class="small text-muted text-truncate" style="max-width: 320px;" title="${row.descripcion || ''}">
                                ${row.descripcion || 'Sin descripción'}
                            </div>`;
                }
            },
            {
                data: "dias_respuesta",
                className: "text-center",
                render: function(data) {
                    return `<span class="badge badge-info px-2 py-1 font-weight-bold">${data || 15} días</span>`;
                }
            },
            {
                data: "prioridad_default",
                className: "text-center",
                render: function(data) {
                    const prio = (data || 'MEDIA').toUpperCase();
                    let color = "badge-secondary";
                    if (prio === "ALTA") color = "badge-warning text-dark";
                    if (prio === "URGENTE") color = "badge-danger";
                    if (prio === "MEDIA") color = "badge-info";
                    return `<span class="badge ${color} px-2 py-1">${prio}</span>`;
                }
            },
            {
                data: "requiere_respuesta",
                className: "text-center",
                render: function(data) {
                    return (data == 1 || data === true)
                        ? '<span class="badge badge-success px-2 py-1"><i class="fas fa-check mr-1"></i>Sí</span>'
                        : '<span class="badge badge-secondary px-2 py-1"><i class="fas fa-times mr-1"></i>No</span>';
                }
            },
            {
                data: "dependencia_nombre",
                render: function(data) {
                    return `<small class="font-weight-bold text-dark"><i class="fas fa-building mr-1 text-muted"></i>${data || 'Sin asignar'}</small>`;
                }
            },
            {
                data: "activo",
                className: "text-center",
                render: function(data, type, row) {
                    const esActivo = (data == 1 || data === true);
                    const btnClass = esActivo ? "btn-success" : "btn-danger";
                    const texto    = esActivo ? "Activo" : "Inactivo";
                    return `<button type="button" class="btn btn-xs ${btnClass} btnToggleEstadoTramite" data-id="${row.id_tramite}" data-estado="${esActivo ? 0 : 1}" title="Haga clic para cambiar estado">
                                ${texto}
                            </button>`;
                }
            },
            {
                data: null,
                orderable: false,
                searchable: false,
                className: "text-center",
                render: function(data, type, row) {
                    return `<button type="button" class="btn btn-warning btn-sm btnEditarTipoTramite" data-id="${row.id_tramite}" title="Editar configuración del trámite">
                                <i class="fas fa-edit"></i>
                            </button>`;
                }
            }
        ],
        order: [[0, "desc"]],
        responsive: true,
        autoWidth: false,
        language: {
            sProcessing:     "Cargando catálogo...",
            sLengthMenu:     "Mostrar _MENU_ tipos",
            sZeroRecords:    "No se encontraron tipos de trámite",
            sEmptyTable:     "No hay tipos de trámite parametrizados",
            sInfo:           "Mostrando _START_ al _END_ de _TOTAL_ tipos",
            sInfoEmpty:      "Mostrando 0 tipos",
            sInfoFiltered:   "(filtrado de _MAX_ registros)",
            sSearch:         "Buscar tipo:",
            oPaginate: {
                sFirst:    "Primero",
                sLast:     "Último",
                sNext:     "Siguiente",
                sPrevious: "Anterior"
            }
        }
    });

    // Cambiar estado activo / inactivo de un trámite
    $("#tblCatalogoTramites").on("click", ".btnToggleEstadoTramite", function() {
        const idTramite = $(this).data("id");
        const nuevoEstado = $(this).data("estado");

        fetch(`${API_TRAMITES_URL}/${idTramite}/estado`, {
            method: "PATCH",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ activo: nuevoEstado })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                Swal.fire({
                    icon: "success",
                    title: "Estado actualizado",
                    text: res.message,
                    timer: 1500,
                    showConfirmButton: false
                });
                tablaCatalogo.ajax.reload(null, false);
            } else {
                Swal.fire("Error", res.message || "No fue posible alternar el estado del trámite.", "error");
            }
        })
        .catch(() => Swal.fire("Error de red", "No se pudo comunicar con el servidor.", "error"));
    });

    // Abrir modal de edición
    $("#tblCatalogoTramites").on("click", ".btnEditarTipoTramite", function() {
        const idTramite = $(this).data("id");
        fetch(`${API_TRAMITES_URL}/${idTramite}`)
            .then(r => r.json())
            .then(res => {
                if (res.success && res.data) {
                    const t = res.data;
                    $("#tramiteId").val(t.id_tramite);
                    $("#tramiteNombre").val(t.nombre);
                    $("#tramiteDescripcion").val(t.descripcion);
                    $("#tramiteDiasRespuesta").val(t.dias_respuesta || 15);
                    $("#tramitePrioridad").val(t.prioridad_default || "MEDIA");
                    $("#tramiteDependencia").val(t.id_dependencia_responsable || "");
                    $("#tramiteEstadoInicial").val(t.id_estado_inicial || "");
                    $("#tramiteRequiereRespuesta").prop("checked", t.requiere_respuesta == 1 || t.requiere_respuesta === true);
                    $("#tramiteActivo").prop("checked", t.activo == 1 || t.activo === true);

                    $("#modalTipoTramiteLabel").html('<i class="fas fa-edit mr-2"></i> Editar Tipo de Trámite');
                    $("#modalTipoTramite").modal("show");
                }
            })
            .catch(() => Swal.fire("Error", "No se pudo consultar la información del trámite.", "error"));
    });
}

/*=============================================
5. CARGA DE ESTADÍSTICAS Y ALERTAS (KPIS)
=============================================*/
function cargarEstadisticasKpis() {
    fetch(`${API_TRAMITES_URL}/stats`)
        .then(r => r.json())
        .then(res => {
            if (res.success && res.data) {
                const s = res.data;
                $("#kpiPendientes").text(s.pendientes || 0);
                $("#kpiEnProceso").text(s.en_proceso || 0);
                $("#kpiFinalizados").text(s.finalizados || 0);
                $("#kpiVencidos").text(s.vencidos || 0);
                $("#kpiTotal").text(s.total || 0);

                // Alerta Documental de Vencidos (RF-32)
                if (s.vencidos && s.vencidos > 0) {
                    $("#contadorVencidosAlerta").text(s.vencidos);
                    $("#alertaVencidos").slideDown();
                } else {
                    $("#alertaVencidos").slideUp();
                }
            }
        })
        .catch(err => console.error("Error al cargar KPIs:", err));
}

/*=============================================
6. EVENTOS DE FILTROS Y KPIS INTERACTIVOS
=============================================*/
function configurarEventosFiltros() {
    $("#frmFiltrosBandeja").on("submit", function(e) {
        e.preventDefault();
        tablaBandeja.draw();
    });

    $("#filtroTexto").on("keyup", function() {
        tablaBandeja.draw();
    });

    $("#filtroEstado, #filtroDependencia, #filtroTramite, #filtroVencimiento, #filtroFechaDesde, #filtroFechaHasta").on("change", function() {
        tablaBandeja.draw();
    });

    $("#btnLimpiarFiltros").on("click", function() {
        $("#frmFiltrosBandeja")[0].reset();
        tablaBandeja.draw();
    });

    $("#btnFiltrarVencidosAlerta").on("click", function() {
        $("#tabsGestionTramites a#tab-bandeja-link").tab("show");
        $("#filtroVencimiento").val("vencidos");
        tablaBandeja.draw();
    });
}

function configurarEventosKpis() {
    $(".kpi-tarjeta").on("click", function() {
        const kpi = $(this).data("kpi");
        $("#tabsGestionTramites a#tab-bandeja-link").tab("show");

        // Limpiar otros filtros para focalizar en el KPI clickeado
        $("#filtroTexto").val("");
        $("#filtroDependencia").val("");
        $("#filtroTramite").val("");
        $("#filtroFechaDesde").val("");
        $("#filtroFechaHasta").val("");

        if (kpi === "pendientes") {
            $("#filtroVencimiento").val("");
            // Buscar id_estado para Pendiente
            const pend = catalogoEstadosGlobal.find(e => e.nombre.toLowerCase().includes("pendiente"));
            $("#filtroEstado").val(pend ? pend.id_estado : "1");
        } else if (kpi === "en_proceso") {
            $("#filtroVencimiento").val("");
            const proc = catalogoEstadosGlobal.find(e => e.nombre.toLowerCase().includes("proceso") || e.nombre.toLowerCase().includes("trámite"));
            $("#filtroEstado").val(proc ? proc.id_estado : "2");
        } else if (kpi === "finalizados") {
            $("#filtroVencimiento").val("");
            const fin = catalogoEstadosGlobal.find(e => e.nombre.toLowerCase().includes("finaliz") || e.nombre.toLowerCase().includes("cerrad"));
            $("#filtroEstado").val(fin ? fin.id_estado : "6");
        } else if (kpi === "vencidos") {
            $("#filtroEstado").val("");
            $("#filtroVencimiento").val("vencidos");
        } else {
            $("#filtroEstado").val("");
            $("#filtroVencimiento").val("");
        }

        tablaBandeja.draw();
    });
}

/*=============================================
7. EVENTOS DE ACCIONES EN LOTE (MASS ACTIONS)
=============================================*/
function configurarEventosLote() {
    // 1. Modal Asignar Responsable
    $("#btnAccionAsignar").on("click", function() {
        if (!seleccionados.size) return;
        $("#frmAsignarResponsable")[0].reset();
        $("#modalAsignarResponsable").modal("show");
    });

    $("#frmAsignarResponsable").on("submit", function(e) {
        e.preventDefault();
        const idUsuario = $("#selectAsignarUsuario").val();
        if (!idUsuario) {
            Swal.fire("Atención", "Debe seleccionar un usuario responsable.", "warning");
            return;
        }

        ejecutarAccionLote({
            accion: "asignar",
            ids: Array.from(seleccionados),
            usuario: idUsuario
        }, "#modalAsignarResponsable", "Responsable asignado correctamente.");
    });

    // 2. Modal Cambiar Estado
    $("#btnAccionEstado").on("click", function() {
        if (!seleccionados.size) return;
        $("#frmCambiarEstado")[0].reset();
        $("#modalCambiarEstado").modal("show");
    });

    $("#frmCambiarEstado").on("submit", function(e) {
        e.preventDefault();
        const idEstado = $("#selectCambioEstado").val();
        if (!idEstado) {
            Swal.fire("Atención", "Debe seleccionar el nuevo estado.", "warning");
            return;
        }

        ejecutarAccionLote({
            accion: "estado",
            ids: Array.from(seleccionados),
            estado: idEstado,
            observacion: $("#observacionCambioEstado").val()
        }, "#modalCambiarEstado", "Estado actualizado exitosamente.");
    });

    // 3. Modal Reasignar
    $("#btnAccionReasignar").on("click", function() {
        if (!seleccionados.size) return;
        $("#frmReasignar")[0].reset();
        $("#modalReasignar").modal("show");
    });

    $("#frmReasignar").on("submit", function(e) {
        e.preventDefault();
        const idUsuario = $("#selectReasignarUsuario").val();
        if (!idUsuario) {
            Swal.fire("Atención", "Debe seleccionar el nuevo usuario responsable.", "warning");
            return;
        }

        ejecutarAccionLote({
            accion: "reasignar",
            ids: Array.from(seleccionados),
            usuarioNuevo: idUsuario,
            dependenciaNueva: $("#selectReasignarDependencia").val(),
            observacion: $("#observacionReasignar").val()
        }, "#modalReasignar", "Trámites reasignados correctamente.");
    });

    // 4. Modal Cerrar / Finalizar
    $("#btnAccionCerrar").on("click", function() {
        if (!seleccionados.size) return;
        $("#frmCerrarTramite")[0].reset();
        $("#modalCerrarTramite").modal("show");
    });

    $("#frmCerrarTramite").on("submit", function(e) {
        e.preventDefault();
        ejecutarAccionLote({
            accion: "cerrar",
            ids: Array.from(seleccionados),
            observacion: $("#observacionCierre").val()
        }, "#modalCerrarTramite", "Trámite(s) cerrado(s) y finalizado(s) exitosamente.");
    });
}

function ejecutarAccionLote(payload, idModal, mensajeExito) {
    Swal.fire({
        title: "¿Confirmar operación?",
        text: `Se procesará la acción para ${payload.ids.length} radicado(s) seleccionado(s).`,
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí, procesar",
        cancelButtonText: "Cancelar"
    }).then(res => {
        if (!res.isConfirmed) return;

        Swal.showLoading();
        fetch(`${API_RADICADOS_URL}/lote`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                $(idModal).modal("hide");
                Swal.fire({
                    icon: "success",
                    title: "Operación exitosa",
                    text: data.message || mensajeExito,
                    timer: 2000,
                    showConfirmButton: false
                });
                seleccionados.clear();
                actualizarEstadoCheckboxes();
                tablaBandeja.ajax.reload(null, false);
                cargarEstadisticasKpis();
            } else {
                Swal.fire("Error en la operación", data.message || "Ocurrió un error al procesar el lote.", "error");
            }
        })
        .catch(() => {
            Swal.fire("Error de conexión", "No fue posible comunicarse con el servidor.", "error");
        });
    });
}

/*=============================================
8. GESTIÓN DEL FORMULARIO DE TIPOS DE TRÁMITE
=============================================*/
function configurarEventosCatalogo() {
    $("#btnAbrirModalNuevoTipo").on("click", function() {
        $("#frmTipoTramite")[0].reset();
        $("#tramiteId").val("");
        $("#tramiteActivo").prop("checked", true);
        $("#tramiteRequiereRespuesta").prop("checked", true);
        $("#modalTipoTramiteLabel").html('<i class="fas fa-file-alt mr-2"></i> Nuevo Tipo de Trámite');
        $("#modalTipoTramite").modal("show");
    });

    $("#frmTipoTramite").on("submit", function(e) {
        e.preventDefault();

        const idTramite = $("#tramiteId").val();
        const payload = {
            nombre:                     $("#tramiteNombre").val().trim(),
            descripcion:                $("#tramiteDescripcion").val().trim(),
            dias_respuesta:             parseInt($("#tramiteDiasRespuesta").val(), 10) || 15,
            prioridad_default:          $("#tramitePrioridad").val(),
            id_dependencia_responsable: $("#tramiteDependencia").val() || null,
            id_estado_inicial:          $("#tramiteEstadoInicial").val() || null,
            requiere_respuesta:         $("#tramiteRequiereRespuesta").is(":checked") ? 1 : 0,
            activo:                     $("#tramiteActivo").is(":checked") ? 1 : 0
        };

        const esEdicion = Boolean(idTramite);
        const url       = esEdicion ? `${API_TRAMITES_URL}/${idTramite}` : API_TRAMITES_URL;
        const method    = esEdicion ? "PUT" : "POST";

        $("#btnGuardarTipoTramite").prop("disabled", true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...');

        fetch(url, {
            method: method,
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        })
        .then(r => r.json())
        .then(res => {
            $("#btnGuardarTipoTramite").prop("disabled", false).html('<i class="fas fa-save mr-1"></i> Guardar Trámite');
            if (res.success) {
                $("#modalTipoTramite").modal("hide");
                Swal.fire({
                    icon: "success",
                    title: "¡Éxito!",
                    text: res.message,
                    timer: 1800,
                    showConfirmButton: false
                });
                tablaCatalogo.ajax.reload(null, false);
                // Actualizar selector de trámites de los filtros
                fetch(API_TRAMITES_URL)
                    .then(r => r.json())
                    .then(tRes => {
                        if (tRes.success && Array.isArray(tRes.data)) {
                            catalogoTramitesGlobal = tRes.data;
                            const options = '<option value="">Todos los tipos</option>' + tRes.data.map(t => `<option value="${t.id_tramite}">${t.nombre}</option>`).join("");
                            $("#filtroTramite").html(options);
                        }
                    });
            } else {
                Swal.fire("Error", res.message || "No se pudo guardar el tipo de trámite.", "error");
            }
        })
        .catch(() => {
            $("#btnGuardarTipoTramite").prop("disabled", false).html('<i class="fas fa-save mr-1"></i> Guardar Trámite');
            Swal.fire("Error de red", "No fue posible conectar con el servidor.", "error");
        });
    });
}

/*=============================================
9. MODAL DETALLE DE RADICADO COMPLETO
=============================================*/
function abrirModalDetalleRadicado(idRadicado) {
    $("#cuerpoDetalleRadicado").html(`
        <div class="text-center py-5">
            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
            <p class="mt-2 text-muted">Cargando expediente del radicado #${idRadicado}...</p>
        </div>
    `);
    $("#detalleNumeroRadicado").text(`#${idRadicado}`);
    $("#modalDetalleRadicado").modal("show");

    fetch(`${API_RADICADOS_URL}/${idRadicado}`)
        .then(r => r.json())
        .then(res => {
            if (!res.success || !res.data) {
                $("#cuerpoDetalleRadicado").html('<div class="alert alert-danger">No se pudo cargar la información del radicado.</div>');
                return;
            }

            const r = res.data;
            $("#detalleNumeroRadicado").text(r.numero_radicado || `#${r.id_radicado}`);

            // Documentos adjuntos
            let htmlDocs = '<p class="text-muted small mb-0">No hay documentos digitales adjuntos a este radicado.</p>';
            if (Array.isArray(r.documentos) && r.documentos.length > 0) {
                htmlDocs = `<div class="list-group">` + r.documentos.map(doc => `
                    <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2">
                        <div>
                            <i class="fas fa-file-alt text-primary mr-2"></i>
                            <strong>${doc.nombre_archivo || doc.nombre || 'Documento'}</strong>
                            <div class="small text-muted">MIME: ${doc.mime_type || 'N/A'} | Subido: ${doc.fecha_subida || ''}</div>
                        </div>
                        <a href="${doc.ruta_archivo}" class="btn btn-sm btn-outline-primary" target="_blank" download="${doc.nombre_archivo || 'documento'}">
                            <i class="fas fa-download mr-1"></i> Descargar
                        </a>
                    </div>
                `).join("") + `</div>`;
            }

            const dias = calcularDiasRestantes(r.fecha_vencimiento || r.fecha_limite);
            const semaforo = renderizarBadgeSemaforo(dias, r.id_estado);

            const contenidoHtml = `
                <div class="row">
                    <!-- Columna Izquierda: Datos del Radicado -->
                    <div class="col-md-6">
                        <div class="card card-outline card-primary shadow-none border mb-3">
                            <div class="card-header py-2">
                                <h6 class="card-title font-weight-bold mb-0 text-primary"><i class="fas fa-info-circle mr-1"></i> Información General</h6>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-striped mb-0">
                                    <tbody>
                                        <tr><th style="width: 40%;">Número Radicado:</th><td><strong>${r.numero_radicado || '-'}</strong></td></tr>
                                        <tr><th>Tipo Radicación:</th><td><span class="badge badge-secondary">${r.tipo_radicado || 'ENTRADA'}</span></td></tr>
                                        <tr><th>Asunto:</th><td>${r.asunto || '-'}</td></tr>
                                        <tr><th>Tipo de Trámite:</th><td>${r.tramite_nombre || 'No asignado'}</td></tr>
                                        <tr><th>Estado Actual:</th><td><span class="badge text-white px-2 py-1" style="background-color:${r.estado_color || '#6c757d'}">${r.estado_nombre || 'Sin estado'}</span></td></tr>
                                        <tr><th>Dependencia Destino:</th><td>${r.dependencia_nombre || 'Sin dependencia'}</td></tr>
                                        <tr><th>Prioridad:</th><td><span class="badge badge-info">${r.prioridad || 'MEDIA'}</span></td></tr>
                                        <tr><th>Medio de Recepción:</th><td>${r.medio_recepcion || 'Ventanilla'}</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card card-outline card-warning shadow-none border mb-3">
                            <div class="card-header py-2">
                                <h6 class="card-title font-weight-bold mb-0 text-warning"><i class="far fa-clock mr-1"></i> Términos y Vencimiento</h6>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-striped mb-0">
                                    <tbody>
                                        <tr><th style="width: 40%;">Fecha Radicación:</th><td>${r.fecha_radicado || '-'}</td></tr>
                                        <tr><th>Fecha Límite / Vencimiento:</th><td><strong>${r.fecha_vencimiento || r.fecha_limite || 'Sin fecha asignada'}</strong></td></tr>
                                        <tr><th>Estado del Plazo:</th><td>${semaforo}</td></tr>
                                        <tr><th>Fecha de Cierre:</th><td>${r.fecha_cierre ? `<span class="text-success font-weight-bold">${r.fecha_cierre}</span>` : '<span class="text-muted">En trámite</span>'}</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Columna Derecha: Solicitante y Documentos -->
                    <div class="col-md-6">
                        <div class="card card-outline card-info shadow-none border mb-3">
                            <div class="card-header py-2">
                                <h6 class="card-title font-weight-bold mb-0 text-info"><i class="fas fa-user mr-1"></i> Remitente / Solicitante</h6>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-striped mb-0">
                                    <tbody>
                                        <tr><th style="width: 40%;">Nombre / Razón Social:</th><td><strong>${r.remitente || 'Anónimo'}</strong></td></tr>
                                        <tr><th>Confidencialidad:</th><td><span class="badge badge-light border">${r.confidencialidad || 'PUBLICO'}</span></td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card card-outline card-success shadow-none border mb-0">
                            <div class="card-header py-2">
                                <h6 class="card-title font-weight-bold mb-0 text-success"><i class="fas fa-paperclip mr-1"></i> Documentos Adjuntos y Expediente</h6>
                            </div>
                            <div class="card-body">
                                ${htmlDocs}
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $("#cuerpoDetalleRadicado").html(contenidoHtml);
        })
        .catch(() => {
            $("#cuerpoDetalleRadicado").html('<div class="alert alert-danger">Error de comunicación al obtener el detalle del radicado.</div>');
        });
}
