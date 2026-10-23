/**
 * Script de Módulo: Gestión de Usuarios
 * Consumo 100% asíncrono de la API RESTful de ODIN (/api/usuarios).
 */

const API_USUARIOS_URL = "api/usuarios";
let tablaUsuarios = null;

$(document).ready(function() {
    inicializarTablaUsuarios();
    cargarEstadisticas();
    cargarSelects();
    configurarFormularioCrear();
    configurarFormularioEditar();
});

/*=============================================
1. INICIALIZAR DATATABLE CONSUMIENDO LA API REST
=============================================*/
function inicializarTablaUsuarios() {
    if ($.fn.DataTable.isDataTable("#tblUsuarios")) {
        $("#tblUsuarios").DataTable().destroy();
    }

    tablaUsuarios = $("#tblUsuarios").DataTable({
        ajax: {
            url: API_USUARIOS_URL,
            type: "GET",
            dataSrc: "data",
            error: function(xhr) {
                const mensaje = xhr.responseJSON?.message || "No fue posible conectar con el servidor de la API.";
                Swal.fire({
                    icon: "error",
                    title: "Error al cargar usuarios",
                    text: mensaje
                });
            }
        },
        columns: [
            { data: "id_usuario" },
            { data: "nombre" },
            { data: "num_identificacion" },
            { data: "correo" },
            {
                data: "rol",
                defaultContent: "<span class='text-muted'>Sin rol</span>"
            },
            {
                data: "dependencia",
                defaultContent: "<span class='text-muted'>Sin dependencia</span>"
            },
            {
                data: "estado",
                render: function(data, type, row) {
                    const colorClass = data === "Activo" ? "btn-success" : "btn-danger";
                    const nuevoEstado = data === "Activo" ? "Inactivo" : "Activo";
                    const deshabilitado = parseInt(row.id_usuario, 10) === 1 ? "disabled" : "";
                    return `<button class="btn btn-xs ${colorClass} btnCambiarEstado" 
                                    data-id="${row.id_usuario}" 
                                    data-estado="${nuevoEstado}"
                                    title="Clic para cambiar a ${nuevoEstado}"
                                    ${deshabilitado}>
                                ${data}
                            </button>`;
                }
            },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    const idUsr = parseInt(row.id_usuario, 10);
                    const esAdminPrincipal = idUsr === 1;
                    const nombreSeguro = $("<div>").text(row.nombre || "").html();

                    return `
                        <button class="btn btn-warning btn-sm btnCargarEditar mr-1" 
                                data-id="${idUsr}"
                                title="Modificar usuario">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm btnEliminarUsuario"
                                data-id="${idUsr}"
                                data-nombre="${nombreSeguro}"
                                title="Eliminar usuario"
                                ${esAdminPrincipal ? "disabled" : ""}>
                            <i class="fas fa-trash"></i>
                        </button>`;
                }
            }
        ],
        responsive: true,
        autoWidth: false,
        language: {
            decimal: "",
            emptyTable: "No hay registros disponibles en la tabla",
            info: "Mostrando _START_ a _END_ de _TOTAL_ usuarios",
            infoEmpty: "Mostrando 0 a 0 de 0 usuarios",
            infoFiltered: "(filtrado de un total de _MAX_ usuarios)",
            lengthMenu: "Mostrar _MENU_ registros",
            loadingRecords: "Cargando usuarios desde la API...",
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
2. CARGAR ESTADÍSTICAS ASÍNCRONAMENTE
=============================================*/
function cargarEstadisticas() {
    fetch(`${API_USUARIOS_URL}/stats`)
        .then(res => res.json())
        .then(res => {
            if (res.success && res.data) {
                $("#totalUsuarios").text(res.data.total ?? 0);
                $("#totalUsuariosActivos").text(res.data.activos ?? 0);
                $("#totalUsuariosInactivos").text(res.data.inactivos ?? 0);
                $("#totalRolesActivos").text(res.data.rolesActivos ?? 0);
            }
        })
        .catch(err => {
            console.error("Error al obtener estadísticas:", err);
        });
}

/*=============================================
3. CARGAR SELECTS DE ROLES Y DEPENDENCIAS
=============================================*/
function cargarSelects() {
    fetch("api/roles")
        .then(res => res.json())
        .then(res => {
            if (res.success && Array.isArray(res.data)) {
                let options = '<option value="">Seleccione un rol</option>';
                res.data.forEach(r => {
                    if (r.estado === "Activo") {
                        options += `<option value="${r.id_rol}">${r.nombre || r.rol}</option>`;
                    }
                });
                $("#nuevoRol").html(options);
                $("#editarRol").html(options);
            }
        })
        .catch(err => console.error("Error al cargar roles:", err));

    fetch("api/dependencias")
        .then(res => res.json())
        .then(res => {
            if (res.success && Array.isArray(res.data)) {
                let options = '<option value="">Seleccione una dependencia</option>';
                res.data.forEach(d => {
                    if (d.estado === "Activo" || d.estado === "Activa") {
                        options += `<option value="${d.id_dependencia}">${d.nombre || d.dependencia}</option>`;
                    }
                });
                $("#nuevaDependencia").html(options);
                $("#editarDependencia").html(options);
            }
        })
        .catch(err => console.error("Error al cargar dependencias:", err));
}

/*=============================================
4. CREAR USUARIO (POST ASÍNCRONO)
=============================================*/
function configurarFormularioCrear() {
    $("#frmAgregarUsuario").on("submit", function(e) {
        e.preventDefault();

        const payload = {
            tipoIdentificacion:   $("#nuevoTipoDocumento").val(),
            numeroIdentificacion: $("#nuevoNumeroIdentificacion").val(),
            nombre:               $("#nuevoNombre").val(),
            correo:               $("#nuevoCorreo").val(),
            idRol:                $("#nuevoRol").val(),
            idDependencia:        $("#nuevaDependencia").val(),
            direccion:            $("#nuevaDireccion").val(),
            telefono:             $("#nuevoTelefono").val()
        };

        fetch(API_USUARIOS_URL, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            if (status === 201 && body.success) {
                Swal.fire({
                    icon: "success",
                    title: "¡Usuario registrado!",
                    text: body.message,
                    timer: 1800,
                    showConfirmButton: false
                });
                $("#modal-agregarUsuario").modal("hide");
                $("#frmAgregarUsuario")[0].reset();
                tablaUsuarios.ajax.reload(null, false);
                cargarEstadisticas();
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error al guardar",
                    text: body.message || "Por favor revise los datos ingresados."
                });
            }
        })
        .catch(() => {
            Swal.fire({
                icon: "error",
                title: "Error de conexión",
                text: "No fue posible comunicarse con el servidor de la API."
            });
        });
    });
}

