/**
 * Script de Módulo: Gestión de Dependencias
 * Consumo 100% asíncrono de la API RESTful de ODIN (/api/dependencias).
 */

const API_DEPENDENCIAS_URL = "api/dependencias";
let tablaDependencias = null;

$(document).ready(function() {
    if ($("#tblDependencias").length > 0) {
        inicializarTablaDependencias();
        cargarEstadisticasDependencias();
    }

    if ($("#frmGuardarDependencia").length > 0) {
        inicializarFormularioDependencia();
    }
});

/*=============================================
1. INICIALIZAR DATATABLE DE DEPENDENCIAS CONSUMIENDO API REST
=============================================*/
function inicializarTablaDependencias() {
    if ($.fn.DataTable.isDataTable("#tblDependencias")) {
        $("#tblDependencias").DataTable().destroy();
    }

    tablaDependencias = $("#tblDependencias").DataTable({
        ajax: {
            url: API_DEPENDENCIAS_URL,
            type: "GET",
            dataSrc: "data",
            error: function(xhr) {
                const mensaje = xhr.responseJSON?.message || "No fue posible cargar el listado de dependencias.";
                Swal.fire({
                    icon: "error",
                    title: "Error al cargar dependencias",
                    text: mensaje
                });
            }
        },
        columns: [
            {
                data: "id_dependencia",
                render: function(data) {
                    return `<span class="badge badge-secondary">${data}</span>`;
                }
            },
            {
                data: "codigo",
                render: function(data, type, row) {
                    const codigo = data || row.dependencia || `DEP-0${row.id_dependencia}`;
                    return `<span class="badge badge-info">${$("<div>").text(codigo).html()}</span>`;
                }
            },
            {
                data: "nombre",
                render: function(data) {
                    return `<strong>${$("<div>").text(data || "").html()}</strong>`;
                }
            },
            {
                data: "descripcion",
                render: function(data) {
                    return $("<div>").text(data || "Sin descripción").html();
                }
            },
            {
                data: "total_usuarios",
                className: "text-center",
                render: function(data) {
                    return `<span class="badge badge-dark">${parseInt(data, 10) || 0}</span>`;
                }
            },
            {
                data: "estado",
                className: "text-center",
                render: function(data) {
                    const esActiva = (data === "Activo" || data === "Activa");
                    const badgeClass = esActiva ? "badge-success" : "badge-danger";
                    return `<span class="badge ${badgeClass}">${esActiva ? "Activa" : "Inactiva"}</span>`;
                }
            },
            {
                data: null,
                className: "text-center",
                orderable: false,
                render: function(data, type, row) {
                    const idDep = parseInt(row.id_dependencia, 10);
                    const esActiva = (row.estado === "Activo" || row.estado === "Activa");
                    const nombreSeguro = $("<div>").text(row.nombre || "").html();

                    const btnEstado = esActiva
                        ? `<button type="button"
                                   class="btn btn-warning btn-sm btnEstadoDependencia mr-1"
                                   data-id-dependencia="${idDep}"
                                   data-estado-dependencia="Inactivo"
                                   title="Desactivar dependencia">
                               <i class="fas fa-power-off"></i>
                           </button>`
                        : `<button type="button"
                                   class="btn btn-success btn-sm btnEstadoDependencia mr-1"
                                   data-id-dependencia="${idDep}"
                                   data-estado-dependencia="Activo"
                                   title="Activar dependencia">
                               <i class="fas fa-check"></i>
                           </button>`;

                    return `
                        <a href="dependenciasForm?id=${idDep}"
                           class="btn btn-info btn-sm mr-1"
                           title="Editar dependencia">
                            <i class="fas fa-edit"></i>
                        </a>
                        ${btnEstado}
                        <button type="button"
                                class="btn btn-danger btn-sm btnEliminarDependencia"
                                data-id-dependencia="${idDep}"
                                data-nombre-dependencia="${nombreSeguro}"
                                title="Eliminar dependencia">
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
            emptyTable: "No hay dependencias registradas",
            info: "Mostrando _START_ a _END_ de _TOTAL_ dependencias",
            infoEmpty: "Mostrando 0 a 0 de 0 dependencias",
            infoFiltered: "(filtrado de un total de _MAX_ dependencias)",
            lengthMenu: "Mostrar _MENU_ registros",
            loadingRecords: "Cargando dependencias desde la API...",
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
2. CARGAR ESTADÍSTICAS KPI DE DEPENDENCIAS
=============================================*/
function cargarEstadisticasDependencias() {
    fetch(`${API_DEPENDENCIAS_URL}/stats`)
        .then(res => res.json())
        .then(res => {
            if (res.success && res.data) {
                $("#kpiTotalDependencias").text(res.data.total ?? 0);
                $("#kpiDependenciasActivas").text(res.data.activas ?? 0);
                $("#kpiDependenciasInactivas").text(res.data.inactivas ?? 0);
            }
        })
        .catch(err => console.error("Error al cargar estadísticas de dependencias:", err));
}

/*=============================================
3. ACTIVAR / DESACTIVAR DEPENDENCIA (PATCH ASÍNCRONO)
=============================================*/
$(document).on("click", ".btnEstadoDependencia", function() {
    const idDep = $(this).data("id-dependencia");
    const nuevoEstado = $(this).data("estado-dependencia");

    fetch(`${API_DEPENDENCIAS_URL}/${idDep}/estado`, {
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
                timer: 1400,
                showConfirmButton: false
            });
            if (tablaDependencias) {
                tablaDependencias.ajax.reload(null, false);
            }
            cargarEstadisticasDependencias();
        } else {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: body.message || "No se pudo actualizar el estado de la dependencia."
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

/*=============================================
4. ELIMINAR DEPENDENCIA (DELETE ASÍNCRONO CON CONFIRMACIÓN)
=============================================*/
$(document).on("click", ".btnEliminarDependencia", function() {
    const idDep = $(this).data("id-dependencia");
    const nombreDep = $(this).data("nombre-dependencia") || "esta dependencia";

    Swal.fire({
        title: `¿Eliminar la dependencia "${nombreDep}"?`,
        text: "Esta acción no se puede deshacer.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`${API_DEPENDENCIAS_URL}/${idDep}`, {
                method: "DELETE"
            })
            .then(res => res.json().then(body => ({ status: res.status, body })))
            .then(({ status, body }) => {
                if (status === 200 && body.success) {
                    Swal.fire({
                        icon: "success",
                        title: "¡Eliminada!",
                        text: body.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    if (tablaDependencias) {
                        tablaDependencias.ajax.reload(null, false);
                    }
                    cargarEstadisticasDependencias();
                } else {
                    Swal.fire({
                        icon: "warning",
                        title: "Dependencia en uso",
                        text: body.message || "No se pudo eliminar la dependencia."
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
5. FORMULARIO DE CREACIÓN / EDICIÓN DE DEPENDENCIA
=============================================*/
function inicializarFormularioDependencia() {
    const params = new URLSearchParams(window.location.search);
    const idDepEditar = parseInt(params.get("id") || "0", 10);

    if (idDepEditar > 0) {
        $("#idDependenciaFormulario").val(idDepEditar);
        $("#tituloFormularioDep").html('<i class="fas fa-building mr-2"></i> Editar Dependencia');

        fetch(`${API_DEPENDENCIAS_URL}/${idDepEditar}`)
            .then(res => res.json())
            .then(res => {
                if (res.success && res.data) {
                    const d = res.data;
                    $("#nombreDependencia").val(d.nombre || "");
                    $("#codigoDependencia").val(d.codigo || d.dependencia || "");
                    $("#tipoDependencia").val(d.tipo || "operativa");
                    $("#descripcionDependencia").val(d.descripcion || "");
                    $("#estadoDependencia").val((d.estado === "Inactivo" || d.estado === "Inactiva") ? "Inactivo" : "Activo");
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "No encontrada",
                        text: res.message || "La dependencia solicitada no existe."
                    }).then(() => {
                        window.location = "dependencias";
                    });
                }
            });
    }

    $("#frmGuardarDependencia").on("submit", function(e) {
        e.preventDefault();

        const idDep = parseInt($("#idDependenciaFormulario").val() || "0", 10);
        const payload = {
            nombre:      $("#nombreDependencia").val().trim(),
            codigo:      $("#codigoDependencia").val().trim(),
            tipo:        $("#tipoDependencia").val(),
            descripcion: $("#descripcionDependencia").val().trim(),
            estado:      $("#estadoDependencia").val()
        };

        const esEdicion = idDep > 0;
        const url = esEdicion ? `${API_DEPENDENCIAS_URL}/${idDep}` : API_DEPENDENCIAS_URL;
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
                    title: esEdicion ? "¡Dependencia actualizada!" : "¡Dependencia creada!",
                    text: body.message,
                    confirmButtonText: "Aceptar"
                }).then(() => {
                    window.location = "dependencias";
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error al guardar",
                    text: body.message || "Por favor verifique los datos ingresados."
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
