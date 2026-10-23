/**
 * Lógica Cliente del Módulo de Radicación Documental — Proyecto ODIN
 * 
 * Gestiona la carga asíncrona de catálogos vía API RESTful, carga reactiva de archivos,
 * validación de tipos MIME y tamaño, radicación anónima y notificaciones SweetAlert2.
 */

const API_RADICADOS_URL = "api/radicados";
const API_CATALOGOS_URL = "api/radicados/catalogos";
const API_USUARIOS_URL   = "api/usuarios";
const API_REMITENTES_URL = "api/remitentes";

// Formatos permitidos en cliente
const ALLOWED_MIME_TYPES = [
    "application/pdf",
    "application/vnd.ms-excel",
    "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
    "image/jpeg",
    "image/png"
];
const MAX_FILE_SIZE_BYTES = 15 * 1024 * 1024; // 15MB

let uploadedFiles = [];
let subseriesCatalogo = [];

$(document).ready(function() {
    inicializarCatalogos();
    inicializarEventosArchivos();
    inicializarRadicacionAnonima();
    inicializarAutollenadoRemitente();
    inicializarEventosResumen();
    inicializarEnvioFormulario();

    // Establecer fecha de hoy como valor por defecto en fechaDocumento
    const hoy = new Date().toISOString().split("T")[0];
    const fechaDocInput = document.getElementById("fechaDocumento");
    if (fechaDocInput && !fechaDocInput.value) {
        fechaDocInput.value = hoy;
    }
});

/**
 * Carga de catálogos maestros desde la API REST (trámites, dependencias, estados, usuarios)
 */
function inicializarCatalogos() {
    // 1. Cargar Trámites, Dependencias, Estados, Series y Subseries
    fetch(API_CATALOGOS_URL)
        .then(r => r.json())
        .then(res => {
            if (res.success && res.data) {
                poblarSelectTramites(res.data.tramites || []);
                poblarSelectDependencias(res.data.dependencias || []);
                poblarSelectEstados(res.data.estados || []);
                poblarSelectSeries(res.data.series || [], res.data.subseries || []);
                poblarSelectTiposDocumentales();
            }
        })
        .catch(err => {
            console.error("Error al cargar catálogos maestros:", err);
        });

    // 2. Cargar Usuarios para Asignación Inicial
    fetch(API_USUARIOS_URL)
        .then(r => r.json())
        .then(res => {
            if (res.success && Array.isArray(res.data)) {
                const $selectResp = $("#responsable");
                $selectResp.empty().append('<option value="">Seleccione un responsable...</option>');
                res.data.forEach(u => {
                    $selectResp.append(`<option value="${u.id_usuario}">${u.nombre} (${u.rol || 'Funcionario'})</option>`);
                });
            }
        })
        .catch(err => {
            console.error("Error al cargar usuarios:", err);
        });
}

function poblarSelectTramites(tramites) {
    const $select = $("#tramite");
    $select.empty().append('<option value="">Seleccione un trámite...</option>');
    tramites.forEach(t => {
        $select.append(`<option value="${t.id_tramite}" data-dias="${t.dias_respuesta || 15}" data-prio="${t.prioridad_default || 'MEDIA'}">${t.nombre}</option>`);
    });

    $select.on("change", function() {
        const selectedOpt = $(this).find("option:selected");
        const prioridadDef = selectedOpt.data("prio");
        if (prioridadDef) {
            $("#prioridad").val(prioridadDef.toLowerCase()).trigger("change");
        }
    });
}

function poblarSelectDependencias(dependencias) {
    const $dest = $("#dependenciaDestino");
    const $resp = $("#dependenciaResponsable");

    $dest.empty().append('<option value="">Seleccione una dependencia...</option>');
    $resp.empty().append('<option value="">Seleccione una dependencia...</option>');

    dependencias.forEach(d => {
        const optionHtml = `<option value="${d.id_dependencia}">[${d.codigo || 'DEP'}] ${d.nombre}</option>`;
        $dest.append(optionHtml);
        $resp.append(optionHtml);
    });
}

function poblarSelectEstados(estados) {
    // Fijar el estado inicial "Pendiente" por defecto sin permitir modificación
    const estadoPendiente = estados.find(e => e.nombre && e.nombre.toLowerCase().includes("pendiente"));
    if (estadoPendiente) {
        $("#estadoInicial").val(estadoPendiente.id_estado);
        $("#estadoInicialVisible").val(estadoPendiente.nombre);
    } else {
        $("#estadoInicial").val("1");
        $("#estadoInicialVisible").val("Pendiente");
    }
}

