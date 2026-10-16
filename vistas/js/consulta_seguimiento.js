/**
 * Script de Módulo: Consulta y Seguimiento de Radicados — Proyecto ODIN
 * Consumo 100% asíncrono de la API RESTful de ODIN.
 */

const API_RADICADOS_URL = "api/radicados";
const API_ESTADOS_URL   = "api/estados";

let tablaSeguimiento = null;

$(document).ready(function() {
    inicializarTablaSeguimiento();
    cargarCatalogoEstados();
    configurarEventosFiltros();
});

/*=============================================
1. INICIALIZAR DATATABLE DE RADICADOS VÍA AJAX
=============================================*/
function inicializarTablaSeguimiento() {
    if ($.fn.DataTable.isDataTable("#tblConsultaSeguimiento")) {
        $("#tblConsultaSeguimiento").DataTable().destroy();
    }

    tablaSeguimiento = $("#tblConsultaSeguimiento").DataTable({
        ajax: {
            url: API_RADICADOS_URL,
            type: "GET",
            dataSrc: function(json) {
                if (json && json.data) {
                    return json.data;
                }
                return [];
            },
            error: function(xhr) {
                const mensaje = xhr.responseJSON?.message || "No fue posible conectar con el servidor de la API.";
                Swal.fire({
                    icon: "error",
                    title: "Error al cargar radicados",
                    text: mensaje,
                    confirmButtonText: "Entendido"
                });
            }
        },
        columns: [
            // 1. Acciones (Descargar / Visualizar)
            {
                data: null,
                orderable: false,
                searchable: false,
                width: "15%",
                render: function(data, type, row) {
                    let btnDescargar = "";
                    let btnVisualizar = "";

                    if (row.ruta_documento) {
                        btnDescargar = `
                            <a class="btn btn-primary btn-sm mb-1" 
                               href="${row.ruta_documento}" 
                               download="${row.nombre_archivo || 'radicado_' + row.numero_radicado}" 
                               target="_blank" 
                               title="Descargar documento adjunto">
                                <i class="fas fa-download mr-1"></i> Descargar
                            </a>`;
                        btnVisualizar = `
                            <a class="btn btn-info btn-sm" 
                               href="${row.ruta_documento}" 
                               target="_blank" 
                               title="Visualizar documento en visor">
                                <i class="far fa-eye mr-1"></i> Visualizar
                            </a>`;
                    } else {
                        btnDescargar = `
                            <button class="btn btn-secondary btn-sm mb-1 btnSinArchivo" 
                                    type="button" 
                                    title="Sin archivo digital adjunto" 
                                    onclick="Swal.fire('Sin archivo', 'Este radicado no cuenta con archivos digitales adjuntos.', 'info')">
                                <i class="fas fa-file-excel mr-1"></i> Sin archivo
                            </button>`;
                        btnVisualizar = `
                            <button class="btn btn-secondary btn-sm btnSinArchivo" 
                                    type="button" 
                                    title="Sin archivo digital adjunto" 
                                    onclick="Swal.fire('Sin archivo', 'Este radicado no cuenta con archivos digitales adjuntos.', 'info')">
                                <i class="far fa-eye-slash mr-1"></i> No disponible
                            </button>`;
                    }

                    return `<div class="d-flex flex-column">${btnDescargar}${btnVisualizar}</div>`;
                }
            },

            // 2. Radicado
            {
                data: "numero_radicado",
                render: function(data, type, row) {
                    const asunto = row.asunto 
                        ? `<br><small class="text-muted d-inline-block text-truncate" style="max-width: 280px;" title="${row.asunto}"><b>Asunto:</b> ${row.asunto}</small>` 
                        : "";
                    const remitente = row.remitente 
                        ? `<br><small class="text-secondary"><i class="far fa-address-card mr-1"></i>${row.remitente}</small>` 
                        : "";
                    return `<strong class="text-primary font-weight-bold">${data || 'S/N'}</strong>${asunto}${remitente}`;
                }
            },

            // 3. Tipo Documento
            {
                data: "tipo_radicado",
                render: function(data, type, row) {
                    const tipo = data || (row.tramite ? row.tramite : "Documento General");
                    let badgeClass = "badge-info";
                    if (tipo === "ENTRADA") badgeClass = "badge-success";
                    if (tipo === "SALIDA") badgeClass = "badge-warning";
                    if (tipo === "INTERNO") badgeClass = "badge-secondary";

                    return `<span class="badge ${badgeClass} text-uppercase px-2 py-1">
                                <i class="far fa-file-alt mr-1"></i>${tipo}
                            </span>`;
                }
            },

            // 4. Fecha Registro
            {
                data: "fecha_radicado",
                render: function(data) {
                    if (!data) return "<span class='text-muted'>Sin fecha</span>";
                    return `<span><i class="far fa-calendar-alt mr-1 text-muted"></i>${data}</span>`;
                }
            },

            // 5. Estado
            {
                data: "estado",
                render: function(data, type, row) {
                    const color = row.estado_color || "#6c757d";
                    return `<span class="badge" style="background-color: ${color}; color: #fff; font-size: 0.85rem; padding: 0.4em 0.7em;">
                                ${data || 'Sin estado'}
                            </span>`;
                }
            },

            // 6. Responsable
            {
                data: "responsable",
                render: function(data) {
                    return `<span><i class="far fa-user mr-1 text-muted"></i>${data || 'Sin asignar'}</span>`;
                }
            }
        ],
        responsive: true,
        autoWidth: false,
        order: [[1, "desc"]],
        dom: "<'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        language: {
            decimal: "",
            emptyTable: "No hay radicados disponibles para este usuario",
            info: "Mostrando _START_ a _END_ de _TOTAL_ radicados",
            infoEmpty: "Mostrando 0 a 0 de 0 radicados",
            infoFiltered: "(filtrado de un total de _MAX_ radicados)",
            lengthMenu: "Mostrar _MENU_ registros",
            loadingRecords: "Cargando radicados desde la API...",
            processing: "Procesando petición...",
            search: "Buscar:",
            zeroRecords: "No se encontraron radicados coincidentes",
            paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
                previous: "Anterior"
            }
        }
    });
}

