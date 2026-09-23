<div class="login-box">

  <div class="login-logo">
    <a href="#"><b>ODIN</b> Sistema</a>
  </div>

  <!-- /.login-logo -->
  <div class="card card-outline card-primary">
    <div class="card-body login-card-body">
      <p class="login-box-msg">Ingresa al sistema para iniciar sesión</p>

      <form method="post">

        <div class="input-group mb-3">
          <input type="text" class="form-control" placeholder="Usuario / Identificación" name="ingUsuario" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>

        <div class="input-group mb-3">
          <input type="password" class="form-control" placeholder="Contraseña" name="ingPassword" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
          </div>
        </div>

        <?php
          ControladorUsuarios::ctrIngresoUsuario();
        ?>

      </form>

    </div>
    <!-- /.login-card-body -->
  </div>

</div>