function poblarSelectSeries(series, subseries) {
    subseriesCatalogo = subseries;
    const $selectSerie = $("#serieDocumental");
    const $selectSubserie = $("#subserieDocumental");

    $selectSerie.empty().append('<option value="">Seleccione una serie...</option>');
    series.forEach(s => {
        $selectSerie.append(`<option value="${s.id_serie}" data-codigo="${s.codigo_serie}">[${s.codigo_serie}] ${s.nombre_serie}</option>`);
    });

    $selectSubserie.empty().append('<option value="">Seleccione primero una serie documental...</option>');

    // Filtrado dinámico y cascada de subseries al seleccionar una serie
    $selectSerie.on("change", function() {
        const idSerieSeleccionada = $(this).val();
        $selectSubserie.empty();

        if (!idSerieSeleccionada) {
            $selectSubserie.append('<option value="">Seleccione primero una serie documental...</option>');
            actualizarResumen();
            return;
        }

        const subseriesFiltradas = subseriesCatalogo.filter(sub => String(sub.id_serie) === String(idSerieSeleccionada));

        if (subseriesFiltradas.length === 0) {
            $selectSubserie.append('<option value="">No hay subseries registradas para esta serie</option>');
        } else {
            $selectSubserie.append('<option value="">Seleccione una subserie...</option>');
            subseriesFiltradas.forEach(sub => {
                $selectSubserie.append(`<option value="${sub.id_subserie}" data-codigo="${sub.codigo_subserie}">[${sub.codigo_subserie}] ${sub.nombre_subserie}</option>`);
            });
        }
        actualizarResumen();
    });

    $selectSubserie.on("change", actualizarResumen);
}

function poblarSelectTiposDocumentales() {
    const $select = $("#tipoDocumental");
    $select.empty().append('<option value="">Seleccione un tipo...</option>');
    const tipos = [
        "Oficio",
        "Memorando",
        "Solicitud / Petición",
        "Acta",
        "Resolución",
        "Informe",
        "Certificación",
        "Circular",
        "Contrato / Convenio"
    ];
    tipos.forEach(t => {
        $select.append(`<option value="${t}">${t}</option>`);
    });
}

/**
 * Gestión interactiva de arrastrar y soltar (Drag & Drop) y selección de archivos
 */
function inicializarEventosArchivos() {
    const dropZone = document.getElementById("dropZone");
    const fileInput = document.getElementById("fileInput");

    if (!dropZone || !fileInput) return;

    dropZone.addEventListener("click", () => fileInput.click());

    dropZone.addEventListener("dragover", (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropZone.style.backgroundColor = "#e8f5e9";
        dropZone.style.borderColor = "#28a745";
    });

    dropZone.addEventListener("dragleave", (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropZone.style.backgroundColor = "transparent";
        dropZone.style.borderColor = "#ffc107";
    });

    dropZone.addEventListener("drop", (e) => {
        e.preventDefault();
        e.stopPropagation();
        dropZone.style.backgroundColor = "transparent";
        dropZone.style.borderColor = "#ffc107";
        agregarArchivos(e.dataTransfer.files);
    });

    fileInput.addEventListener("change", (e) => {
        agregarArchivos(e.target.files);
        fileInput.value = ""; // Permitir volver a seleccionar el mismo archivo si fue eliminado
    });
}

function agregarArchivos(archivosFileList) {
    const rechazados = [];

    for (let i = 0; i < archivosFileList.length; i++) {
        const file = archivosFileList[i];

        // Validar tipo MIME
        if (!ALLOWED_MIME_TYPES.includes(file.type)) {
            rechazados.push(`${file.name}: Formato no permitido. Solo PDF, Excel (XLSX, XLS), JPG y PNG.`);
            continue;
        }

        // Validar peso máximo
        if (file.size > MAX_FILE_SIZE_BYTES) {
            rechazados.push(`${file.name}: Supera el límite de 15MB.`);
            continue;
        }

        // Evitar duplicados por nombre y tamaño
        const yaExiste = uploadedFiles.some(f => f.name === file.name && f.size === file.size);
        if (!yaExiste) {
            uploadedFiles.push(file);
        }
    }

    if (rechazados.length > 0) {
        Swal.fire({
            icon: "warning",
            title: "Archivos no admitidos",
            html: '<ul class="text-left">' + rechazados.map(r => `<li>${r}</li>`).join('') + '</ul>'
        });
    }

    renderizarListaArchivos();
    actualizarResumen();
}