/*=============================================
2. CARGAR CATÁLOGO DE ESTADOS EN EL SELECT
=============================================*/
function cargarCatalogoEstados() {
    fetch(API_ESTADOS_URL)
        .then(res => res.json())
        .then(response => {
            if (response.success && Array.isArray(response.data)) {
                const $select = $("#filtroEstado");
                $select.empty().append('<option value="">Todos los estados</option>');
                response.data.forEach(e => {
                    $select.append(`<option value="${e.nombre}">${e.nombre}</option>`);
                });
            }
        })
        .catch(err => {
            console.error("Error al cargar estados en filtro:", err);
        });
}

/*=============================================
3. CONFIGURAR FILTROS REACTIVOS DE LA VISTA
=============================================*/
function configurarEventosFiltros() {
    // Búsqueda en vivo (radicado, asunto o responsable)
    $("#inputBuscarRadicado").on("keyup search input", function() {
        if (tablaSeguimiento) {
            tablaSeguimiento.search(this.value).draw();
        }
    });

    // Filtro por tipo de documento / radicado (columna 2)
    $("#filtroTipoDocumento").on("change", function() {
        const valor = $(this).val();
        if (tablaSeguimiento) {
            tablaSeguimiento.column(2).search(valor ? valor : "", true, false).draw();
        }
    });

    // Filtro por estado (columna 4)
    $("#filtroEstado").on("change", function() {
        const valor = $(this).val();
        if (tablaSeguimiento) {
            tablaSeguimiento.column(4).search(valor ? valor : "", true, false).draw();
        }
    });

    // Filtro por fecha (columna 3)
    $("#fecha").on("change input", function() {
        const valor = $(this).val();
        if (tablaSeguimiento) {
            tablaSeguimiento.column(3).search(valor ? valor : "", true, false).draw();
        }
    });
}
