# Reglas y Lineamientos de Desarrollo del Proyecto ODIN

> **Norma Oficial de Desarrollo:** Este repositorio se rige estrictamente por el skill `odin-development-guidelines` y el documento maestro normativo [LINEAMIENTOS_Y_ESTANDARES_DESARROLLO.md](file:///d:/Trabajo%202026/ADSO/3140278/OdinDocs/LINEAMIENTOS_Y_ESTANDARES_DESARROLLO.md).

Toda tarea de generación, modificación, refactorización o auditoría de código en este repositorio debe respetar de forma obligatoria las siguientes directrices arquitectónicas:

---

## 1. Arquitectura MVC Desacoplada mediante API RESTful

- **Backend API RESTful (`api/`, `controladores/`, `modelos/`, `servicios/`):**
  - La API atiende peticiones HTTP y responde **exclusivamente en formato JSON** con cabecera `Content-Type: application/json; charset=UTF-8` y códigos de estado HTTP semánticos (`http_response_code(200, 201, 400, 401, 404, 500)`).
  - Las respuestas deben emitirse centralizadamente a través de `RespuestaHelper::exito()` o `RespuestaHelper::error()`.
  - **Prohibición absoluta:** Jamás emitir código HTML, volcados técnicos (`var_dump`) ni scripts `<script>Swal.fire(...)</script>` desde el backend.
  - Lectura de payloads JSON mediante `json_decode(file_get_contents("php://input"), true)`.
  - Autenticación desacoplada sin estado mediante cabecera `Authorization: Bearer <TOKEN>`.

- **Frontend Desacoplado (`vistas/modulos/`, `vistas/js/`):**
  - **Vistas 100% limpias:** Queda terminantemente prohibido invocar métodos de controladores (`ControladorUsuarios::ctr...`) o ejecutar consultas SQL dentro de las vistas (`vistas/modulos/*.php`).
  - Toda interacción de datos se realiza de forma asíncrona mediante JavaScript (`fetch` / `$.ajax`) consumiendo los endpoints de la API REST.

---

## 2. Estándar de Interfaz y Estilos AdminLTE 3 (`odin_api/vistas/`)

Toda interfaz gráfica debe utilizar los recursos, estilos y componentes de AdminLTE 3 y plugins integrados en la carpeta `vistas/`:

- **Assets y Estilos Principales:**
  - Estilos base: `vistas/dist/css/adminlte.min.css`.
  - Iconografía oficial: FontAwesome 5 (`<i class="fas fa-*"></i>`) desde `vistas/plugins/fontawesome-free/`.
  - Tablas: DataTables con estilos Bootstrap 4 en `vistas/plugins/datatables-bs4/`.
  - Alertas visuales: SweetAlert2 con tema Bootstrap 4 en `vistas/plugins/sweetalert2-theme-bootstrap-4/`.
  - Scripts base: `vistas/plugins/jquery/`, `vistas/plugins/bootstrap/js/bootstrap.bundle.min.js`, `vistas/dist/js/adminlte.min.js`.

- **Estructura Obligatoria de Vistas (`vistas/modulos/[modulo].php`):**
  1. `<section class="content-header">`: Título del módulo `<h1>` con icono FontAwesome y migas de pan `<ol class="breadcrumb float-sm-right">`.
  2. `<section class="content">`: Contenedor `.container-fluid` con tarjeta `.card.card-primary.card-outline`.
  3. Cabecera `.card-header`: Título `.card-title` y herramientas `.card-tools.ml-auto` con botones de acción (`btn btn-success`, etc.).
  4. Tabla `.card-body`: `<table id="tbl[Modulo]" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%">` con `<tbody>` vacío para renderizado AJAX.
  5. Modales Bootstrap 4: Cabecera `.modal-header.bg-primary.text-white`, botón de cierre `.close.text-white`, formulario con grid `.row > .col-md-6.form-group` y pie `.modal-footer.justify-content-between`.
  6. Script del Módulo: Inclusión obligatoria al pie `<script src="vistas/js/[modulo].js"></script>`.

---

## 3. Interactividad y Experiencia de Usuario (UI/UX)

- **DataTables:** Deben inicializarse en modo AJAX consumiendo el endpoint REST (`ajax: { url: "api/[recurso]", dataSrc: "data" }`) con opciones responsive activadas.
- **Formularios:** Deben interceptar el evento `submit` con `e.preventDefault()`, enviar el payload en JSON o `FormData` vía `POST` / `PUT` a la API, y gestionar notificaciones interactivas con SweetAlert2 (`Swal.fire`) **exclusivamente en JavaScript**.
- **Cambios de Estado y Acciones Atómicas:** Deben realizarse mediante peticiones asíncronas (`PATCH /api/[recurso]/{id}/estado`), refrescando únicamente la tabla con `DataTable.ajax.reload(null, false)` sin recargar la página.

---

## 4. Persistencia Segura y Base de Datos (MySQL con PDO)

- **Prohibición Absoluta de SQL Injection (SQLi):** Jamás concatenar variables en sentencias SQL. Toda consulta parametrizada debe usar sentencias preparadas de PDO (`prepare`) y `bindParam` con su constante tipificada (`PDO::PARAM_INT`, `PDO::PARAM_STR`).
- **Conexión Centralizada:** Únicamente a través del método estático `Conexion::conectar()` (`modelos/conexion.php`).
- **Contraseñas Seguras:** Almacenadas con `password_hash($pass, PASSWORD_DEFAULT)` y validadas con `password_verify()`. Prohibido texto plano, MD5 o SHA1.
- **Validación de Entradas:** Filtrar y sanitizar datos con `filter_var` y expresiones regulares (`preg_match`) en el controlador antes de invocar la persistencia.
- **Manejo Seguro de Errores:** Capturar fallos con `try/catch` y registrar en `error_log()`. Jamás exponer volcados o excepciones PDO en respuestas al cliente.

---

## 5. Gestión Documental, Archivos y Servicios Transversales

- **Renombrado Preventivo Operativo:** Prohibido guardar archivos con el nombre original del cliente. Aplicar convención:  
  `[PREFIJO]_[RADICADO]_[YYYYMMDD]_[HASH/CONSECUTIVO].[ext]`.
- **Estructura Particionada:** Almacenamiento organizado en `vistas/archivos/[tipo]/YYYY/MM/`.
- **Filtros de Seguridad:** Validación estricta de extensiones permitidas, verificación del tipo MIME binario real (`mime_content_type`), límites de peso y protección con `.htaccess` anti-ejecución (`php_flag engine off`).
- **Persistencia en BD:** Almacenar únicamente rutas relativas canónicas y metadatos documentales. Prohibido almacenar BLOBs en MySQL.
- **Notificaciones por Correo:** Desacopladas en `servicios/correo.servicio.php`. Fallos de envío SMTP deben capturarse con `try/catch` para no revertir transacciones exitosas en la base de datos.

---

## 6. Nomenclatura Estricta y Convenciones

- **Controladores REST:** `Controlador[Nombre]` en `controladores/[nombre].controlador.php` con métodos semánticos (`ctrListar`, `ctrObtener($id)`, `ctrCrear($body)`, `ctrActualizar($id, $body)`, `ctrCambiarEstado($id, $estado)`).
- **Modelos:** `Modelo[Nombre]` en `modelos/[nombre].modelo.php` con métodos `mdl...` retornando arrays asociativos o booleanos.
- **Endpoints de la API:** Sustantivos en plural (`/api/usuarios`, `/api/radicados`) y verbos semánticos (`GET`, `POST`, `PUT`, `PATCH`, `DELETE`).
- **Base de Datos:** Tablas en minúsculas y `snake_case` plural (`usuarios`, `radicados`), claves primarias `id_[tabla_singular]`.
- **Scripts Frontend:** Archivos modulares `vistas/js/[modulo].js` con variables y funciones en `camelCase`.