function eliminarArchivo(index) {
    uploadedFiles.splice(index, 1);
    renderizarListaArchivos();
    actualizarResumen();
}

function renderizarListaArchivos() {
    const $container = $("#fileList");
    const $totalFiles = $("#totalFiles");
    $totalFiles.val(uploadedFiles.length);

    if (uploadedFiles.length === 0) {
        $container.html('<p class="text-muted text-center"><i class="fas fa-inbox mr-2"></i>Ningún archivo adjuntado</p>');
        return;
    }

    let html = `
    <div class="alert alert-success py-2 mb-2">
        <i class="fas fa-check-circle mr-1"></i><strong>${uploadedFiles.length}</strong> archivo(s) listo(s) para radicar
    </div>`;

    uploadedFiles.forEach((file, idx) => {
        const tamanoKB = (file.size / 1024).toFixed(1);
        const iconClass = obtenerIconoPorMime(file.type);

        html += `
        <div class="alert alert-light border border-left-success py-2 px-3 mb-2 d-flex justify-content-between align-items-center">
            <div class="text-truncate mr-2">
                <i class="fas ${iconClass} mr-2 text-success"></i>
                <strong>${file.name}</strong>
                <span class="text-muted small ml-2">(${tamanoKB} KB)</span>
            </div>
            <button type="button" class="btn btn-outline-danger btn-xs" onclick="eliminarArchivo(${idx})" title="Quitar archivo">
                <i class="fas fa-trash-alt"></i>
            </button>
        </div>`;
    });

    $container.html(html);
}

function obtenerIconoPorMime(mime) {
    if (mime === "application/pdf") return "fa-file-pdf text-danger";
    if (mime.includes("excel") || mime.includes("spreadsheet")) return "fa-file-excel text-success";
    if (mime.startsWith("image/")) return "fa-file-image text-info";
    return "fa-file-alt text-secondary";
}

/**
 * Control del Switch de Radicación Anónima (Ley de Transparencia RF-09)
 */

let debounceTimerRemitente = null;
let ultimoDocumentoBuscado = "";

/**
 * Autollenado reactivo de datos del solicitante consultando la tabla remitentes_externos
 */
function inicializarAutollenadoRemitente() {
    const $numDoc = $("#numeroDocumento");
    if (!$numDoc.length) return;

    // 1. Detección en tiempo real con debounce al escribir o pegar
    $numDoc.on("input", function() {
        const valor = $(this).val().trim();
        clearTimeout(debounceTimerRemitente);

        if (valor.length < 3) {
            $("#remitenteFeedback").empty();
            ultimoDocumentoBuscado = "";
            return;
        }

        debounceTimerRemitente = setTimeout(() => {
            consultarYAutollenarRemitente(valor);
        }, 400);
    });

    // 2. Consulta inmediata al perder el foco (blur) o cambio (change)
    $numDoc.on("blur change", function() {
        const valor = $(this).val().trim();
        if (valor.length >= 3 && valor !== ultimoDocumentoBuscado) {
            clearTimeout(debounceTimerRemitente);
            consultarYAutollenarRemitente(valor);
        }
    });

    // 3. Revalidar si cambia el tipo de documento con un número ya ingresado
    $("#tipoDocumento").on("change", function() {
        const valor = $numDoc.val().trim();
        if (valor.length >= 3) {
            ultimoDocumentoBuscado = "";
            consultarYAutollenarRemitente(valor);
        }
    });
}

/**
 * Consulta a la API RESTful los datos del remitente externo para autollenar el formulario
 */
