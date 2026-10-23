/**
 * Script de Módulo: Gestión de Dependencias — Proyecto ODIN
 * Consumo 100% asíncrono de la API RESTful de ODIN.
 */

const API_DEPENDENCIAS_URL = "api/dependencias";
const API_USUARIOS_URL     = "api/usuarios";

let tablaDependencias = null;
let catalogoUsuariosActivos = [];

$(document).ready(function() {
    inicializarTablaDependencias();
    cargarCatalogoUsuarios();
    cargarEstadisticas();
    configurarFormularioCrear();
    configurarFormularioEditar();
    configurarEventosTabla();
});

/*=============================================
1. INICIALIZAR DATATABLE DE DEPENDENCIAS VÍA AJAX
=============================================*/
function inicializarTablaDependencias() {
    if ($.fn.DataTable.isDataTable("#tblDependencias")) {
        $("#tblDependencias").DataTable().destroy();
    }

    tablaDependencias = $("#tblDependencias").DataTable({
        ajax: {
            url: API_DEPENDENCIAS_URL,
            type: "GET",
            dataSrc: function(json) {
                if (json && json.data) {
                    actualizarContadoresConData(json.data);
                    return json.data;
                }
                return [];
            },
            error: function(xhr) {
                const mensaje = xhr.responseJSON?.message || "No fue posible conectar con el servidor de la API.";
                Swal.fire({
                    icon: "error",
                    title: "Error al cargar dependencias",
                    text: mensaje
                });
            }
        },
        columns: [
            // 1. ID
            {
                data: "id_dependencia",
                className: "text-center align-middle",
                render: function(data) {
                    return `<span class="badge badge-secondary font-weight-bold">${data}</span>`;
                }
            },

            // 2. Código
            {
                data: "codigo",
                className: "text-center align-middle",
                render: function(data) {
                    return `<span class="badge badge-info px-2 py-1">${data || '—'}</span>`;
                }
            },

            // 3. Dependencia
            {
                data: null,
                className: "align-middle",
                render: function(data, type, row) {
                    const nombre = row.nombre || row.dependencia || "Sin nombre";
                    const desc = row.descripcion 
                        ? `<br><small class="text-muted d-inline-block text-truncate" style="max-width: 320px;" title="${row.descripcion}">${row.descripcion}</small>` 
                        : "";
                    return `<strong>${nombre}</strong>${desc}`;
                }
            },

            // 4. Tipo
            {
                data: "tipo",
                className: "text-center align-middle",
                render: function(data) {
                    const tipo = (data || "operativa").toLowerCase();
                    let badgeClass = "badge-secondary";
                    if (tipo === "directiva") badgeClass = "badge-dark";
                    if (tipo === "administrativa") badgeClass = "badge-primary";
                    if (tipo === "operativa") badgeClass = "badge-info";
                    return `<span class="badge ${badgeClass} text-capitalize px-2">${tipo}</span>`;
                }
            },

            // 5. Encargado / Responsable
            {
                data: null,
                className: "align-middle",
                render: function(data, type, row) {
                    if (row.id_usuario_responsable && row.encargado_nombre) {
                        const rol = row.encargado_rol ? `<span class="badge badge-light border mr-1">${row.encargado_rol}</span>` : "";
                        const correo = row.encargado_correo ? `<small class="text-muted">&bull; ${row.encargado_correo}</small>` : "";
                        return `
                            <div class="d-flex align-items-center">
                                <div class="mr-2 text-primary">
                                    <i class="fas fa-user-shield" style="font-size: 1.25rem;"></i>
                                </div>
                                <div>
                                    <div class="font-weight-bold text-dark">${row.encargado_nombre}</div>
                                    <div class="small">${rol}${correo}</div>
                                </div>
                            </div>`;
                    }
                    return `<span class="badge badge-warning text-dark px-2 py-1"><i class="fas fa-exclamation-triangle mr-1"></i>Sin asignar</span>`;
                }
            },

            // 6. Estado
            {
                data: "estado",
                className: "text-center align-middle",
                render: function(data, type, row) {
                    const esActivo = (data === "Activo");
                    const colorClass = esActivo ? "btn-success" : "btn-danger";
                    const nuevoEstado = esActivo ? "Inactivo" : "Activo";
                    return `<button class="btn btn-xs ${colorClass} btnCambiarEstado font-weight-bold" 
                                    data-id="${row.id_dependencia}" 
                                    data-estado="${nuevoEstado}"
                                    title="Clic para cambiar a ${nuevoEstado}">
                                ${data || 'Activo'}
                            </button>`;
                }
            },

            // 7. Acciones
            {
                data: null,
                orderable: false,
                searchable: false,
                className: "text-center align-middle",
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-warning btn-sm btnCargarEditar" 
                                data-id="${row.id_dependencia}"
                                title="Modificar dependencia y encargado">
                            <i class="fas fa-edit"></i>
                        </button>`;
                }
            }
        ],
        responsive: true,
        autoWidth: false,
        language: {
            decimal: "",
            emptyTable: "No hay dependencias registradas",
            info: "Mostrando _START_ a _END_ de _TOTAL_ dependencias",
            infoEmpty: "Mostrando 0 a 0 de 0 dependencias",
            infoFiltered: "(filtrado de un total de _MAX_ dependencias)",
            lengthMenu: "Mostrar _MENU_ dependencias",
            loadingRecords: "Cargando dependencias desde la API...",
            processing: "Procesando petición...",
            search: "Buscar:",
            zeroRecords: "No se encontraron coincidencias",
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
2. CARGAR CATÁLOGO DE USUARIOS PARA ASIGNAR ENCARGADO
=============================================*/
function cargarCatalogoUsuarios() {
    fetch(API_USUARIOS_URL)
        .then(res => res.json())
        .then(res => {
            if (res.success && Array.isArray(res.data)) {
                catalogoUsuariosActivos = res.data;
                poblarSelectsUsuarios(res.data);
            }
        })
        .catch(err => console.error("Error al cargar catálogo de usuarios:", err));
}

function poblarSelectsUsuarios(usuarios) {
    let options = '<option value="">-- Sin encargado asignado --</option>';

    usuarios.forEach(u => {
        const rolTexto = u.rol ? ` (${u.rol})` : "";
        const depActual = u.dependencia ? ` [Actualmente en: ${u.dependencia}]` : "";
        options += `<option value="${u.id_usuario}">${u.nombre}${rolTexto} &mdash; ${u.correo}${depActual}</option>`;
    });

    $(".selectUsuariosEncargado").html(options);
}

/*=============================================
3. MÉTRICAS Y CONTADORES
=============================================*/
function cargarEstadisticas() {
    fetch(API_DEPENDENCIAS_URL)
        .then(res => res.json())
        .then(res => {
            if (res.success && Array.isArray(res.data)) {
                actualizarContadoresConData(res.data);
            }
        })
        .catch(err => console.error("Error al cargar estadísticas:", err));
}

function actualizarContadoresConData(dependencias) {
    const total = dependencias.length;
    let activas = 0;
    let inactivas = 0;
    let conEncargado = 0;

    dependencias.forEach(d => {
        if (d.estado === "Activo") activas++;
        else inactivas++;

        if (d.id_usuario_responsable) conEncargado++;
    });

    $("#totalDependencias").text(total);
    $("#totalDependenciasActivas").text(activas);
    $("#totalDependenciasInactivas").text(inactivas);
    $("#totalConEncargado").text(conEncargado);
}

/*=============================================
4. REGISTRAR NUEVA DEPENDENCIA
=============================================*/
function configurarFormularioCrear() {
    $("#formAgregarDependencia").off("submit").on("submit", function(e) {
        e.preventDefault();

        const payload = {
            nombre:                 $("#nuevoNombre").val().trim(),
            codigo:                 $("#nuevoCodigo").val().trim().toUpperCase(),
            tipo:                   $("#nuevoTipo").val(),
            descripcion:            $("#nuevaDescripcion").val().trim(),
            id_usuario_responsable: $("#nuevoResponsable").val() || null,
            estado:                 $("#nuevoEstado").val()
        };

        if (!payload.nombre || !payload.codigo) {
            Swal.fire({
                icon: "warning",
                title: "Campos requeridos",
                text: "El nombre y el código de la dependencia son obligatorios."
            });
            return;
        }

        const $btn = $("#btnGuardarDependencia");
        const btnHtmlOriginal = $btn.html();
        $btn.prop("disabled", true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...');

        fetch(API_DEPENDENCIAS_URL, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(({ status, body }) => {
            if ((status === 200 || status === 201) && body.success) {
                Swal.fire({
                    icon: "success",
                    title: "¡Dependencia registrada!",
                    text: body.message || "La dependencia y su usuario encargado fueron guardados correctamente.",
                    timer: 2000,
                    showConfirmButton: false
                });

                $("#modal-agregarDependencia").modal("hide");
                $("#formAgregarDependencia")[0].reset();
                tablaDependencias.ajax.reload(null, false);
                cargarEstadisticas();
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error al registrar",
                    text: body.message || "No fue posible registrar la dependencia."
                });
            }
        })
        .catch(err => {
            console.error("Error de conexión:", err);
            Swal.fire({
                icon: "error",
                title: "Error de conexión",
                text: "No se pudo comunicar con el servidor."
            });
        })
        .finally(() => {
            $btn.prop("disabled", false).html(btnHtmlOriginal);
        });
    });
}

/*=============================================
5. MODIFICAR DEPENDENCIA Y ENCARGADO (EDITABLE)
=============================================*/
function configurarFormularioEditar() {
    $("#formEditarDependencia").off("submit").on("submit", function(e) {
        e.preventDefault();

        const id = $("#editarIdDependencia").val();
        const payload = {
            nombre:                 $("#editarNombre").val().trim(),
            codigo:                 $("#editarCodigo").val().trim().toUpperCase(),
            tipo:                   $("#editarTipo").val(),
            descripcion:            $("#editarDescripcion").val().trim(),
            id_usuario_responsable: $("#editarResponsable").val() || null,
            estado:                 $("#editarEstado").val()
        };

        if (!payload.nombre || !payload.codigo) {
            Swal.fire({
                icon: "warning",
                title: "Campos requeridos",
                text: "El nombre y el código de la dependencia son obligatorios."
            });
            return;
        }

        const $btn = $("#btnActualizarDependencia");
        const btnHtmlOriginal = $btn.html();
        $btn.prop("disabled", true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...');

        fetch(`${API_DEPENDENCIAS_URL}/${id}`, {
            method: "PUT",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.success) {
                Swal.fire({
                    icon: "success",
                    title: "¡Dependencia actualizada!",
                    text: body.message || "Los cambios en la dependencia y el encargado se guardaron con éxito.",
                    timer: 2000,
                    showConfirmButton: false
                });

                $("#modal-editarDependencia").modal("hide");
                tablaDependencias.ajax.reload(null, false);
                cargarEstadisticas();
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error al actualizar",
                    text: body.message || "No fue posible actualizar la dependencia."
                });
            }
        })
        .catch(err => {
            console.error("Error de conexión:", err);
            Swal.fire({
                icon: "error",
                title: "Error de conexión",
                text: "No se pudo comunicar con el servidor."
            });
        })
        .finally(() => {
            $btn.prop("disabled", false).html(btnHtmlOriginal);
        });
    });
}

/*=============================================
6. EVENTOS DE TABLA (CARGAR EDICIÓN Y CAMBIO DE ESTADO)
=============================================*/
function configurarEventosTabla() {
    // A. Clic en botón Editar
    $("#tblDependencias").on("click", ".btnCargarEditar", function() {
        const id = $(this).data("id");

        fetch(`${API_DEPENDENCIAS_URL}/${id}`)
            .then(res => res.json())
            .then(res => {
                if (res.success && res.data) {
                    const dep = res.data;
                    $("#editarIdDependencia").val(dep.id_dependencia);
                    $("#editarNombre").val(dep.nombre || dep.dependencia || "");
                    $("#editarCodigo").val(dep.codigo || "");
                    $("#editarTipo").val(dep.tipo || "operativa");
                    $("#editarDescripcion").val(dep.descripcion || "");
                    $("#editarEstado").val(dep.estado || "Activo");

                    // Asegurar que el select de encargados tenga el valor asignado (o vacío si no tiene)
                    $("#editarResponsable").val(dep.id_usuario_responsable ? String(dep.id_usuario_responsable) : "");

                    $("#modal-editarDependencia").modal("show");
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: res.message || "No se pudo cargar la información de la dependencia."
                    });
                }
            })
            .catch(err => {
                console.error("Error al consultar dependencia:", err);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Error al consultar la dependencia."
                });
            });
    });

    // B. Clic en botón Cambiar Estado
    $("#tblDependencias").on("click", ".btnCambiarEstado", function() {
        const id = $(this).data("id");
        const nuevoEstado = $(this).data("estado");

        Swal.fire({
            title: `¿Cambiar a estado ${nuevoEstado}?`,
            text: `La dependencia cambiará a estado ${nuevoEstado}.`,
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: nuevoEstado === "Activo" ? "#28a745" : "#dc3545",
            cancelButtonColor: "#6c757d",
            confirmButtonText: `Sí, cambiar a ${nuevoEstado}`,
            cancelButtonText: "Cancelar"
        }).then(result => {
            if (result.isConfirmed) {
                fetch(`${API_DEPENDENCIAS_URL}/${id}/estado`, {
                    method: "PATCH",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ estado: nuevoEstado })
                })
                .then(res => res.json().then(data => ({ status: res.status, body: data })))
                .then(({ status, body }) => {
                    if (status === 200 && body.success) {
                        const Toast = Swal.mixin({
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                            timer: 2000
                        });
                        Toast.fire({
                            icon: "success",
                            title: `Dependencia actualizada a ${nuevoEstado}`
                        });

                        tablaDependencias.ajax.reload(null, false);
                        cargarEstadisticas();
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: body.message || "No se pudo cambiar el estado."
                        });
                    }
                })
                .catch(err => {
                    console.error("Error al cambiar estado:", err);
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Fallo de conexión al cambiar el estado."
                    });
                });
            }
        });
    });
}
