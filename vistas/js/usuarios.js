

$(document).on("click", ".btnActivarUsuario", function() {
    // converitimos en numero el total de usuarios activos que tenemos en la base de datos, para luego restarle 1 si el usuario se desactiva, o sumarle 1 si el usuario se activa. Esto nos permite mantener actualizado el contador de usuarios activos en tiempo real sin necesidad de recargar la página.
    let totalUsuariosActivos = parseInt($("#totalUsuariosActivos").html());
    let boton = $(this);
    let idUsuario = $(this).attr("data-id-usuario") || $(this).attr("data-idUsuario");
    let estadoUsuario = $(this).attr("data-estado-usuario") || $(this).attr("data-estadoUsuario");

    let datos = new FormData();
    datos.append("idUsuario", idUsuario);
    datos.append("nuevoEstadoUsuario", estadoUsuario);

    $.ajax({
        url: "ajax/usuarios.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta) {
            if (respuesta === true) {
                if (estadoUsuario === "Activo") {
                    boton.removeClass("btn-danger");
                    boton.addClass("btn-success");
                    boton.html("Activo");
                    boton.attr("data-estado-usuario", "Inactivo");
                    boton.attr("data-estadoUsuario", "Inactivo");
                    totalUsuariosActivos++;
                    $("#totalUsuariosActivos").html(totalUsuariosActivos);
                } else {
                    boton.removeClass("btn-success");
                    boton.addClass("btn-danger");
                    boton.html("Inactivo");
                    boton.attr("data-estado-usuario", "Activo");
                    boton.attr("data-estadoUsuario", "Activo");
                    totalUsuariosActivos--;
                    $("#totalUsuariosActivos").html(totalUsuariosActivos);
                }
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudo actualizar el estado del usuario."
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: "error",
                title: "Error de comunicación",
                text: "No se pudo conectar con el servidor."
            });
        }
    });
});// fin de activar/desactivar usuario

$(document).on("click", ".btnEditarUsuario", function() {
    let idUsuario = $(this).attr("data-id-usuario") || $(this).attr("data-idUsuario");
    let datos = new FormData();
    datos.append("idUsuarioEditar", idUsuario);
    $.ajax({
        url: "ajax/usuarios.ajax.php",
        method: "POST",
        data: datos,
        cache: false,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function(respuesta) {
            $("#idUsuario").val(respuesta["id_usuario"]);
            $("#editarTipoDocumento").val(respuesta["tipo_identificacion"]);
            $("#editarNumeroIdentificacion").val(respuesta["num_identificacion"]);
            $("#editarNombreUsuario").val(respuesta["nombre"]);
            $("#editarCorreo").val(respuesta["correo"]);
            $("#editarDireccion").val(respuesta["direccion"]);
            $("#editarTelefono").val(respuesta["telefono"]);
            $("#editarRol").val(respuesta["id_rol"]);
            $("#editarDependencia").val(respuesta["id_dependencia"]);
        }
    });
    

})// fin de editar usuarios