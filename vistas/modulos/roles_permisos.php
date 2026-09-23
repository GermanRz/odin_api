


    <!-- Content Header (Page header) -->
    <section class="content-header">

        <div class="container-fluid">

            <div class="row mb-2">


                <div class="col-sm-6">

                    <h1>Gestión de roles</h1>

                    <p class="text-muted mb-0">
                        Administre y controle los roles registrados en el sistema.
                    </p>

                </div>



                <div class="col-sm-6">

                    <ol class="breadcrumb float-sm-right">

                        <li class="breadcrumb-item">

                            <a href="roles_permisosForm" class="btn btn-primary">

                                <i class="fas fa-plus"></i>
                                Nuevo rol

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


            <div class="card card-primary card-outline shadow">



                <div class="card-header">


                    <h3 class="card-title">

                        <i class="fas fa-user-shield mr-2"></i>

                        Gestión de Roles

                    </h3>


                </div>





                <div class="card-body">


                    <table id="example1" 
                           class="table table-bordered table-hover table-striped">



                        <thead class="bg-primary">


                            <tr>

                                <th width="80">
                                    ID
                                </th>


                                <th>
                                    Nombre
                                </th>


                                <th width="200">
                                    Tipo de Rol
                                </th>


                                <th width="140" class="text-center">
                                    Estado
                                </th>


                                <th width="250" class="text-center">
                                    Acciones
                                </th>


                            </tr>


                        </thead>





                        <tbody>



                            <tr>



                                <td>

                                    <span class="badge badge-secondary">
                                        1
                                    </span>

                                </td>



                                <td>
                                    Administrador
                                </td>




                                <td>

                                    <span class="badge badge-info">
                                        Administrador del sistema
                                    </span>

                                </td>




                                <td class="text-center">


                                    <span class="badge badge-success">

                                        Activo

                                    </span>


                                </td>




                                <td class="text-center">



                                    <!-- Editar -->

                                    <a href="roles_permisosForm?id=1"
                                       class="btn btn-info btn-sm"
                                       title="Editar">

                                        <i class="fas fa-edit"></i>

                                    </a>




                                    <!-- Activar -->

                                    <button class="btn btn-success btn-sm"
                                            title="Activar">

                                        <i class="fas fa-check"></i>

                                    </button>





                                    <!-- Desactivar -->

                                    <button class="btn btn-warning btn-sm"
                                            title="Desactivar">

                                        <i class="fas fa-power-off"></i>

                                    </button>





                                    <!-- Eliminar -->

                                    <button class="btn btn-danger btn-sm"
                                            title="Eliminar">

                                        <i class="fas fa-trash"></i>

                                    </button>



                                </td>



                            </tr>







                            <tr>



                                <td>

                                    <span class="badge badge-secondary">
                                        2
                                    </span>

                                </td>




                                <td>
                                    Usuario
                                </td>




                                <td>

                                    <span class="badge badge-info">
                                        Usuario básico
                                    </span>

                                </td>




                                <td class="text-center">


                                    <span class="badge badge-danger">

                                        Inactivo

                                    </span>


                                </td>




                                <td class="text-center">




                                    <!-- Editar -->

                                    <a href="roles_permisosForm?id=2"
                                       class="btn btn-info btn-sm"
                                       title="Editar">

                                        <i class="fas fa-edit"></i>

                                    </a>





                                    <!-- Activar -->

                                    <button class="btn btn-success btn-sm"
                                            title="Activar">

                                        <i class="fas fa-check"></i>

                                    </button>





                                    <!-- Desactivar -->

                                    <button class="btn btn-warning btn-sm"
                                            title="Desactivar">

                                        <i class="fas fa-power-off"></i>

                                    </button>





                                    <!-- Eliminar -->

                                    <button class="btn btn-danger btn-sm"
                                            title="Eliminar">

                                        <i class="fas fa-trash"></i>

                                    </button>




                                </td>



                            </tr>



                        </tbody>






                        <tfoot>


                            <tr>


                                <th>ID</th>

                                <th>Nombre</th>

                                <th>Tipo de Rol</th>

                                <th class="text-center">
                                    Estado
                                </th>

                                <th class="text-center">
                                    Acciones
                                </th>


                            </tr>


                        </tfoot>




                    </table>


                </div>



            </div>


        </div>



    </section>
    <!-- /.content -->


