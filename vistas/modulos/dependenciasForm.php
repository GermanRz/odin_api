

  <!-- Content Header -->
  <section class="content-header">
    <div class="container-fluid">

      <div class="row mb-2">

        <div class="col-sm-6">
          <h1>Edita o crea dependencias</h1>
          <p>Formulario para editar o crear dependencias</p>
        </div>


        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item">
              <a href="dependencias">
                <button type="button" class="btn btn-block bg-gradient-secondary">
                  Volver
                </button>
              </a>
            </li>
          </ol>
        </div>

      </div>

    </div>
  </section>



  <!-- Main content -->
  <section class="content">

    <div class="container-fluid">


      <!-- general form elements -->
      <div class="card card-primary">

        <div class="card-header">
          <h3 class="card-title">
            Información de la dependencia
          </h3>
        </div>


        <!-- form start -->
        <form method="POST" action="dependencias">

          <div class="card-body">


            <div class="form-group">
              <label>
                Nombre
              </label>

              <input 
                type="text" 
                class="form-control" 
                placeholder="Ingrese el nombre de la dependencia">
            </div>



            <div class="form-group">

              <label>
                Dependencia (código / sigla)
              </label>

              <input 
                type="text" 
                class="form-control" 
                placeholder="Ejemplo: RH">

            </div>



            <div class="form-group">

              <label>
                Descripción
              </label>

              <textarea 
                class="form-control" 
                rows="3"
                placeholder="Ingrese la descripción">
              </textarea>

            </div>



            <div class="form-group">

              <label>
                Estado
              </label>

              <select class="form-control select2" style="width: 100%;">

                <option selected>
                  Seleccione
                </option>

                <option>
                  Activo
                </option>

                <option>
                  Inactivo
                </option>

              </select>

            </div>


          </div>
          <!-- /.card-body -->



          <div class="card-footer">

            <button 
              type="submit" 
              class="btn btn-primary">

              <i class="fas fa-save"></i>
              Guardar Cambios

            </button>


            <a href="dependencias" 
               class="btn btn-danger">

              Cancelar

            </a>


          </div>


        </form>


      </div>
      <!-- /.card -->


    </div>

  </section>

