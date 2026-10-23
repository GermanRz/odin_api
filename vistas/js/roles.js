/**
 * Script de Módulo: Gestión de Roles y Permisos (RBAC)
 * Consumo 100% asíncrono de la API RESTful de ODIN (/api/roles).
 */

const API_ROLES_URL = "api/roles";
let tablaRoles = null;

$(document).ready(function() {
    if ($("#tblRoles").length > 0) {
        inicializarTablaRoles();
        cargarEstadisticasRoles();
    }

    if ($("#frmGuardarRol").length > 0) {
        inicializarFormularioRol();
    }
});

/*=============================================
1. INICIALIZAR DATATABLE DE ROLES CONSUMIENDO API REST
=============================================*/
function inicializarTablaRoles() {
    if ($.fn.DataTable.isDataTable("#tblRoles")) {
        $("#tblRoles").DataTable().destroy();
    }

    tablaRoles = $("#tblRoles").DataTable({
        ajax: {
            url: API_ROLES_URL,
            type: "GET",
            dataSrc: "data",
            error: function(xhr) {
                const mensaje = xhr.responseJSON?.message || "No fue posible cargar el listado de roles desde la API.";
                Swal.fire({
                    icon: "error",
                    title: "Error al cargar roles",
                    text: mensaje
                });
            }
        },
        columns: [
            {
                data: "id_rol",
                render: function(data) {
                    return `<span class="badge badge-secondary">${data}</span>`;
                }
            },
            {
                data: "codigo",
                render: function(data, type, row) {
                    const codigo = data || (row.rol ? String(row.rol).toUpperCase() : "ROL");
                    return `<span class="badge badge-info">${codigo}</span>`;
                }
            },
            {
                data: "nombre",
                render: function(data) {
                    return `<strong>${ $("<div>").text(data || "").html() }</strong>`;
                }
            },
            {
                data: "descripcion",
                render: function(data) {
                    return $("<div>").text(data || "Sin descripción").html();
                }
            },
            {
                data: "total_permisos",
                className: "text-center",
                render: function(data, type, row) {
                    const total = parseInt(data, 10) || 0;
                    return `<button type="button"
                                    class="btn btn-outline-primary btn-xs btnVerPermisosRol"
                                    data-id-rol="${row.id_rol}"
                                    title="Ver permisos asignados">
                                <i class="fas fa-key mr-1"></i> ${total} permiso(s)
                            </button>`;
                }
            },
            {
                data: "total_usuarios",
                className: "text-center",
                render: function(data) {
                    const total = parseInt(data, 10) || 0;
                    return `<span class="badge badge-dark">${total}</span>`;
                }
            },
            {
                data: "estado",
                className: "text-center",
                render: function(data) {
                    const esActivo = data === "Activo";
                    const badgeClass = esActivo ? "badge-success" : "badge-danger";
                    return `<span class="badge ${badgeClass}">${data || "Activo"}</span>`;
                }
            },
            {
                data: null,
                className: "text-center",
                orderable: false,
                render: function(data, type, row) {
                    const idRol = parseInt(row.id_rol, 10);
                    const esAdminPrincipal = idRol === 1;
                    const esActivo = row.estado === "Activo";
                    const nombreSeguro = $("<div>").text(row.nombre || "").html();

                    const btnEstado = esActivo
                        ? `<button type="button"
                                   class="btn btn-warning btn-sm btnEstadoRol mr-1"
                                   data-id-rol="${idRol}"
                                   data-estado-rol="Inactivo"
                                   title="Desactivar rol"
                                   ${esAdminPrincipal ? "disabled" : ""}>
                               <i class="fas fa-power-off"></i>
                           </button>`
                        : `<button type="button"
                                   class="btn btn-success btn-sm btnEstadoRol mr-1"
                                   data-id-rol="${idRol}"
                                   data-estado-rol="Activo"
                                   title="Activar rol">
                               <i class="fas fa-check"></i>
                           </button>`;

                    return `
                        <a href="roles_permisosForm?id=${idRol}"
                           class="btn btn-info btn-sm mr-1"
                           title="Editar rol y permisos">
                            <i class="fas fa-edit"></i>
                        </a>
                        ${btnEstado}
                        <button type="button"
                                class="btn btn-danger btn-sm btnEliminarRol"
                                data-id-rol="${idRol}"
                                data-nombre-rol="${nombreSeguro}"
                                title="Eliminar rol"
                                ${esAdminPrincipal ? "disabled" : ""}>
                            <i class="fas fa-trash"></i>
                        </button>`;
                }
            }
        ],
        responsive: true,
        autoWidth: false,
        dom: "Bfrtip",
        buttons: [
            { extend: "excelHtml5", text: "Excel", className: "btn btn-secondary btn-sm" },
            { extend: "pdfHtml5", text: "PDF", className: "btn btn-secondary btn-sm" }
        ],
        language: {
            decimal: "",
            emptyTable: "No hay roles registrados",
            info: "Mostrando _START_ a _END_ de _TOTAL_ roles",
            infoEmpty: "Mostrando 0 a 0 de 0 roles",
            infoFiltered: "(filtrado de un total de _MAX_ roles)",
            lengthMenu: "Mostrar _MENU_ registros",
            loadingRecords: "Cargando roles desde la API...",
            processing: "Procesando...",
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
2. CARGAR TARJETAS KPI DE ROLES Y PERMISOS
=============================================*/
function cargarEstadisticasRoles() {
    fetch(`${API_ROLES_URL}/stats`)
        .then(res => res.json())
        .then(res => {
            if (res.success && res.data) {
                $("#kpiTotalRoles").text(res.data.total ?? 0);
                $("#kpiRolesActivos").text(res.data.activos ?? 0);
                $("#kpiTotalPermisos").text(res.data.totalPermisos ?? 0);
            }
        })
        .catch(err => console.error("Error al cargar estadísticas de roles:", err));
}

/*=============================================
3. VISUALIZAR PERMISOS DE UN ROL EN MODAL
=============================================*/
$(document).on("click", ".btnVerPermisosRol", function() {
    const idRol = $(this).data("id-rol");

    fetch(`${API_ROLES_URL}/${idRol}`)
        .then(res => res.json())
        .then(res => {
            if (res.success && res.data) {
                const rol = res.data;
                const codigo = rol.codigo || (rol.rol ? String(rol.rol).toUpperCase() : "");
                $("#modalPermisosNombreRol").text(`${rol.nombre} (${codigo})`);
                $("#modalPermisosDescRol").text(rol.descripcion || "Sin descripción");
                $("#btnIrEditarPermisosRol").attr("href", `roles_permisosForm?id=${rol.id_rol}`);

                const contenedor = $("#listaPermisosModal");
                contenedor.empty();

                if (Array.isArray(rol.permisos) && rol.permisos.length > 0) {
                    rol.permisos.forEach(p => {
                        const etiquetaModulo = p.modulo_etiqueta || p.modulo || "General";
                        const nombrePermiso = $("<div>").text(p.nombre || "").html();
                        const descPermiso = $("<div>").text(p.descripcion || p.accion || "").html();

                        contenedor.append(`
                            <div class="col-md-6 mb-2">
                                <div class="p-2 border rounded bg-light h-100">
                                    <span class="badge badge-primary mr-1">${etiquetaModulo}</span>
                                    <strong>${nombrePermiso}</strong>
                                    <div class="small text-muted mt-1">${descPermiso}</div>
                                </div>
                            </div>
                        `);
                    });
                } else {
                    contenedor.append(`
                        <div class="col-12 text-muted text-center py-3">
                            Este rol no tiene permisos asignados actualmente.
                        </div>
                    `);
                }

                $("#modal-verPermisosRol").modal("show");
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: res.message || "No fue posible obtener los permisos del rol."
                });
            }
        })
        .catch(() => {
            Swal.fire({
                icon: "error",
                title: "Error de conexión",
                text: "No se pudo consultar la información del rol en la API."
            });
        });
});

/*=============================================
4. ACTIVAR / DESACTIVAR ROL (PATCH ASÍNCRONO)
=============================================*/
$(document).on("click", ".btnEstadoRol", function() {
    const idRol = $(this).data("id-rol");
    const nuevoEstado = $(this).data("estado-rol");

    fetch(`${API_ROLES_URL}/${idRol}/estado`, {
        method: "PATCH",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ estado: nuevoEstado })
    })
    .then(res => res.json().then(body => ({ status: res.status, body })))
    .then(({ status, body }) => {
        if (status === 200 && body.success) {
            Swal.fire({
                icon: "success",
                title: "Estado actualizado",
                text: body.message,
                timer: 1500,
                showConfirmButton: false
            });
            if (tablaRoles) {
                tablaRoles.ajax.reload(null, false);
            }
            cargarEstadisticasRoles();
        } else {
            Swal.fire({
                icon: "error",
                title: "No permitido",
                text: body.message || "No se pudo cambiar el estado del rol."
            });
        }
    })
    .catch(() => {
        Swal.fire({
            icon: "error",
            title: "Error de comunicación",
            text: "No se pudo conectar con el servidor de la API."
        });
    });
});

