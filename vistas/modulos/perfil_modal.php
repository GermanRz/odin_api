<!-- =======================================================
MODAL: PERFIL DE USUARIO (Actualización de datos personales)
======================================================= -->
<div class="modal fade" id="modal-perfilUsuario" tabindex="-1" role="dialog" aria-labelledby="modalPerfilLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content shadow-lg border-0">

      <!-- Cabecera del Modal con tema AdminLTE -->
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title font-weight-bold" id="modalPerfilLabel">
          <i class="fas fa-id-badge mr-2"></i> Mi Perfil de Usuario
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="frmPerfilUsuario" autocomplete="off">
        <div class="modal-body p-4">

          <!-- Tarjeta de Identidad Visual del Usuario -->
          <div class="card card-outline card-primary mb-3">
            <div class="card-body box-profile text-center py-3">
              <div class="position-relative d-inline-block">
                <img class="profile-user-img img-fluid img-circle elevation-2"
                     src="vistas/dist/img/user2-160x160.jpg"
                     alt="Foto de perfil del usuario"
                     style="width: 90px; height: 90px; object-fit: cover;">
              </div>
              <h4 class="profile-username text-center font-weight-bold mt-2 mb-1" id="perfilHeaderNombre">
                <?php echo htmlspecialchars((string)($_SESSION["nombre"] ?? "Usuario ODIN")); ?>
              </h4>
              <p class="text-muted text-center mb-2" id="perfilHeaderCorreo">
                <?php echo htmlspecialchars((string)($_SESSION["correo"] ?? "")); ?>
              </p>
              
              <!-- Badges informativos de rol y dependencia -->
              <div class="d-flex justify-content-center align-items-center flex-wrap" style="gap: 8px;">
                <span class="badge badge-primary px-3 py-1 font-weight-normal" id="badgePerfilRol">
                  <i class="fas fa-user-shield mr-1"></i> Rol: Cargando...
                </span>
                <span class="badge badge-info px-3 py-1 font-weight-normal" id="badgePerfilDependencia">
                  <i class="fas fa-building mr-1"></i> Dependencia: Cargando...
                </span>
                <span class="badge badge-success px-3 py-1 font-weight-normal" id="badgePerfilEstado">
                  <i class="fas fa-check-circle mr-1"></i> Activo
                </span>
              </div>
            </div>
          </div>

          <!-- Campo oculto con ID del usuario en sesión -->
          <input type="hidden" id="perfilIdUsuario" value="<?php echo htmlspecialchars((string)($_SESSION['id'] ?? $_SESSION['id_usuario'] ?? '')); ?>">

          <!-- 1. SECCIÓN: DATOS DE IDENTIFICACIÓN INSTITUCIONAL (Sólo Lectura) -->
          <div class="row">
            <div class="col-md-4 form-group">
              <label for="perfilTipoDoc" class="text-muted small font-weight-bold text-uppercase">
                <i class="fas fa-id-card mr-1"></i> Tipo Documento
              </label>
              <input type="text" class="form-control bg-light" id="perfilTipoDoc" readonly>
            </div>

            <div class="col-md-8 form-group">
              <label for="perfilNumDoc" class="text-muted small font-weight-bold text-uppercase">
                <i class="fas fa-fingerprint mr-1"></i> Número de Identificación
              </label>
              <input type="text" class="form-control bg-light" id="perfilNumDoc" readonly>
              <small class="form-text text-muted">El documento de identidad está bloqueado por políticas de integridad institucional.</small>
            </div>
          </div>

          <!-- 2. SECCIÓN: INFORMACIÓN PERSONAL EDITABLE -->
          <div class="row">
            <div class="col-md-6 form-group">
              <label for="perfilNombre" class="font-weight-bold small text-uppercase">
                Nombre Completo <span class="text-danger">*</span>
              </label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-user"></i></span>
                </div>
                <input type="text" class="form-control" id="perfilNombre" required placeholder="Nombres y Apellidos">
              </div>
            </div>

            <div class="col-md-6 form-group">
              <label for="perfilCorreo" class="font-weight-bold small text-uppercase">
                Correo Electrónico <span class="text-danger">*</span>
              </label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                </div>
                <input type="email" class="form-control" id="perfilCorreo" required placeholder="correo@institucion.gov.co">
              </div>
            </div>
          </div>

          <!-- 3. SECCIÓN: DATOS DE CONTACTO Y UBICACIÓN -->
          <div class="row">
            <div class="col-md-6 form-group">
              <label for="perfilTelefono" class="font-weight-bold small text-uppercase">
                Teléfono de Contacto
              </label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-phone-alt"></i></span>
                </div>
                <input type="text" class="form-control" id="perfilTelefono" placeholder="Ej: 3101234567 o PBX 601...">
              </div>
            </div>

            <div class="col-md-6 form-group">
              <label for="perfilDireccion" class="font-weight-bold small text-uppercase">
                Dirección Física
              </label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                </div>
                <input type="text" class="form-control" id="perfilDireccion" placeholder="Dirección residencial o institucional">
              </div>
            </div>
          </div>

          <!-- 4. SECCIÓN: CAMBIO DE CONTRASEÑA (OPCIONAL) -->
          <div class="card card-secondary card-outline mt-2 mb-0" id="seccionCambioPassword">
            <div class="card-header py-2 bg-light">
              <h3 class="card-title text-sm font-weight-bold text-dark">
                <i class="fas fa-lock mr-1 text-warning"></i> Cambiar Contraseña de Acceso (Opcional)
              </h3>
            </div>
            <div class="card-body p-3">
              <p class="text-muted small mb-3">
                <i class="fas fa-info-circle mr-1 text-info"></i>
                Deje estos campos en blanco si desea conservar su contraseña actual.
              </p>
              <div class="row">
                <div class="col-md-6 form-group mb-md-0">
                  <label for="perfilNuevaPassword" class="small font-weight-bold">Nueva Contraseña</label>
                  <div class="input-group">
                    <input type="password" class="form-control" id="perfilNuevaPassword" placeholder="Mínimo 4 caracteres">
                    <div class="input-group-append">
                      <button class="btn btn-outline-secondary btnVerPassword" type="button" data-target="#perfilNuevaPassword" title="Mostrar/Ocultar">
                        <i class="fas fa-eye"></i>
                      </button>
                    </div>
                  </div>
                </div>

                <div class="col-md-6 form-group mb-0">
                  <label for="perfilConfirmarPassword" class="small font-weight-bold">Confirmar Nueva Contraseña</label>
                  <div class="input-group">
                    <input type="password" class="form-control" id="perfilConfirmarPassword" placeholder="Repita la nueva contraseña">
                    <div class="input-group-append">
                      <button class="btn btn-outline-secondary btnVerPassword" type="button" data-target="#perfilConfirmarPassword" title="Mostrar/Ocultar">
                        <i class="fas fa-eye"></i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- Pie del Modal con botones de acción -->
        <div class="modal-footer justify-content-between bg-light">
          <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Cancelar
          </button>
          <button type="submit" class="btn btn-primary px-4 font-weight-bold" id="btnGuardarPerfil">
            <i class="fas fa-save mr-1"></i> Guardar Cambios
          </button>
        </div>
      </form>

    </div>
  </div>
</div>
