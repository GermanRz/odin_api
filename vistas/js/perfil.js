/**
 * perfil.js - Lógica interactiva para la gestión del Perfil de Usuario en ODIN
 * 
 * Permite cargar y actualizar datos del usuario autenticado (nombre, correo,
 * teléfono, dirección y cambio opcional de contraseña) consumiendo la API REST.
 * Incluye detección y alerta condicionada cuando la contraseña sigue siendo la cédula.
 */

$(document).ready(function() {

    const modalPerfil = $("#modal-perfilUsuario");
    const frmPerfil   = $("#frmPerfilUsuario");
    const btnGuardar  = $("#btnGuardarPerfil");

    // =========================================================================
    // 1. Función para abrir modal y enfocar directamente la sección de contraseña
    // =========================================================================
    function abrirModalCambioClave() {
        modalPerfil.modal("show");

        setTimeout(function() {
            const seccion = $("#seccionCambioPassword");
            const modalBody = $("#modal-perfilUsuario .modal-body");

            if (seccion.length > 0 && modalBody.length > 0) {
                // Desplazamiento animado suave hacia la sección de contraseña
                modalBody.animate({
                    scrollTop: modalBody.scrollTop() + seccion.position().top - 15
                }, 400);

                // Efecto visual de resalto / pulso
                seccion.removeClass("card-secondary").addClass("card-warning shadow-lg");
                $("#perfilNuevaPassword").focus();

                setTimeout(function() {
                    seccion.removeClass("card-warning shadow-lg").addClass("card-secondary");
                }, 2200);
            }
        }, 450);
    }

    // =========================================================================
    // 2. Disparadores de acceso directo para cambiar contraseña
    // =========================================================================
    $(document).on("click", "#btnAccesoDirectoCambiarClave, .btnAbrirCambioClave", function(e) {
        e.preventDefault();
        abrirModalCambioClave();
    });

    // =========================================================================
    // 3. Alerta Condicionada Inicial: Notificación interactiva SweetAlert2
    // =========================================================================
    // Se ejecuta al cargar la página si el banner detectó la contraseña por defecto
    if ($("#bannerAlertaClaveDefecto").length > 0 && !sessionStorage.getItem("avisoClaveCedulaMostrado")) {
        sessionStorage.setItem("avisoClaveCedulaMostrado", "true");

        Swal.fire({
            title: "¡Aviso de Seguridad Institucional!",
            html: `Detectamos que su contraseña actual <b>sigue siendo su número de cédula</b>.<br><br>
                   Por políticas de seguridad y protección de la información del <b>Sistema ODIN</b>, 
                   debe cambiar su contraseña por una clave personal segura.`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#ffc107",
            cancelButtonColor: "#6c757d",
            confirmButtonText: '<i class="fas fa-key mr-1"></i> Cambiar mi contraseña ahora',
            cancelButtonText: "Recordar más tarde",
            focusConfirm: true
        }).then(function(result) {
            if (result.isConfirmed) {
                abrirModalCambioClave();
            }
        });
    }

    // =========================================================================
    // 4. Alternar visualización de contraseñas (Mostrar / Ocultar)
    // =========================================================================
    $(document).on("click", ".btnVerPassword", function() {
        const targetSelector = $(this).data("target");
        const input = $(targetSelector);
        const icon = $(this).find("i");

        if (input.attr("type") === "password") {
            input.attr("type", "text");
            icon.removeClass("fa-eye").addClass("fa-eye-slash");
        } else {
            input.attr("type", "password");
            icon.removeClass("fa-eye-slash").addClass("fa-eye");
        }
    });

    // =========================================================================
    // 5. Al abrir el modal: Cargar datos actuales del usuario desde la API REST
    // =========================================================================
    modalPerfil.on("show.bs.modal", function() {
        const idUsuario = $("#perfilIdUsuario").val();

        if (!idUsuario || parseInt(idUsuario, 10) <= 0) {
            Swal.fire({
                icon: "warning",
                title: "Sesión no identificada",
                text: "No fue posible identificar al usuario activo. Por favor vuelva a iniciar sesión."
            });
            return;
        }

        // Limpiar campos de contraseñas y resetear iconos de visibilidad
        $("#perfilNuevaPassword").val("").attr("type", "password");
        $("#perfilConfirmarPassword").val("").attr("type", "password");
        $(".btnVerPassword i").removeClass("fa-eye-slash").addClass("fa-eye");

        // Petición asíncrona a la API
        fetch("api/usuarios/" + idUsuario, {
            method: "GET",
            headers: {
                "Accept": "application/json"
            }
        })
        .then(function(res) {
            return res.json().then(function(body) {
                return { status: res.status, body: body };
            });
        })
        .then(function(resObj) {
            if (resObj.status === 200 && resObj.body.success && resObj.body.data) {
                const u = resObj.body.data;

                // Actualizar cabecera visual
                $("#perfilHeaderNombre").text(u.nombre || "Usuario");
                $("#perfilHeaderCorreo").text(u.correo || "");
                $("#badgePerfilRol").html('<i class="fas fa-user-shield mr-1"></i> ' + (u.rol || "Rol Asignado"));
                $("#badgePerfilDependencia").html('<i class="fas fa-building mr-1"></i> ' + (u.dependencia || "Dependencia General"));
                $("#badgePerfilEstado").html('<i class="fas fa-check-circle mr-1"></i> ' + (u.estado || "Activo"));

                // Poblar formulario
                $("#perfilTipoDoc").val(u.tipo_identificacion || "CC");
                $("#perfilNumDoc").val(u.num_identificacion || "");
                $("#perfilNombre").val(u.nombre || "");
                $("#perfilCorreo").val(u.correo || "");
                $("#perfilTelefono").val(u.telefono || "");
                $("#perfilDireccion").val(u.direccion || "");
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error al cargar perfil",
                    text: (resObj.body && resObj.body.message) ? resObj.body.message : "No se pudieron obtener sus datos."
                });
            }
        })
        .catch(function(err) {
            console.error("Error al consultar perfil:", err);
            Swal.fire({
                icon: "error",
                title: "Error de conexión",
                text: "No se pudo comunicar con el servidor para consultar su perfil."
            });
        });
    });

    // =========================================================================
    // 6. Enviar formulario para actualizar el perfil
    // =========================================================================
    frmPerfil.on("submit", function(e) {
        e.preventDefault();

        const idUsuario         = $("#perfilIdUsuario").val();
        const nombre            = $("#perfilNombre").val().trim();
        const correo            = $("#perfilCorreo").val().trim();
        const telefono          = $("#perfilTelefono").val().trim();
        const direccion         = $("#perfilDireccion").val().trim();
        const nuevaPassword     = $("#perfilNuevaPassword").val().trim();
        const confirmarPassword = $("#perfilConfirmarPassword").val().trim();

        // Validaciones en cliente
        if (!nombre || !correo) {
            Swal.fire({
                icon: "warning",
                title: "Campos incompletos",
                text: "El nombre y el correo electrónico son obligatorios."
            });
            return;
        }

        // Validación de cambio de contraseña opcional
        if (nuevaPassword !== "" || confirmarPassword !== "") {
            if (nuevaPassword.length < 4) {
                Swal.fire({
                    icon: "warning",
                    title: "Contraseña muy corta",
                    text: "La nueva contraseña debe tener al menos 4 caracteres."
                });
                return;
            }

            if (nuevaPassword !== confirmarPassword) {
                Swal.fire({
                    icon: "error",
                    title: "Contraseñas no coinciden",
                    text: "La nueva contraseña y su confirmación deben ser exactamente iguales."
                });
                return;
            }

            // Validar que la nueva contraseña no vuelva a ser la cédula
            const numDocActual = $("#perfilNumDoc").val().trim();
            if (numDocActual !== "" && nuevaPassword === numDocActual) {
                Swal.fire({
                    icon: "warning",
                    title: "Contraseña no permitida",
                    text: "La nueva contraseña no puede ser idéntica a su número de documento de identidad."
                });
                return;
            }
        }

        // Construir payload
        const payload = {
            nombre: nombre,
            correo: correo,
            telefono: telefono,
            direccion: direccion
        };

        if (nuevaPassword !== "") {
            payload.password = nuevaPassword;
        }

        // Estado visual del botón durante la petición
        const originalBtnHtml = btnGuardar.html();
        btnGuardar.prop("disabled", true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Guardando...');

        fetch("api/usuarios/" + idUsuario, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify(payload)
        })
        .then(function(res) {
            return res.json().then(function(body) {
                return { status: res.status, body: body };
            });
        })
        .then(function(resObj) {
            btnGuardar.prop("disabled", false).html(originalBtnHtml);

            if (resObj.status === 200 && resObj.body.success) {
                Swal.fire({
                    icon: "success",
                    title: "¡Perfil Actualizado!",
                    text: resObj.body.message || "Sus datos han sido actualizados satisfactoriamente.",
                    timer: 2000,
                    showConfirmButton: false
                });

                // Actualizar nombre en el sidebar en tiempo real
                $("#perfilNombreSidebar").text(nombre);
                $("#perfilHeaderNombre").text(nombre);
                $("#perfilHeaderCorreo").text(correo);

                // Si se cambió la contraseña exitosamente, remover el aviso persistente
                if (nuevaPassword !== "") {
                    $("#bannerAlertaClaveDefecto").slideUp(400, function() {
                        $(this).remove();
                    });
                    sessionStorage.removeItem("avisoClaveCedulaMostrado");
                }

                // Si la tabla de usuarios existe en la vista actual, refrescarla
                if (typeof $.fn.DataTable !== "undefined" && $("#tblUsuarios").length > 0 && $.fn.DataTable.isDataTable("#tblUsuarios")) {
                    $("#tblUsuarios").DataTable().ajax.reload(null, false);
                }

                // Cerrar modal
                modalPerfil.modal("hide");
            } else {
                Swal.fire({
                    icon: "error",
                    title: "No fue posible guardar los cambios",
                    text: (resObj.body && resObj.body.message) ? resObj.body.message : "Ocurrió un error inesperado."
                });
            }
        })
        .catch(function(err) {
            btnGuardar.prop("disabled", false).html(originalBtnHtml);
            console.error("Error al actualizar perfil:", err);
            Swal.fire({
                icon: "error",
                title: "Error de comunicación",
                text: "No se pudo conectar con el servidor para guardar los cambios."
            });
        });
    });

});