/*=============================================
5. ELIMINAR ROL (DELETE ASÍNCRONO CON CONFIRMACIÓN)
=============================================*/
$(document).on("click", ".btnEliminarRol", function() {
    const idRol = $(this).data("id-rol");
    const nombreRol = $(this).data("nombre-rol") || "este rol";

    Swal.fire({
        title: `¿Eliminar el rol "${nombreRol}"?`,
        text: "Esta acción eliminará el rol y sus asignaciones de permisos en la matriz RBAC.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`${API_ROLES_URL}/${idRol}`, {
                method: "DELETE"
            })
            .then(res => res.json().then(body => ({ status: res.status, body })))
            .then(({ status, body }) => {
                if (status === 200 && body.success) {
                    Swal.fire({
                        icon: "success",
                        title: "¡Eliminado!",
                        text: body.message,
                        timer: 1600,
                        showConfirmButton: false
                    });
                    if (tablaRoles) {
                        tablaRoles.ajax.reload(null, false);
                    }
                    cargarEstadisticasRoles();
                } else {
                    Swal.fire({
                        icon: "warning",
                        title: "No se puede eliminar",
                        text: body.message || "El rol no pudo ser eliminado."
                    });
                }
            })
            .catch(() => {
                Swal.fire({
                    icon: "error",
                    title: "Error de conexión",
                    text: "No se pudo comunicar con el servidor."
                });
            });
        }
    });
});

