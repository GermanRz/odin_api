  <!-- Content Wrapper. Contains page content -->
  <!-- <div class="content-wrapper"> -->
  <!-- Content Header (Page header) -->
  <?php
  $totalUsuarios = ControladorUsuarios::ctrContarUsuarios();
  $totalUsuariosActivos = ControladorUsuarios::ctrContarUsuariosActivos();
  ?>

  <section class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1>Gestion de usuarios</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
            <!-- <li class="breadcrumb-item active">Inicio</li> -->
          </ol>
        </div>
      </div>
    </div><!-- /.container-fluid -->
  </section>

  <!-- Main content -->
  <section class="content">

    <div class="container-fluid">
      <!-- Small boxes (Stat box) -->
      <div class="row">


        <div class="col-lg-3 col-sm-12">
          <!-- small box -->
          <div class="small-box bg-info">
            <div class="inner">
              <h3 id="totalUsuarios"><?php echo $totalUsuarios['total']; ?></h3>
              <p>Total registrados</p>
            </div>
            <div class="icon">
              <i class="ion ion-person"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->

        <div class="col-lg-3 col-12">
          <!-- small box -->
          <div class="small-box bg-info">
            <div class="inner">
              <h3 id="totalUsuariosActivos"><?php echo $totalUsuariosActivos['total']; ?></h3>
              <p>Usuarios activos</p>
            </div>
            <div class="icon">
              <i class="ion ion-person-stalker"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->

        <div class="col-lg-3 col-12">
          <!-- small box -->
          <div class="small-box bg-info">
            <div class="inner">
              <h3>150</h3>
              <p>Documentos en trámite</p>
            </div>
            <div class="icon">
              <i class="ion ion-bag"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->

        <div class="col-lg-3 col-12">
          <!-- small box -->
          <div class="small-box bg-info">
            <div class="inner">
              <h3>150</h3>
              <p>Documentos cerrados</p>
            </div>
            <div class="icon">
              <i class="ion ion-bag"></i>
            </div>
          </div>
        </div>
        <!-- ./col -->




      </div>
      <!-- /.row -->


      <div class="row">
        <div class="col-12">

          <div class="card">
            <div class="card-header">
              <!-- <h3 class="card-title">DataTable with default features</h3> -->
              <div class="card-tools ml-auto">
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal-agregarUsuario">Agregar Usuario</button>
              </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
              <table id="tblUsuarios" class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>id</th>
                    <th>usuario</th>
                    <th>identificacion</th>
                    <th>Rol</th>
                    <th>Dependencia</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  $respuesta = ControladorUsuarios::ctrMostrarUsuarios();
                  // var_dump($respuesta);

                  foreach ($respuesta as $usuario) {
                    echo '<tr>';
                    echo '<td>' . $usuario['id_usuario'] . '</td>';
                    echo '<td>' . $usuario['nombre'] . '</td>';
                    echo '<td>' . $usuario['num_identificacion'] . '</td>';
                    echo '<td>' . $usuario['rol'] . '</td>';
                    echo '<td>' . $usuario['dependencia'] . '</td>';
                    echo '<td>';
                    // boton de estado activo o inactivo
                    if ($usuario['estado'] == 'Activo') {
                      echo "<button class='btn btn-xs btn-success btnActivarUsuario' data-estado-usuario='Inactivo' data-estadoUsuario='Inactivo' data-id-usuario='" . $usuario['id_usuario'] . "' data-idUsuario='" . $usuario['id_usuario'] . "'>Activo</button>";
                    } else {
                      echo "<button class='btn btn-xs btn-danger btnActivarUsuario' data-estado-usuario='Activo' data-estadoUsuario='Activo' data-id-usuario='" . $usuario['id_usuario'] . "' data-idUsuario='" . $usuario['id_usuario'] . "'>Inactivo</button>";
                    }

                    echo '</td>';
                    echo '<td>';
                    echo '<button class="btnEditarUsuario" data-id-usuario="' . $usuario['id_usuario'] . '" data-idUsuario="' . $usuario['id_usuario'] . '" data-toggle="modal" data-target="#modal-editarUsuario"><i class="fa fa-edit"></i></button>';
                    echo '<button><i class="fa fa-eye"></i></button>';
                    echo '</td>';
                    echo '</tr>';
                  }  // End of foreach

                  ?>

                </tbody>


              </table>
            </div>
            <!-- /.card-body -->
          </div>
          <!-- /.card -->

        </div>
      </div>
    </div>

  </section>
  <!-- /.content -->
  <!-- </div> -->
  <!-- /.content-wrapper -->


  <!-- ***************************************************************************************************************************************** -->

  <!-- MODAL DE NUEVO USUARIO -->
  <div class="modal fade" id="modal-agregarUsuario">
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-header bg-primary">

          <h4 class="modal-title">Agregar Usuario</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>

        </div>

        <div class="modal-body">
          <!-- <p>One fine body&hellip;</p> -->
          <form id="frmUsuarios" class="form-horizontal" method="post">
            <div class="card-body">

              <!-- select de tipo de documento y numero de identificacion -->
              <div class="form-group row">

                <div class="col-md-5">
                  <select class="form-control" id="inputTipoDocumento" name="nuevoTipoDocumento">
                    <option value="">Seleccione</option>
                    <option value="TI">Tarjeta de identidad</option>
                    <option value="CC">Cédula de ciudadanía</option>
                    <option value="CE">Cédula de extranjería</option>
                    <option value="PA">Pasaporte</option>
                  </select>
                </div>

                <div class="col-md-7">
                  <input type="text" class="form-control" id="inputNumeroIdentificacion" placeholder="Número de identificación" name="nuevoNumeroIdentificacion">
                </div>

              </div>

              <!-- input de nombre de usuario -->
              <div class="input-group mb-3">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-user"></i></span>
                </div>
                <input type="text" class="form-control" id="inputUsuario" placeholder="Nombre completo" name="nuevoNombre">
              </div>

              <!-- input de correo electronico -->
              <div class="input-group mb-3">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                </div>
                <input type="email" class="form-control" placeholder="Email" name="nuevoCorreo">
              </div>

              <!-- input de direccion -->
              <div class="input-group mb-3">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                </div>
                <input type="text" class="form-control" placeholder="Dirección" name="nuevaDireccion">
              </div>


              <!-- input de telefono -->
              <div class="input-group mb-3">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-phone"></i></span>
                </div>
                <input type="text" class="form-control" placeholder="Teléfono" name="nuevoTelefono">
              </div>


              <!-- select del rol del usuario -->
              <div class="form-group row">
                <label for="inputRol" class="col-sm-2 col-form-label">Rol</label>
                <div class="col-sm-10">

                  <?php
                  $respuesta = ControladorRoles::ctrMostrarRoles();
                  // var_dump($respuesta);
                  echo '<select class="form-control" id="inputRol" name="nuevoRol">';
                  echo '<option value="">Seleccione</option>';
                  foreach ($respuesta as $rol) {
                    echo '<option value="' . $rol['id_rol'] . '">' . $rol['nombre'] . '</option>';
                  }
                  echo '</select>';

                  ?>
                </div>
              </div>

              <!-- select de la dependencia del usuario -->
              <div class="form-group row">
                <label for="inputDependencia" class="col-md-3 col-form-label">Dependencia</label>
                <div class="col-md-9">

                  <?php
                  $respuesta = ControladorDependencias::ctrMostrarDependencias();
                  // var_dump($respuesta);
                  echo '<select class="form-control" id="inputDependencia" name="nuevaDependencia">';
                  echo '<option value="">Seleccione</option>';
                  foreach ($respuesta as $dependencia) {
                    echo '<option value="' . $dependencia['id_dependencia'] . '">' . $dependencia['nombre'] . '</option>';
                  }
                  echo '</select>';
                  ?>


                </div>
              </div>
            </div>
            <div class="modal-footer justify-content-between">
              <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
              <button type="submit" class="btn btn-primary">Guardar</button>
            </div>

            <?php
              ControladorUsuarios::ctrCrearUsuario();
            ?>


          </form>

        </div>


      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>
  <!-- /.MODAL DE NUEVO USUARIO -->


  <!-- ***************************************************************************************************************************************** -->

  <!-- MODAL DE EDICION USUARIO -->
  <div class="modal fade" id="modal-editarUsuario">
    <div class="modal-dialog">
      <div class="modal-content">

        <div class="modal-header bg-primary">

          <h4 class="modal-title">Editar Usuario</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>

        </div>

        <div class="modal-body">
          <!-- <p>One fine body&hellip;</p> -->
          <form id="frmEditarUsuario" class="form-horizontal" method="post">
            <div class="card-body">

              <!-- input oculto con id del usuario -->
              <input type="hidden" id="idUsuario" name="idUsuario">

              <!-- select de tipo de documento y numero de identificacion -->
              <div class="form-group row">

                <div class="col-md-5">
                  <select class="form-control" id="editarTipoDocumento" name="editarTipoDocumento" disabled>
                    <option value="">Seleccione</option>
                    <option value="TI">Tarjeta de identidad</option>
                    <option value="CC">Cédula de ciudadanía</option>
                    <option value="CE">Cédula de extranjería</option>
                    <option value="PA">Pasaporte</option>
                  </select>
                </div>

                <div class="col-md-7">
                  <input type="text" class="form-control" id="editarNumeroIdentificacion" placeholder="Número de identificación" name="editarNumeroIdentificacion" readonly>
                </div>

              </div>

              <!-- input de nombre de usuario -->
              <div class="input-group mb-3">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-user"></i></span>
                </div>
                <input type="text" class="form-control" id="editarNombreUsuario" placeholder="Nombre completo" name="editarNombre">
              </div>

              <!-- input de correo electronico -->
              <div class="input-group mb-3">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                </div>
                <input type="email" class="form-control" id="editarCorreo" placeholder="Email" name="editarCorreo">
              </div>

              <!-- input de direccion -->
              <div class="input-group mb-3">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                </div>
                <input type="text" class="form-control" placeholder="Dirección" id="editarDireccion" name="editarDireccion">
              </div>


              <!-- input de telefono -->
              <div class="input-group mb-3">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-phone"></i></span>
                </div>
                <input type="text" class="form-control" placeholder="Teléfono" id="editarTelefono" name="editarTelefono">
              </div>


              <!-- select del rol del usuario -->
              <div class="form-group row">
                <label for="editarRol" class="col-sm-2 col-form-label">Rol</label>
                <div class="col-sm-10">

                  <?php
                  $respuesta = ControladorRoles::ctrMostrarRoles();
                  // var_dump($respuesta);
                  echo '<select class="form-control" id="editarRol" name="editarRol">';
                  echo '<option value="">Seleccione</option>';
                  foreach ($respuesta as $rol) {
                    echo '<option value="' . $rol['id_rol'] . '">' . $rol['nombre'] . '</option>';
                  }
                  echo '</select>';

                  ?>
                </div>
              </div>

              <!-- select de la dependencia del usuario -->
              <div class="form-group row">
                <label for="editarDependencia" class="col-md-3 col-form-label">Dependencia</label>
                <div class="col-md-9">

                  <?php
                  $respuesta = ControladorDependencias::ctrMostrarDependencias();
                  // var_dump($respuesta);
                  echo '<select class="form-control" id="editarDependencia" name="editarDependencia">';
                  echo '<option value="">Seleccione</option>';
                  foreach ($respuesta as $dependencia) {
                    echo '<option value="' . $dependencia['id_dependencia'] . '">' . $dependencia['nombre'] . '</option>';
                  }
                  echo '</select>';
                  ?>


                </div>
              </div>
            </div>
            <div class="modal-footer justify-content-between">
              <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
              <button type="submit" class="btn btn-primary">Modificar</button>
            </div>

            <?php
              ControladorUsuarios::ctrEditarUsuario();
            ?>


          </form>

        </div>


      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>
  <!-- /.MODAL DE NUEVO USUARIO -->