/*=============================================
5. VER FICHA DETALLADA Y PERMISOS DEL USUARIO
=============================================*/
$(document).on("click", ".btnVerUsuario", function() {
    const idUsuario = $(this).data("id");

    fetch(`${API_USUARIOS_URL}/${idUsuario}`)
        .then(res => res.json())
        .then(res => {
            if (res.success && res.data) {
                const u = res.data;
                $("#verNombreUsuario").text(u.nombre || "");
                $("#verIdentificacionUsuario").text(`${u.tipo_identificacion || "CC"} - ${u.num_identificacion || ""}`);
                $("#verCorreoUsuario").text(u.correo || "");
                $("#verTelefonoUsuario").text(u.telefono || "No registrado");
                $("#verRolUsuario").text(u.rol_nombre || "Sin rol");
                $("#verDependenciaUsuario").text(u.dependencia_nombre || "Sin dependencia");
                $("#verDireccionUsuario").text(u.direccion || "No registrada");

                const badgeEstado = u.estado === "Activo"
                    ? '<span class="badge badge-success">Activo</span>'
                    : '<span class="badge badge-danger">Inactivo</span>';
                $("#verEstadoUsuario").html(badgeEstado);

                const contenedorPermisos = $("#verPermisosUsuario");
                contenedorPermisos.empty();

                if (Array.isArray(u.permisos_rol) && u.permisos_rol.length > 0) {
                    u.permisos_rol.forEach(p => {
                        const mod = p.modulo_etiqueta || p.modulo || "General";
                        contenedorPermisos.append(`
                            <div class="col-md-6 mb-2">
                                <div class="p-2 border rounded bg-light h-100">
                                    <span class="badge badge-primary mr-1">${mod}</span>
                                    <strong>${$("<div>").text(p.nombre || "").html()}</strong>
                                    <div class="small text-muted">${$("<div>").text(p.descripcion || "").html()}</div>
                                </div>
                            </div>
                        `);
                    });
                } else {
                    contenedorPermisos.html('<div class="col-12 text-muted">El rol asignado no posee permisos granulares configurados.</div>');
                }

                $("#modal-verUsuario").modal("show");
            }
        });
});

