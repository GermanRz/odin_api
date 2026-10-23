/**
 * Script de Módulo: Consulta y Seguimiento de Radicados — Proyecto ODIN
 * 
 * Cumple con los estándares del Archivo General de la Nación (AGN - Colombia),
 * la Ley 594 de 2000, Ley 1712 de 2014 y el Semáforo RF-30.
 * 
 * Consumo 100% desacoplado y asíncrono de la API RESTful de ODIN.
 */

const API_SEGUIMIENTO_URL = "api/seguimiento";
const API_CATALOGOS_URL   = "api/seguimiento/catalogos";

let tablaSeguimiento = null;
let radicadoActualDetalle = null;

$(document).ready(function() {
    cargarCatalogosModales();
    inicializarTablaSeguimiento();
    configurarEventosModales();
    configurarDescargaDocumento();
});

/*=============================================
1. CARGAR CATÁLOGOS PARA MODALES
=============================================*/
function cargarCatalogosModales() {
    fetch(API_CATALOGOS_URL)
        .then(res => res.json())
        .then(response => {
            if (response.success && response.data) {
                const data = response.data;

                // Dependencias para modal de traslado
                const $selTrazaDep = $("#trazaNuevaDependencia");
                $selTrazaDep.empty().append('<option value="">Mantener dependencia actual</option>');
                if (Array.isArray(data.dependencias)) {
                    data.dependencias.forEach(d => {
                        $selTrazaDep.append(`<option value="${d.id_dependencia}">${d.codigo ? '[' + d.codigo + '] ' : ''}${d.nombre}</option>`);
                    });
                }

                // Estados para modal de cambio de estado
                const $selTrazaEst = $("#trazaNuevoEstado");
                $selTrazaEst.empty().append('<option value="">Mantener estado actual</option>');
                if (Array.isArray(data.estados)) {
                    data.estados.forEach(e => {
                        $selTrazaEst.append(`<option value="${e.id_estado}">${e.nombre}</option>`);
                    });
                }
            }
        })
        .catch(err => {
            console.error("Error al cargar catálogos:", err);
        });
}