function consultarYAutollenarRemitente(numeroDocumento) {
    if ($("#radicacionAnonima").is(":checked")) return;
    if (!numeroDocumento || numeroDocumento === ultimoDocumentoBuscado) return;

    const tipoDoc = $("#tipoDocumento").val();
    const queryParams = tipoDoc ? `?tipo=${encodeURIComponent(tipoDoc)}` : "";
    const url = `${API_REMITENTES_URL}/${encodeURIComponent(numeroDocumento)}${queryParams}`;

    fetch(url)
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            ultimoDocumentoBuscado = numeroDocumento;

            if (status === 200 && body.success && body.data) {
                const rem = body.data;
                const nombreCompleto = rem.nombre_completo || [rem.nombre, rem.apellido].filter(Boolean).join(" ");

                // Sincronizar select de tipo de documento
                if (rem.tipo_identificacion) {
                    const tipoNormalizado = rem.tipo_identificacion.toLowerCase();
                    if ($(`#tipoDocumento option[value="${tipoNormalizado}"]`).length) {
                        $("#tipoDocumento").val(tipoNormalizado);
                    }
                }

                // Autollenar campos del solicitante
                if (nombreCompleto) {
                    $("#nombreSolicitante").val(nombreCompleto);
                }
                if (rem.correo) {
                    $("#correoSolicitante").val(rem.correo);
                }
                if (rem.telefono) {
                    $("#telefonoSolicitante").val(rem.telefono);
                }
                if (rem.direccion) {
                    $("#ciudadSolicitante").val(rem.direccion);
                }

                // Mensaje informativo sutil
                $("#remitenteFeedback").html(
                    '<span class="text-success small font-weight-bold"><i class="fas fa-check-circle mr-1"></i>Remitente registrado: Datos cargados automáticamente.</span>'
                );

                // Destello visual en campos autocompletados
                const $camposLlenados = $("#nombreSolicitante, #correoSolicitante, #telefonoSolicitante, #ciudadSolicitante");
                $camposLlenados.addClass("border-success");
                setTimeout(() => $camposLlenados.removeClass("border-success"), 2000);

                actualizarResumen();
            } else {
                $("#remitenteFeedback").html(
                    '<span class="text-muted small"><i class="fas fa-user-plus mr-1"></i>Remitente nuevo: Complete los datos para su registro.</span>'
                );
            }
        })
        .catch(err => {
            console.warn("Aviso al consultar remitente externo:", err);
            $("#remitenteFeedback").empty();
        });
}
function inicializarRadicacionAnonima() {
    $("#radicacionAnonima").on("change", function() {
        const esAnonimo = $(this).is(":checked");
        const campos = [
            "#tipoDocumento",
            "#numeroDocumento",
            "#nombreSolicitante",
            "#correoSolicitante",
            "#telefonoSolicitante",
            "#ciudadSolicitante"
        ];

        if (esAnonimo) {
            campos.forEach(c => {
                const $c = $(c);
                $c.val("");
                $c.prop("disabled", true);
                $c.removeAttr("required");
                $c.removeClass("is-invalid is-valid");
            });
            ultimoDocumentoBuscado = "";
            $("#remitenteFeedback").empty();
            $("#seccionDatosSolicitante").addClass("bg-light text-muted");
            $(".resumen-solicitante").html('<span class="badge badge-warning"><i class="fas fa-user-secret mr-1"></i>ANÓNIMO (Ley de Transparencia)</span>');
        } else {
            campos.forEach(c => {
                const $c = $(c);
                $c.prop("disabled", false);
            });
            $("#tipoDocumento, #numeroDocumento, #nombreSolicitante").attr("required", "required");
            $("#seccionDatosSolicitante").removeClass("bg-light text-muted");
            actualizarResumen();
        }
    });
}

/**
 * Actualización reactiva del panel lateral de Resumen
 */
function inicializarEventosResumen() {
    const campos = [
        "#numeroDocumento",
        "#nombreSolicitante",
        "#dependenciaDestino",
        "#serieDocumental",
        "#subserieDocumental",
        "#asuntoDocumento",
        "#prioridad",
        "#fechaLimite"
    ];

    $(campos.join(",")).on("change keyup", actualizarResumen);
}

function actualizarResumen() {
    const esAnonimo = $("#radicacionAnonima").is(":checked");
    if (esAnonimo) {
        $(".resumen-solicitante").html('<span class="badge badge-warning"><i class="fas fa-user-secret mr-1"></i>ANÓNIMO (Ley de Transparencia)</span>');
    } else {
        const num = $("#numeroDocumento").val().trim();
        const nom = $("#nombreSolicitante").val().trim();
        const solTexto = (num || nom) ? `${num} - ${nom}` : "—";
        $(".resumen-solicitante").text(solTexto);
    }

    const depText = $("#dependenciaDestino option:selected").text();
    $(".resumen-dependencia").text(depText && !depText.includes("Seleccione") ? depText : "—");

    const serieText = $("#serieDocumental option:selected").text();
    $(".resumen-serie").text(serieText && !serieText.includes("Seleccione") ? serieText : "—");

    const subserieText = $("#subserieDocumental option:selected").text();
    $(".resumen-subserie").text(subserieText && !subserieText.includes("Seleccione") && !subserieText.includes("No hay") ? subserieText : "—");

    const asunto = $("#asuntoDocumento").val().trim();
    $(".resumen-asunto").text(asunto || "—");

    $(".resumen-adjuntos").text(uploadedFiles.length);

    const prio = $("#prioridad option:selected").text();
    $(".resumen-prioridad").text(prio && !prio.includes("Seleccione") ? prio : "—");

    const fechaLim = $("#fechaLimite").val();
    $(".resumen-fechaLimite").text(fechaLim || "—");
}