/*=============================================
6. CARGAR DATOS PARA EDICIÓN (GET ESPECÍFICO)
=============================================*/
$(document).on("click", ".btnCargarEditar", function() {
    const idUsuario = $(this).data("id");

    fetch(`${API_USUARIOS_URL}/${idUsuario}`)
        .then(res => res.json())
        .then(res => {
            if (res.success && res.data) {
                const u = res.data;
                $("#editarIdUsuario").val(u.id_usuario);
                $("#editarTipoDocumento").val(u.tipo_identificacion || "CC");
                $("#editarNumeroIdentificacion").val(u.num_identificacion);
                $("#editarNombre").val(u.nombre);
                $("#editarCorreo").val(u.correo);
                $("#editarRol").val(u.id_rol);
                $("#editarDependencia").val(u.id_dependencia);
                $("#editarDireccion").val(u.direccion);
                $("#editarTelefono").val(u.telefono);
                $("#editarPassword").val("");

                $("#modal-editarUsuario").modal("show");
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: res.message || "No se encontraron los datos del usuario."
                });
            }
        })
        .catch(() => {
            Swal.fire({
                icon: "error",
                title: "Error de conexión",
                text: "No fue posible consultar los datos del usuario."
            });
        });
});

/*=============================================
7. ACTUALIZAR USUARIO (PUT ASÍNCRONO)
=============================================*/
function configurarFormularioEditar() {
    $("#frmEditarUsuario").on("submit", function(e) {
        e.preventDefault();

        const idUsuario = $("#editarIdUsuario").val();
        const payload = {
            nombre:        $("#editarNombre").val(),
            correo:        $("#editarCorreo").val(),
            idRol:         $("#editarRol").val(),
            idDependencia: $("#editarDependencia").val(),
            direccion:     $("#editarDireccion").val(),
            telefono:      $("#editarTelefono").val(),
            password:      $("#editarPassword").val()
        };

        fetch(`${API_USUARIOS_URL}/${idUsuario}`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(payload)
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.success) {
                Swal.fire({
                    icon: "success",
                    title: "¡Usuario actualizado!",
                    text: body.message,
                    timer: 1800,
                    showConfirmButton: false
                });
                $("#modal-editarUsuario").modal("hide");
                tablaUsuarios.ajax.reload(null, false);
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error al actualizar",
                    text: body.message || "Ocurrió un error al actualizar los datos."
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
    });
}

/*=============================================
8. CAMBIO DE ESTADO ATÓMICO (PATCH ASÍNCRONO)
=============================================*/
$(document).on("click", ".btnCambiarEstado", function() {
    const boton = $(this);
    const idUsuario = boton.data("id");
    const nuevoEstado = boton.data("estado");

    fetch(`${API_USUARIOS_URL}/${idUsuario}/estado`, {
        method: "PATCH",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ estado: nuevoEstado })
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            tablaUsuarios.ajax.reload(null, false);
            cargarEstadisticas();
        } else {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: res.message || "No se pudo cambiar el estado del usuario."
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
9. ELIMINAR USUARIO (DELETE ASÍNCRONO)
=============================================*/
$(document).on("click", ".btnEliminarUsuario", function() {
    const idUsuario = $(this).data("id");
    const nombreUsuario = $(this).data("nombre") || "este usuario";

    Swal.fire({
        title: `¿Eliminar a "${nombreUsuario}"?`,
        text: "Esta acción eliminará el registro del usuario de forma permanente.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`${API_USUARIOS_URL}/${idUsuario}`, {
                method: "DELETE"
            })
            .then(res => res.json().then(body => ({ status: res.status, body })))
            .then(({ status, body }) => {
                if (status === 200 && body.success) {
                    Swal.fire({
                        icon: "success",
                        title: "¡Eliminado!",
                        text: body.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    tablaUsuarios.ajax.reload(null, false);
                    cargarEstadisticas();
                } else {
                    Swal.fire({
                        icon: "warning",
                        title: "No se puede eliminar",
                        text: body.message || "No fue posible eliminar el usuario."
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