/*=============================================
2. INICIALIZAR DATATABLE DIRECTA CON FILTRO EN VISTA
=============================================*/
function inicializarTablaSeguimiento() {
    if ($.fn.DataTable.isDataTable("#tblConsultaSeguimiento")) {
        $("#tblConsultaSeguimiento").DataTable().destroy();
    }

    tablaSeguimiento = $("#tblConsultaSeguimiento").DataTable({
        responsive: true,
        autoWidth: false,
        processing: true,
        order: [[1, "desc"]], // Ordenar por N° de radicado descendente
        dom: "<'row'<'col-md-6 d-flex align-items-center'B><'col-md-6 text-right'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-md-5'i><'col-md-7'p>>",
        buttons: [
            {
                extend: "excelHtml5",
                text: '<i class="far fa-file-excel mr-1 text-success"></i> Excel',
                className: "btn btn-outline-success btn-sm",
                title: "ODIN_Reporte_Seguimiento_" + new Date().toISOString().slice(0, 10),
                exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7] }
            },
            {
                extend: "pdfHtml5",
                text: '<i class="far fa-file-pdf mr-1 text-danger"></i> PDF',
                className: "btn btn-outline-danger btn-sm",
                orientation: "landscape",
                pageSize: "LEGAL",
                title: "ODIN - Reporte de Seguimiento y Trazabilidad (AGN / Ley 594 de 2000)",
                exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7] },
                customize: function(doc) {
                    doc.styles.tableHeader.fillColor = "#003366";
                    doc.styles.tableHeader.color = "#FFFFFF";
                    doc.defaultStyle.fontSize = 8;
                }
            },
            {
                extend: "csvHtml5",
                text: '<i class="fas fa-file-csv mr-1 text-info"></i> CSV',
                className: "btn btn-outline-info btn-sm",
                title: "ODIN_Seguimiento_CSV",
                exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7] }
            },
            {
                extend: "print",
                text: '<i class="fas fa-print mr-1 text-secondary"></i> Imprimir',
                className: "btn btn-outline-secondary btn-sm",
                title: "Sistema ODIN — Reporte General de Seguimiento Documental",
                exportOptions: { columns: [1, 2, 3, 4, 5, 6, 7] }
            }
        ],
        ajax: {
            url: API_SEGUIMIENTO_URL,
            type: "GET",
            dataSrc: function(json) {
                if (json && json.data) {
                    return json.data;
                }
                return [];
            },
            error: function(xhr) {
                const mensaje = xhr.responseJSON?.message || "No fue posible conectar con el servicio de seguimiento.";
                Swal.fire({
                    icon: "error",
                    title: "Error al cargar radicados",
                    text: mensaje,
                    confirmButtonText: "Entendido"
                });
            }
        },
        columns: [
            // 0. Acciones
            {
                data: null,
                orderable: false,
                searchable: false,
                className: "text-center align-middle",
                render: function(data, type, row) {
                    const id = row.id_radicado;
                    const tieneDoc = (row.total_documentos && parseInt(row.total_documentos, 10) > 0) || row.ruta_documento;
                    const btnClassDoc = tieneDoc ? "btn-outline-primary" : "btn-outline-secondary";
                    const btnTitleDoc = tieneDoc ? "Descargar documento digital adjunto" : "Sin documento digital adjunto";

                    return `
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-info btn-xs btnVerTimeline" data-id="${id}" title="Ver Historial y Trazabilidad (AGN)">
                                <i class="fas fa-stream"></i>
                            </button>
                            <button type="button" class="btn btn-secondary btn-xs btnVerDetalle" data-id="${id}" title="Ver Metadatos del Radicado">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-success btn-xs btnRegistrarMovimiento" data-id="${id}" title="Registrar Avance / Traslado">
                                <i class="fas fa-plus-circle"></i>
                            </button>
                            <button type="button" class="btn ${btnClassDoc} btn-xs btnDescargarDocumento" data-id="${id}" data-tiene="${tieneDoc ? '1' : '0'}" title="${btnTitleDoc}">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    `;
                }
            },

            // 1. Radicado
            {
                data: "numero_radicado",
                render: function(data, type, row) {
                    const num = data || "S/N";
                    const asunto = row.asunto
                        ? `<br><small class="text-muted d-inline-block text-truncate" style="max-width: 260px;" title="${row.asunto}"><b>Asunto:</b> ${row.asunto}</small>`
                        : "";
                    return `<strong class="text-primary font-weight-bold" style="font-size: 0.95rem;">${num}</strong>${asunto}`;
                }
            },

            // 2. Tipo
            {
                data: "tipo_radicado",
                className: "align-middle text-center",
                render: function(data) {
                    const tipo = (data || "ENTRADA").toUpperCase();
                    let badge = "badge-info";
                    let icono = "fa-file-alt";

                    if (tipo === "ENTRADA") {
                        badge = "badge-success";
                        icono = "fa-sign-in-alt";
                    } else if (tipo === "SALIDA") {
                        badge = "badge-warning text-dark";
                        icono = "fa-sign-out-alt";
                    } else if (tipo === "INTERNO") {
                        badge = "badge-secondary";
                        icono = "fa-exchange-alt";
                    }

                    return `<span class="badge ${badge} px-2 py-1 text-uppercase font-weight-bold">
                                <i class="fas ${icono} mr-1"></i>${tipo}
                            </span>`;
                }
            },

            // 3. Remitente / Entidad
            {
                data: "remitente",
                className: "align-middle",
                render: function(data) {
                    const rem = data || "ANÓNIMO";
                    const esAnonimo = rem.indexOf("ANÓNIMO") !== -1;
                    const icono = esAnonimo ? "fa-user-secret text-secondary" : "fa-user text-primary";
                    return `<span><i class="fas ${icono} mr-1"></i>${rem}</span>`;
                }
            },

            // 4. Dependencia & Responsable
            {
                data: "dependencia",
                className: "align-middle",
                render: function(data, type, row) {
                    const dep = data || "Sin asignar";
                    const resp = row.responsable ? `<br><small class="text-muted"><i class="far fa-user mr-1"></i>${row.responsable}</small>` : "";
                    return `<span><i class="fas fa-building text-secondary mr-1"></i><b>${dep}</b></span>${resp}`;
                }
            },

            // 5. Fecha Radicado
            {
                data: "fecha_radicado",
                className: "align-middle",
                render: function(data, type, row) {
                    const fRad = data ? `<span class="d-block"><i class="far fa-calendar-alt text-muted mr-1"></i>${data.substring(0, 16)}</span>` : "<span class='text-muted'>S/F</span>";
                    const fLim = row.fecha_limite 
                        ? `<small class="text-secondary d-block"><i class="far fa-clock text-warning mr-1"></i><b>Límite:</b> ${row.fecha_limite.substring(0, 10)}</small>` 
                        : "";
                    return `${fRad}${fLim}`;
                }
            },

            // 6. Estado Operativo
            {
                data: "estado",
                className: "align-middle text-center",
                render: function(data, type, row) {
                    const nombre = data || "Sin estado";
                    const color = row.estado_color || "#6c757d";
                    return `<span class="badge text-white px-2 py-1 shadow-sm font-weight-bold" style="background-color: ${color}; font-size: 0.85rem;">
                                ${nombre}
                            </span>`;
                }
            },

            // 7. Semáforo Plazos (RF-30)
            {
                data: "semaforo_color",
                className: "align-middle text-center",
                render: function(data, type, row) {
                    const badgeClass = row.semaforo_badge || "badge-secondary";
                    const icono = row.semaforo_icono || "fas fa-circle";
                    const texto = row.semaforo_texto || "Sin plazo";
                    const desc  = row.semaforo_desc || "";

                    return `
                        <span class="badge ${badgeClass} px-2 py-1 font-weight-bold shadow-sm" title="${desc}">
                            <i class="${icono}"></i> ${texto}
                        </span>
                    `;
                }
            }
        ],
        language: {
            decimal: "",
            emptyTable: "No se encontraron radicados registrados",
            info: "Mostrando _START_ a _END_ de _TOTAL_ radicados",
            infoEmpty: "Mostrando 0 a 0 de 0 radicados",
            infoFiltered: "(filtrado de un total de _MAX_ radicados)",
            lengthMenu: "Mostrar _MENU_ registros",
            loadingRecords: "Cargando radicados...",
            processing: "<i class='fas fa-spinner fa-spin mr-1'></i> Procesando...",
            search: "Filtrar en vista:",
            zeroRecords: "No se encontraron registros coincidentes",
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
3. CONFIGURACIÓN DE EVENTOS DE MODALES
=============================================*/
function configurarEventosModales() {

    // A) Abrir Línea de Tiempo (Historial / Trazabilidad)
    $(document).on("click", ".btnVerTimeline", function() {
        const idRadicado = $(this).data("id");
        cargarTimelineRadicado(idRadicado);
    });

    // B) Abrir Detalle y Metadatos
    $(document).on("click", ".btnVerDetalle", function() {
        const idRadicado = $(this).data("id");
        cargarDetalleRadicado(idRadicado);
    });

    // C) Botón dentro del modal de Historial para ver los Metadatos directamente
    $("#btnModalVerMetadatos").on("click", function() {
        if (radicadoActualDetalle && radicadoActualDetalle.id_radicado) {
            $("#modal-timeline").modal("hide");
            cargarDetalleRadicado(radicadoActualDetalle.id_radicado);
        }
    });

    // D) Botón dentro del modal de Metadatos para volver al Historial
    $("#btnModalVolverHistorial").on("click", function() {
        if (radicadoActualDetalle && radicadoActualDetalle.id_radicado) {
            $("#modal-detalle-radicado").modal("hide");
            cargarTimelineRadicado(radicadoActualDetalle.id_radicado);
        }
    });

    // E) Abrir Registrar Movimiento
    $(document).on("click", ".btnRegistrarMovimiento", function() {
        const idRadicado = $(this).data("id");
        abrirModalRegistrarTraza(idRadicado);
    });

    // E2) Abrir Registrar Movimiento desde el propio modal de Timeline
    $("#btnModalAbrirRegistrarTraza").on("click", function() {
        if (radicadoActualDetalle && radicadoActualDetalle.id_radicado) {
            $("#modal-timeline").modal("hide");
            abrirModalRegistrarTraza(radicadoActualDetalle.id_radicado);
        }
    });

    // F) Enviar Formulario de Registro de Movimiento
    $("#frmRegistrarTraza").on("submit", function(e) {
        e.preventDefault();
        const idRadicado = $("#trazaIdRadicado").val();
        if (!idRadicado) return;

        const payload = {
            accion: $("#trazaAccion").val(),
            nuevo_estado: $("#trazaNuevoEstado").val() || null,
            nueva_dependencia: $("#trazaNuevaDependencia").val() || null,
            observacion: $("#trazaObservacion").val().trim()
        };

        if (!payload.observacion) {
            Swal.fire("Campo requerido", "Debe ingresar una observación o justificación para registrar el movimiento.", "warning");
            return;
        }

        $("#btnGuardarTraza").prop("disabled", true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...');

        fetch(`${API_SEGUIMIENTO_URL}/${idRadicado}/movimiento`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(resp => {
            $("#btnGuardarTraza").prop("disabled", false).html('<i class="fas fa-save mr-1"></i> Registrar Movimiento');
            if (resp.success) {
                Swal.fire({
                    icon: "success",
                    title: "Movimiento Registrado",
                    text: resp.message || "El avance ha sido registrado de forma inmutable en la trazabilidad.",
                    timer: 2000,
                    showConfirmButton: false
                });

                $("#modal-registrar-traza").modal("hide");
                $("#frmRegistrarTraza")[0].reset();

                // Recargar tabla
                if (tablaSeguimiento) {
                    tablaSeguimiento.ajax.reload(null, false);
                }
            } else {
                Swal.fire("Error al registrar", resp.message || "No fue posible registrar la traza.", "error");
            }
        })
        .catch(err => {
            $("#btnGuardarTraza").prop("disabled", false).html('<i class="fas fa-save mr-1"></i> Registrar Movimiento');
            console.error("Error al registrar movimiento:", err);
            Swal.fire("Error de conexión", "Ocurrió un error al contactar al servidor de la API.", "error");
        });
    });

    // G) Imprimir Hoja de Ruta desde Modal de Timeline
    $("#btnModalImprimirHojaRuta").on("click", function() {
        if (!radicadoActualDetalle) return;
        imprimirHojaDeRuta(radicadoActualDetalle);
    });
}

/*=============================================
4. DESCARGA SEGURA Y DIRECTA DEL DOCUMENTO
=============================================*/
function configurarDescargaDocumento() {
    $(document).on("click", ".btnDescargarDocumento", function(e) {
        e.preventDefault();
        const idRadicado = $(this).data("id");
        const tieneDoc = $(this).data("tiene") === "1" || $(this).data("tiene") === 1;

        if (!tieneDoc) {
            Swal.fire({
                icon: "info",
                title: "Sin documento adjunto",
                text: "Este radicado fue registrado sin archivos digitales anexos.",
                confirmButtonText: "Entendido"
            });
            return;
        }

        // Descarga directa a través del endpoint seguro del backend
        window.location.href = `${API_SEGUIMIENTO_URL}/${idRadicado}/descargar`;
    });
}

/*=============================================
5. CARGA Y RENDERIZADO DEL TIMELINE (ADMINLTE 3)
=============================================*/
function cargarTimelineRadicado(idRadicado) {
    fetch(`${API_SEGUIMIENTO_URL}/${idRadicado}`)
        .then(res => res.json())
        .then(resp => {
            if (resp.success && resp.data) {
                radicadoActualDetalle = resp.data;
                const r = resp.data;

                $("#modalTimelineRadicado").text(r.numero_radicado || "S/N");
                $("#modalTimelineTipo").text((r.tipo_radicado || "ENTRADA").toUpperCase());
                $("#modalTimelineAsunto").text(r.asunto || "Sin asunto");

                const estColor = r.estado_color || "#6c757d";
                $("#modalTimelineBadgeEstado").html(`
                    <span class="badge text-white px-2 py-1 font-weight-bold" style="background-color: ${estColor}; font-size: 0.85rem;">
                        ${r.estado_nombre || 'Sin estado'}
                    </span>
                `);

                if (r.semaforo) {
                    $("#modalTimelineBadgeSemaforo").html(`
                        <span class="badge ${r.semaforo.badge_class} px-2 py-1 font-weight-bold">
                            <i class="${r.semaforo.icono}"></i> ${r.semaforo.texto}
                        </span>
                    `);
                }

                // Cargar historial
                return fetch(`${API_SEGUIMIENTO_URL}/${idRadicado}/timeline`);
            } else {
                throw new Error("No se pudo obtener el detalle del radicado");
            }
        })
        .then(res => res.json())
        .then(resp => {
            const $timeline = $("#contenedorTimelineAdminLTE");
            $timeline.empty();

            if (resp.success && Array.isArray(resp.data) && resp.data.length > 0) {
                let fechaAnterior = "";

                resp.data.forEach(h => {
                    const fechaCompleta = h.fecha || "";
                    const soloFecha = fechaCompleta.substring(0, 10);
                    const hora = fechaCompleta.length > 10 ? fechaCompleta.substring(11, 19) : "";

                    if (soloFecha !== fechaAnterior) {
                        $timeline.append(`
                            <div class="time-label">
                                <span class="bg-primary px-2 py-1 font-weight-bold text-white">
                                    <i class="far fa-calendar-alt mr-1"></i> ${soloFecha}
                                </span>
                            </div>
                        `);
                        fechaAnterior = soloFecha;
                    }

                    const iconoClase = h.icono_clase || "fas fa-history bg-secondary";
                    const titulo = h.titulo_evento || "Movimiento";
                    const usr = h.usuario_nombre || "Funcionario";
                    const dep = h.dependencia_nombre || "Área";

                    $timeline.append(`
                        <div>
                            <i class="${iconoClase}"></i>
                            <div class="timeline-item shadow-sm">
                                <span class="time"><i class="fas fa-clock mr-1"></i>${hora}</span>
                                <h3 class="timeline-header font-weight-bold text-dark">
                                    ${titulo} <small class="text-muted ml-2">por <b>${usr}</b> (${dep})</small>
                                </h3>
                                <div class="timeline-body text-dark" style="font-size: 0.95rem;">
                                    ${h.descripcion || 'Sin observaciones adicionales.'}
                                </div>
                            </div>
                        </div>
                    `);
                });

                $timeline.append(`
                    <div>
                        <i class="fas fa-flag-checkered bg-gray"></i>
                    </div>
                `);

            } else {
                $timeline.html('<p class="text-muted p-3">No hay movimientos registrados para este radicado.</p>');
            }

            $("#modal-timeline").modal("show");
        })
        .catch(err => {
            console.error("Error al cargar timeline:", err);
            Swal.fire("Error", "No fue posible cargar la línea de tiempo del radicado.", "error");
        });
}

/*=============================================
6. CARGA DE DETALLE Y METADATOS DEL RADICADO
=============================================*/
function cargarDetalleRadicado(idRadicado) {
    fetch(`${API_SEGUIMIENTO_URL}/${idRadicado}`)
        .then(res => res.json())
        .then(resp => {
            if (resp.success && resp.data) {
                const r = resp.data;
                radicadoActualDetalle = r;

                $("#detNumeroRadicado").text(r.numero_radicado || "S/N");
                $("#detTipoRadicado").text((r.tipo_radicado || "ENTRADA").toUpperCase());

                const estColor = r.estado_color || "#6c757d";
                $("#detEstadoBadge").html(`
                    <span class="badge text-white px-2 py-1 font-weight-bold" style="background-color: ${estColor};">
                        ${r.estado_nombre || 'Sin estado'}
                    </span>
                `);

                $("#detRemitente").text(r.remitente || "ANÓNIMO");
                const docRem = r.remitente_num_doc ? `${r.remitente_tipo_doc || 'CC'} ${r.remitente_num_doc}` : "No registra identificación";
                const telRem = r.remitente_telefono ? ` | Tel: ${r.remitente_telefono}` : "";
                const mailRem = r.remitente_correo ? ` | ${r.remitente_correo}` : "";
                $("#detIdentificacion").text(docRem + telRem + mailRem);

                $("#detDependencia").text(r.dependencia_nombre || "Sin asignar");
                $("#detResponsable").text(r.responsable || "Sin funcionario asignado");
                $("#detFechaRadicado").text(r.fecha_radicado || "S/F");
                $("#detFechaLimite").text(r.fecha_limite || (r.fecha_vencimiento || "Sin fecha estipulada"));
                $("#detAsunto").text(r.asunto || "Sin asunto registrado");

                $("#detSerieDocumental").text(r.nombre_serie ? `[${r.codigo_serie || ''}] ${r.nombre_serie}` : "Sin clasificar");
                $("#detSubserieDocumental").text(r.nombre_subserie ? `[${r.codigo_subserie || ''}] ${r.nombre_subserie}` : "Sin clasificar");

                // Documentos
                const $tbDocs = $("#detTablaDocumentos");
                $tbDocs.empty();

                if (Array.isArray(r.documentos) && r.documentos.length > 0) {
                    r.documentos.forEach(doc => {
                        const tamKb = doc.tamano ? Math.round(doc.tamano / 1024) + " KB" : "N/D";
                        const hashCorto = doc.checksum ? doc.checksum.substring(0, 12) + "..." : "N/D";
                        $tbDocs.append(`
                            <tr>
                                <td class="align-middle font-weight-bold">
                                    <i class="far fa-file-pdf text-danger mr-1"></i> ${doc.nombre_archivo || doc.nombre || 'archivo'}
                                </td>
                                <td class="align-middle">${doc.mime_type || doc.tipo || 'application/pdf'}</td>
                                <td class="align-middle">${tamKb}</td>
                                <td class="align-middle"><small class="text-monospace">${hashCorto}</small></td>
                                <td class="align-middle text-center">
                                    <a href="${API_SEGUIMIENTO_URL}/${r.id_radicado}/descargar" class="btn btn-outline-primary btn-xs" title="Descargar documento">
                                        <i class="fas fa-download mr-1"></i> Descargar
                                    </a>
                                </td>
                            </tr>
                        `);
                    });
                } else {
                    $tbDocs.html('<tr><td colspan="5" class="text-center text-muted">No cuenta con archivos digitales adjuntos.</td></tr>');
                }

                $("#modal-detalle-radicado").modal("show");
            } else {
                Swal.fire("Error", resp.message || "No se pudo cargar el detalle del radicado.", "error");
            }
        })
        .catch(err => {
            console.error("Error al cargar detalle:", err);
            Swal.fire("Error", "Error al comunicarse con la API de seguimiento.", "error");
        });
}

/*=============================================
7. MODAL PARA REGISTRAR NUEVO MOVIMIENTO
=============================================*/
function abrirModalRegistrarTraza(idRadicado) {
    $("#frmRegistrarTraza")[0].reset();
    $("#trazaIdRadicado").val(idRadicado);
    $("#modal-registrar-traza").modal("show");
}

/*=============================================
8. EXPORTACIÓN / IMPRESIÓN DE HOJA DE RUTA
=============================================*/
function imprimirHojaDeRuta(rad) {
    const numRadicado = rad.numero_radicado || "S/N";
    const fechaRad = rad.fecha_radicado || new Date().toLocaleString();
    const remitente = rad.remitente || "ANÓNIMO";
    const asunto = rad.asunto || "Sin asunto";
    const dependencia = rad.dependencia || (rad.dependencia_nombre || "Área Responsable");
    const estado = rad.estado || (rad.estado_nombre || "En trámite");

    const ventana = window.open("", "_blank");
    ventana.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Hoja de Ruta - Radicado ${numRadicado}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 30px; color: #333; }
                .header { text-align: center; border-bottom: 2px solid #003366; padding-bottom: 15px; margin-bottom: 20px; }
                .logo-title { font-size: 20px; font-weight: bold; color: #003366; }
                .sub-title { font-size: 13px; color: #666; margin-top: 4px; }
                .box-meta { border: 1px solid #ccc; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
                .box-meta table { width: 100%; border-collapse: collapse; font-size: 12px; }
                .box-meta td { padding: 6px; }
                .box-meta td.label { font-weight: bold; width: 25%; color: #555; }
                .section-title { font-size: 15px; font-weight: bold; color: #003366; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-top: 20px; margin-bottom: 10px; }
                .footer { font-size: 11px; text-align: center; color: #777; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 10px; }
                @media print { body { margin: 10px; } }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="logo-title">SISTEMA DE GESTIÓN DOCUMENTAL ODIN</div>
                <div class="sub-title">Constancia Oficial de Radicación, Seguimiento y Trazabilidad (AGN / Ley 594 de 2000)</div>
            </div>

            <div class="box-meta">
                <table>
                    <tr>
                        <td class="label">N° DE RADICADO:</td>
                        <td><b style="font-size: 15px; color: #003366;">${numRadicado}</b></td>
                        <td class="label">FECHA RADICACIÓN:</td>
                        <td>${fechaRad}</td>
                    </tr>
                    <tr>
                        <td class="label">TIPO DOCUMENTAL:</td>
                        <td>${(rad.tipo_radicado || 'ENTRADA').toUpperCase()}</td>
                        <td class="label">ESTADO ACTUAL:</td>
                        <td><b>${estado}</b></td>
                    </tr>
                    <tr>
                        <td class="label">SOLICITANTE:</td>
                        <td>${remitente}</td>
                        <td class="label">DEPENDENCIA:</td>
                        <td>${dependencia}</td>
                    </tr>
                    <tr>
                        <td class="label">ASUNTO:</td>
                        <td colspan="3">${asunto}</td>
                    </tr>
                </table>
            </div>

            <div class="section-title">CERTIFICACIÓN DE CADENA DE CUSTODIA Y AUDITORÍA</div>
            <p style="font-size: 12px; line-height: 1.5;">
                El presente documento certifica la autenticidad e integridad del radicado dentro de los repositorios inmutables del Sistema ODIN. Cada movimiento, traslado y anotación cuenta con trazabilidad cronológica según las normas archivísticas del Archivo General de la Nación de Colombia.
            </p>

            <div class="footer">
                Documento generado el ${new Date().toLocaleString()} — Sistema ODIN Gestor Documental Institucional.
            </div>

            <script>
                window.onload = function() { window.print(); };
            </script>
        </body>
        </html>
    `);
    ventana.document.close();
}
