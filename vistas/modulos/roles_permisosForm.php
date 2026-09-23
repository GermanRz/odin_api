

    <!-- Content Header (Page header) -->
    <section class="content-header">

        <div class="container-fluid">

            <div class="row mb-2">


                <div class="col-sm-6">

                    <h1>Edita o crea roles</h1>

                    <p class="text-muted mb-0">
                        Formulario para administrar los roles del sistema.
                    </p>

                </div>




                <div class="col-sm-6">


                    <ol class="breadcrumb float-sm-right">


                        <li class="breadcrumb-item">

                            <a href="roles_permisos">

                                <button type="button" 
                                        class="btn btn-secondary">

                                    <i class="fas fa-arrow-left"></i>
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



            <!-- Card -->

            <div class="card card-primary card-outline shadow">



                <div class="card-header">


                    <h3 class="card-title">

                        <i class="fas fa-user-shield mr-2"></i>

                        Información del rol

                    </h3>


                </div>






                <!-- Form -->

                <form method="POST" action="roles_permisos">


                    <div class="card-body">





                        <!-- Nombre -->

                        <div class="form-group">


                            <label>
                                Nombre del rol
                            </label>


                            <input 
                                type="text"
                                class="form-control"
                                name="nombre"
                                placeholder="Ejemplo: Administrador"
                                required>


                        </div>







                        <!-- Tipo de rol -->

                        <div class="form-group">


                            <label>
                                Tipo de rol
                            </label>



                            <select 
                                class="form-control select2"
                                name="tipo"
                                style="width:100%;"
                                required>


                                <option selected disabled>
                                    Seleccione un tipo de rol
                                </option>


                                <option value="Administrador">
                                    Administrador
                                </option>


                                <option value="Usuario">
                                    Usuario
                                </option>


                                <option value="Supervisor">
                                    Supervisor
                                </option>


                                <option value="Invitado">
                                    Invitado
                                </option>



                            </select>


                        </div>








                        <!-- Descripción -->

                        <div class="form-group">


                            <label>
                                Descripción
                            </label>



                            <textarea
                                class="form-control"
                                name="descripcion"
                                rows="4"
                                placeholder="Ingrese una descripción del rol">
                            </textarea>


                        </div>








                        <!-- Estado -->

                        <div class="form-group">


                            <label>
                                Estado
                            </label>



                            <select 
                                class="form-control select2"
                                name="estado"
                                style="width:100%;">



                                <option selected disabled>
                                    Seleccione
                                </option>



                                <option value="Activo">
                                    Activo
                                </option>



                                <option value="Inactivo">
                                    Inactivo
                                </option>



                            </select>


                        </div>




                    </div>
                    <!-- /.card-body -->







                    <!-- Footer -->

                    <div class="card-footer">



                        <button 
                            type="submit"
                            class="btn btn-primary">


                            <i class="fas fa-save"></i>

                            Guardar Cambios


                        </button>





                        <a href="roles"
                           class="btn btn-danger">


                            <i class="fas fa-times"></i>

                            Cancelar


                        </a>



                    </div>




                </form>



            </div>



        </div>



    </section>
    <!-- /.content -->