/**
 * Limpieza integral del formulario
 */
function limpiarFormulario() {
    $("#formRadicacion")[0].reset();
    $("#formRadicacion").removeClass("was-validated");
    $("#tipoRadicacion").val("entrada");
    $("#tipoRadicacionVisible").val("Entrada");
    $("#estadoInicial").val("1");
    $("#estadoInicialVisible").val("Pendiente");
    ultimoDocumentoBuscado = "";
    $("#remitenteFeedback").empty();
    uploadedFiles = [];
    renderizarListaArchivos();
    $("#radicacionAnonima").prop("checked", false).trigger("change");
    $("#subserieDocumental").empty().append('<option value="">Seleccione primero una serie documental...</option>');
    actualizarResumen();
}

/**
 * Intercepción y envío del formulario a la API RESTful
 */
function inicializarEnvioFormulario() {
    const form = document.getElementById("formRadicacion");
    if (!form) return;

    form.addEventListener("submit", function(e) {
        e.preventDefault();

        // 1. Validar presencia de adjuntos obligatorios
        if (uploadedFiles.length === 0) {
            Swal.fire({
                icon: "warning",
                title: "Documento adjunto requerido",
                text: "Debe adjuntar al menos un archivo digital (PDF, Excel o imagen) para completar el proceso de radicación."
            });
            return;
        }

        // 2. Validación de HTML5
        if (!form.checkValidity()) {
            e.stopPropagation();
            form.classList.add("was-validated");
            Swal.fire({
                icon: "error",
                title: "Campos incompletos",
                text: "Por favor complete todos los campos obligatorios marcados con asterisco (*)."
            });
            return;
        }

        // 3. Preparar FormData para envío multipart
        const formData = new FormData(form);

        // Adjuntar los archivos del array uploadedFiles
        formData.delete("adjuntos[]");
        uploadedFiles.forEach(file => {
            formData.append("adjuntos[]", file);
        });

        // 4. Bloquear botón de envío con spinner
        const $btn = $("#btnRadicar");
        const btnHtmlOriginal = $btn.html();
        $btn.prop("disabled", true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Radicando documento...');

        // 5. Envío Asíncrono a la API RESTful
        fetch(API_RADICADOS_URL, {
            method: "POST",
            body: formData
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            if (status === 201 && body.success) {
                const rad = body.data;
                const totalDocs = rad.documentos ? rad.documentos.length : 1;

                Swal.fire({
                    icon: "success",
                    title: "¡Radicación Exitosa!",
                    html: `
                        <div class="text-center my-3">
                            <span class="text-muted">Número oficial asignado:</span>
                            <h2 class="text-success font-weight-bold mt-1 mb-2">${rad.numero_radicado}</h2>
                            <p class="mb-1"><strong>Fecha de radicado:</strong> ${rad.fecha_radicado}</p>
                            <p class="mb-1"><strong>Tipo:</strong> <span class="badge badge-info">${rad.tipo_radicado}</span></p>
                            <p class="mb-0"><strong>Documentos almacenados:</strong> ${totalDocs}</p>
                        </div>
                    `,
                    confirmButtonText: '<i class="fas fa-check mr-1"></i> Aceptar',
                    confirmButtonColor: "#28a745"
                });

                limpiarFormulario();
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error al radicar",
                    text: body.message || "No fue posible procesar la radicación del documento."
                });
            }
        })
        .catch(err => {
            console.error("Fallo de conexión:", err);
            Swal.fire({
                icon: "error",
                title: "Error de conexión",
                text: "No fue posible establecer comunicación con el servidor. Verifique su red e intente nuevamente."
            });
        })
        .finally(() => {
            $btn.prop("disabled", false).html(btnHtmlOriginal);
        });
    });
}