/*=============================================
6. FORMULARIO DE CREACIÓN / EDICIÓN Y MATRIZ RBAC
=============================================*/
function inicializarFormularioRol() {
    const params = new URLSearchParams(window.location.search);
    const idRolEditar = parseInt(params.get("id") || "0", 10);

    // 1. Cargar catálogo completo de permisos RBAC
    fetch(`${API_ROLES_URL}/permisos`)
        .then(res => res.json())
        .then(res => {
            if (!res.success || !Array.isArray(res.data)) {
                $("#contenedorMatrizPermisos").html('<div class="col-12 text-danger">No se pudo cargar el catálogo de permisos.</div>');
                return;
            }

            renderizarMatrizPermisos(res.data);

            // 2. Si estamos en modo edición, cargar datos y marcar permisos del rol
            if (idRolEditar > 0) {
                $("#idRolFormulario").val(idRolEditar);
                $("#tituloFormularioRol").html('<i class="fas fa-user-shield mr-2"></i> Editar Rol y Permisos');
                cargarDatosRolParaEditar(idRolEditar);
            }
        })
        .catch(() => {
            $("#contenedorMatrizPermisos").html('<div class="col-12 text-danger">Error de comunicación al cargar permisos.</div>');
        });

    // 3. Interceptar submit para crear (POST) o actualizar (PUT)
    $("#frmGuardarRol").on("submit", function(e) {
        e.preventDefault();

        const idRol = parseInt($("#idRolFormulario").val() || "0", 10);
        const permisosSeleccionados = [];
        $(".chkPermisoRol:checked").each(function() {
            permisosSeleccionados.push(parseInt($(this).val(), 10));
        });

        const payload = {
            nombre:   $("#nombreRol").val().trim(),
            rol:      $("#codigoRol").val(),
            estado:   $("#estadoRol").val(),
            permisos: permisosSeleccionados
        };

        const esEdicion = idRol > 0;
        const url = esEdicion ? `${API_ROLES_URL}/${idRol}` : API_ROLES_URL;
        const metodo = esEdicion ? "PUT" : "POST";

        fetch(url, {
            method: metodo,
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json().then(body => ({ status: res.status, body })))
        .then(({ status, body }) => {
            if ((status === 200 || status === 201) && body.success) {
                Swal.fire({
                    icon: "success",
                    title: esEdicion ? "¡Rol actualizado!" : "¡Rol creado!",
                    text: body.message,
                    confirmButtonText: "Aceptar"
                }).then(() => {
                    window.location = "roles_permisos";
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error al guardar",
                    text: body.message || "Verifique los datos ingresados."
                });
            }
        })
        .catch(() => {
            Swal.fire({
                icon: "error",
                title: "Error de conexión",
                text: "No se pudo comunicar con el servidor de la API."
            });
        });
    });
}

function renderizarMatrizPermisos(permisos) {
    const porModulo = {};
    permisos.forEach(p => {
        const mod = p.modulo_etiqueta || p.modulo || "General";
        if (!porModulo[mod]) {
            porModulo[mod] = [];
        }
        porModulo[mod].push(p);
    });

    const contenedor = $("#contenedorMatrizPermisos");
    contenedor.empty();

    Object.keys(porModulo).forEach(modulo => {
        let itemsHtml = "";
        porModulo[modulo].forEach(p => {
            const idPermiso = parseInt(p.id_permiso, 10);
            const nombre = $("<div>").text(p.nombre || "").html();
            const desc = $("<div>").text(p.descripcion || "").html();

            itemsHtml += `
                <div class="custom-control custom-checkbox mb-2">
                    <input class="custom-control-input chkPermisoRol"
                           type="checkbox"
                           id="perm_${idPermiso}"
                           value="${idPermiso}">
                    <label for="perm_${idPermiso}" class="custom-control-label">
                        <strong>${nombre}</strong>
                        <div class="text-muted small">${desc}</div>
                    </label>
                </div>
            `;
        });

        contenedor.append(`
            <div class="col-md-6 mb-3">
                <div class="card card-outline card-secondary h-100 mb-0">
                    <div class="card-header py-2 bg-light">
                        <strong class="text-primary">
                            <i class="fas fa-folder-open mr-1"></i> ${modulo}
                        </strong>
                    </div>
                    <div class="card-body py-2">
                        ${itemsHtml}
                    </div>
                </div>
            </div>
        `);
    });
}

function cargarDatosRolParaEditar(idRol) {
    fetch(`${API_ROLES_URL}/${idRol}`)
        .then(res => res.json())
        .then(res => {
            if (res.success && res.data) {
                const r = res.data;
                $("#nombreRol").val(r.nombre || "");

                const rolLower = String(r.rol || "").toLowerCase();
                if ($(`#codigoRol option[value="${rolLower}"]`).length === 0 && rolLower !== "") {
                    $("#codigoRol").append(`<option value="${rolLower}">${rolLower.toUpperCase()}</option>`);
                }
                $("#codigoRol").val(rolLower || "operario");
                $("#estadoRol").val(r.estado || "Activo");

                if (parseInt(r.id_rol, 10) === 1) {
                    $("#estadoRol").prop("disabled", true);
                }

                $(".chkPermisoRol").prop("checked", false);
                if (Array.isArray(r.permisosIds)) {
                    r.permisosIds.forEach(idPerm => {
                        $(`#perm_${idPerm}`).prop("checked", true);
                    });
                }
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Rol no encontrado",
                    text: res.message || "No se encontró el rol solicitado."
                }).then(() => {
                    window.location = "roles_permisos";
                });
            }
        });
}

/*=============================================
7. SELECCIONAR / LIMPIAR TODOS LOS PERMISOS
=============================================*/
$(document).on("click", "#btnSeleccionarTodosPermisos", function() {
    $(".chkPermisoRol").prop("checked", true);
});

$(document).on("click", "#btnLimpiarPermisos", function() {
    $(".chkPermisoRol").prop("checked", false);
});
