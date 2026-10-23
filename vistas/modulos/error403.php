<!-- Content Header (Page header) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1><i class="fas fa-user-lock text-danger mr-2"></i> 403 Acceso Denegado</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
          <li class="breadcrumb-item active">403 Acceso Denegado</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<!-- Main content -->
<section class="content">
  <div class="error-page">
    <h2 class="headline text-danger">403</h2>

    <div class="error-content">
      <h3><i class="fas fa-exclamation-triangle text-danger mr-2"></i> No tiene permisos para acceder a este módulo.</h3>

      <p>
        Su rol actual (<strong><?php echo isset($_SESSION["nombre_rol"]) ? htmlspecialchars($_SESSION["nombre_rol"]) : "Usuario"; ?></strong>)
        no cuenta con el permiso granular requerido en la matriz RBAC (<strong>RF-N13 / RF-N14</strong>) para visualizar o ejecutar esta sección.
      </p>

      <div class="mt-3">
        <a href="inicio" class="btn btn-primary">
          <i class="fas fa-home mr-1"></i> Volver al Inicio
        </a>
      </div>
    </div>
  </div>
</section>
