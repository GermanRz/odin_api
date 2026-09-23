---
name: odin-development-guidelines
description: >-
  Normas técnicas, arquitectónicas y de seguridad de desarrollo para el Sistema ODIN (ADSO).
  Activa este skill obligatoriamente siempre que se vaya a crear, modificar, auditar o refactorizar código
  (PHP, SQL, JavaScript, HTML) para el proyecto ODIN, garantizando una arquitectura MVC desacoplada
  mediante API RESTful nativa, PDO seguro, estilos oficiales AdminLTE 3 en vistas, DataTables con AJAX y SweetAlert2.
---

# Skill: Lineamientos Oficiales de Desarrollo de Software — Proyecto ODIN

Este skill define las **reglas de ingeniería, arquitectura, seguridad, diseño de interfaz y estilo de código de obligado cumplimiento** para el desarrollo del **Sistema de Seguimiento y Gestión Documental ODIN**.

El agente y cualquier desarrollador deben aplicar estas directrices con tolerancia cero a desviaciones arquitectónicas.

---

## 1. REGLAS DE ORO INVIOLABLES (TOLERANCIA CERO)

1. **PROHIBICIÓN ABSOLUTA DE CONCATENACIÓN SQL (SQLi):**
   - Jamás interpolar ni concatenar variables en sentencias SQL (`"WHERE id = " . $id`).
   - Usar **exclusivamente** sentencias preparadas de PDO con `bindParam` fuertemente tipificado:
     ```php
     $stmt = Conexion::conectar()->prepare("SELECT * FROM usuarios WHERE id_usuario = :id");
     $stmt->bindParam(":id", $id, PDO::PARAM_INT);
     $stmt->execute();
     ```

2. **PROHIBICIÓN DE HTML O ALERTAS JS EN EL BACKEND:**
   - La API REST (controladores y modelos) **nunca** debe emitir código HTML ni scripts `<script>Swal.fire(...)</script>`.
   - Los controladores responden **únicamente** con cabecera `Content-Type: application/json; charset=UTF-8`, códigos HTTP (`http_response_code()`) y objetos JSON emitidos con `RespuestaHelper::exito()` o `RespuestaHelper::error()`.

3. **VISTAS 100% LIMPIAS (CERO PHP DE LÓGICA EN VISTAS):**
   - Queda terminantemente prohibido invocar métodos de controladores (`ControladorUsuarios::ctr...`) o ejecutar consultas SQL dentro de los archivos de vista (`vistas/modulos/*.php`).
   - Las vistas solo contienen estructura HTML5 / AdminLTE 3, tablas vacías (para DataTables) y modales de formularios.
   - La transferencia de datos es **100% asíncrona** vía JavaScript (`fetch` / `$.ajax`) consumiendo la API REST.

4. **ESTILOS OFICIALES ADMINLTE 3 EN TODAS LAS VISTAS:**
   - Toda interfaz debe emplear rigurosamente los componentes y estilos visuales de AdminLTE 3 y plugins integrados alojados en `odin_api/vistas/`:
     - CSS Principal: `vistas/dist/css/adminlte.min.css`.
     - Iconos: `vistas/plugins/fontawesome-free/css/all.min.css` (clases `fas fa-*`).
     - DataTables: `vistas/plugins/datatables-bs4/` y `vistas/plugins/datatables-responsive/`.
     - Modales y Formularios: Grid y componentes Bootstrap 4 (`card card-outline`, `modal-header bg-primary`, etc.).

5. **CONTRASEÑAS SEGURAS:**
   - Guardar contraseñas únicamente con `password_hash($password, PASSWORD_DEFAULT)`. Prohibido MD5, SHA1 o texto plano.
   - Validar credenciales únicamente con `password_verify($passwordPlano, $hashBD)`.

6. **PROHIBICIÓN DE FRAMEWORKS GLOBALES:**
   - No usar Laravel, Symfony, CodeIgniter, etc.
   - El backend debe construirse en **PHP 8.x Nativo Orientado a Objetos** con arquitectura limpia y desacoplada.

---

## 2. ESTÁNDAR DE INTERFAZ Y ESTILOS ADMINLTE 3 (`odin_api/vistas/`)

Todo módulo de vista en `vistas/modulos/[modulo].php` debe respetar estrictamente el sistema de diseño visual de AdminLTE 3 presente en el proyecto:

### 2.1. Recursos y Assets de AdminLTE en el Proyecto
- **Estilos base:** `vistas/dist/css/adminlte.min.css`
- **Iconografía oficial:** FontAwesome 5 (`<i class="fas fa-[icono]"></i>`) en `vistas/plugins/fontawesome-free/`
- **Tablas:** DataTables con tema Bootstrap 4 (`vistas/plugins/datatables-bs4/`)
- **Alertas interactivas:** SweetAlert2 con tema Bootstrap 4 (`vistas/plugins/sweetalert2-theme-bootstrap-4/`)
- **Scripts:** `vistas/plugins/jquery/`, `vistas/plugins/bootstrap/js/bootstrap.bundle.min.js`, `vistas/dist/js/adminlte.min.js`

### 2.2. Anatomía Canónica de una Vista con AdminLTE 3
Toda vista de módulo debe estructurarse en dos secciones obligatorias (`content-header` y `content`), junto con sus modales correspondientes:

```html
<!-- 1. Encabezado de Página (Breadcrumbs y Título) -->
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1><i class="fas fa-folder-open mr-2"></i> Gestión de [Nombre del Módulo]</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="inicio">Inicio</a></li>
          <li class="breadcrumb-item active">[Nombre del Módulo]</li>
        </ol>
      </div>
    </div>
  </div>
</section>

<!-- 2. Contenido Principal con Card AdminLTE y Tabla -->
<section class="content">
  <div class="container-fluid">
    
    <div class="card card-primary card-outline">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list mr-1"></i> Listado de Registros</h3>
        <div class="card-tools ml-auto">
          <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal-agregarRegistro">
            <i class="fas fa-plus mr-1"></i> Agregar Registro
          </button>
        </div>
      </div>
      
      <div class="card-body">
        <table id="tbl[Modulo]" class="table table-bordered table-striped dt-responsive nowrap" style="width:100%">
          <thead>
            <tr>
              <th>ID</th>
              <th>Campo 1</th>
              <th>Campo 2</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <!-- El contenido se inyecta dinámicamente vía AJAX desde vistas/js/[modulo].js -->
          </tbody>
        </table>
      </div>
    </div>

  </div>
</section>

<!-- 3. Modales AdminLTE / Bootstrap 4 -->
<div class="modal fade" id="modal-agregarRegistro" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-plus-circle mr-1"></i> Nuevo Registro</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="frmAgregarRegistro">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 form-group">
              <label for="nuevoCampo1">Campo 1 (*)</label>
              <input type="text" class="form-control" id="nuevoCampo1" placeholder="Ingrese el dato" required>
            </div>
            <div class="col-md-6 form-group">
              <label for="nuevoCampo2">Campo 2 (*)</label>
              <select class="form-control" id="nuevoCampo2" required>
                <option value="">Seleccione una opción</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- 4. Vinculación del script del módulo -->
<script src="vistas/js/[modulo].js"></script>
```

---

## 3. MAPA DE RESPONSABILIDADES Y TOPOLOGÍA

```
odin_api/
├── api/                          # Núcleo API RESTful (Backend)
│   ├── index.php                 # Router semántico único (GET, POST, PUT, PATCH, DELETE)
│   └── helpers/
│       └── respuesta.helper.php  # Formato JSON estándar y http_response_code()
├── controladores/                # Capa de Controladores REST (Recibe JSON, valida, llama Modelo)
│   └── [modulo].controlador.php
├── modelos/                      # Capa de Persistencia PDO (Sentencias SQL preparadas)
│   ├── conexion.php              # Singleton PDO centralizado
│   └── [modulo].modelo.php
├── servicios/                    # Servicios desacoplados (Archivos, Correos SMTP)
│   ├── archivo.servicio.php
│   └── correo.servicio.php
├── vistas/                       # Capa de Presentación (Frontend Desacoplado)
│   ├── dist/                     # Assets compilados de AdminLTE 3 (css/adminlte.min.css, js/adminlte.min.js)
│   ├── plugins/                  # Plugins de terceros (bootstrap, fontawesome, datatables, sweetalert2)
│   ├── modulos/                  # Vistas HTML puras con AdminLTE 3 (Sin PHP de negocio)
│   │   └── [modulo].php
│   └── js/                       # Lógica Cliente (DataTables AJAX, Fetch, SweetAlert2)
│       └── [modulo].js
```

---

## 4. ESTÁNDAR RESTful Y CONVENCIONES DE CÓDIGO

### 4.1. Matriz de Endpoints y Verbos HTTP
| Operación | Verbo | Endpoint | Payload | Código Éxito |
| :--- | :--- | :--- | :--- | :--- |
| **Listar** | `GET` | `/api/[recurso]` | N/A | `200 OK` |
| **Consultar uno** | `GET` | `/api/[recurso]/{id}` | N/A | `200 OK` |
| **Crear** | `POST` | `/api/[recurso]` | JSON Body | `201 Created` |
| **Actualizar** | `PUT` | `/api/[recurso]/{id}` | JSON Body | `200 OK` |
| **Cambiar Estado**| `PATCH` | `/api/[recurso]/{id}/estado`| `{"estado": "..."}` | `200 OK` |
| **Eliminar** | `DELETE` | `/api/[recurso]/{id}` | N/A | `200 OK` |

### 4.2. Formato Unificado de Respuesta JSON
```php
// Éxito:
RespuestaHelper::exito($data, "Mensaje descriptivo", 200); // o 201

// Error:
RespuestaHelper::error("Mensaje amigable", 400, $erroresOpcionales); // 400, 404, 500
```
Estructura emitida:
```json
{
  "status": 200,
  "success": true,
  "message": "Operación ejecutada correctamente",
  "data": []
}
```

### 4.3. Nomenclatura Estricta
- **Clases Controlador:** `Controlador[Nombre]` en `controladores/[nombre].controlador.php`.
- **Clases Modelo:** `Modelo[Nombre]` en `modelos/[nombre].modelo.php`.
- **Métodos Controlador:** `ctrListar()`, `ctrObtener($id)`, `ctrCrear($body)`, `ctrActualizar($id, $body)`, `ctrCambiarEstado($id, $estado)`.
- **Métodos Modelo:** `mdlListar[Entidad]()`, `mdlCrear[Entidad]($datos)`, etc., con prefijo `mdl...`.
- **Base de Datos:** Tablas en minúsculas y `snake_case` plural (`usuarios`, `radicados`). Claves primarias `id_[tabla_singular]`.
- **Payloads JSON y Scripts JS:** Variables y propiedades en `camelCase`.

---

## 5. PATRONES DE CÓDIGO CANÓNICOS (BOILERPLATE OBLIGATORIO)

### 5.1. Modelo PDO (`modelos/[modulo].modelo.php`)
```php
<?php
require_once "conexion.php";

class ModeloUsuarios {

    public static function mdlMostrarUsuarios(): array {
        $stmt = Conexion::conectar()->prepare("SELECT id_usuario, nombre, num_identificacion, estado FROM usuarios ORDER BY id_usuario DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function mdlCrearUsuario(array $datos): bool {
        $stmt = Conexion::conectar()->prepare("INSERT INTO usuarios (nombre, num_identificacion, correo, password, estado)
                                               VALUES (:nombre, :identificacion, :correo, :password, 'Activo')");
        $stmt->bindParam(":nombre",         $datos["nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":identificacion", $datos["identificacion"], PDO::PARAM_STR);
        $stmt->bindParam(":correo",         $datos["correo"], PDO::PARAM_STR);
        $stmt->bindParam(":password",       $datos["password"], PDO::PARAM_STR);
        return $stmt->execute();
    }
}
```

### 5.2. Controlador REST (`controladores/[modulo].controlador.php`)
```php
<?php
require_once __DIR__ . "/../modelos/usuarios.modelo.php";
require_once __DIR__ . "/../api/helpers/respuesta.helper.php";

class ControladorUsuarios {

    public static function ctrListar(): void {
        try {
            $usuarios = ModeloUsuarios::mdlMostrarUsuarios();
            RespuestaHelper::exito($usuarios, "Listado de usuarios");
        } catch (Exception $e) {
            error_log("Error en ctrListar: " . $e->getMessage());
            RespuestaHelper::error("Error al obtener la lista de usuarios", 500);
        }
    }

    public static function ctrCrear(array $body): void {
        if (empty($body["nombre"]) || empty($body["identificacion"]) || empty($body["correo"])) {
            RespuestaHelper::error("Campos obligatorios incompletos", 400);
        }

        $datos = [
            "nombre"         => trim($body["nombre"]),
            "identificacion" => trim($body["identificacion"]),
            "correo"         => trim(strtolower($body["correo"])),
            "password"       => password_hash($body["identificacion"], PASSWORD_DEFAULT)
        ];

        $resultado = ModeloUsuarios::mdlCrearUsuario($datos);
        if ($resultado) {
            RespuestaHelper::exito([], "Usuario creado exitosamente", 201);
        } else {
            RespuestaHelper::error("No fue posible registrar el usuario", 500);
        }
    }
}
```

### 5.3. Consumo Frontend con AdminLTE UI (`vistas/js/[modulo].js`)
```javascript
const API_URL = "api/usuarios";

$(document).ready(function() {
    // 1. DataTables con AJAX y componentes AdminLTE
    $("#tblUsuarios").DataTable({
        ajax: {
            url: API_URL,
            type: "GET",
            dataSrc: "data"
        },
        columns: [
            { data: "id_usuario" },
            { data: "nombre" },
            { data: "num_identificacion" },
            { 
                data: "estado",
                render: (data, type, row) => {
                    const btnClass = data === 'Activo' ? 'btn-success' : 'btn-danger';
                    const proxEstado = data === 'Activo' ? 'Inactivo' : 'Activo';
                    return `<button class="btn btn-xs ${btnClass} btnEstado" data-id="${row.id_usuario}" data-estado="${proxEstado}">${data}</button>`;
                }
            },
            {
                data: null,
                render: (data, type, row) => `
                    <button class="btn btn-warning btn-sm btnEditar" data-id="${row.id_usuario}">
                        <i class="fas fa-edit"></i>
                    </button>`
            }
        ],
        responsive: true
    });

    // 2. Interceptar formulario con Fetch y SweetAlert2
    $("#frmAgregarUsuario").on("submit", function(e) {
        e.preventDefault();
        const payload = {
            nombre: $("#nuevoNombre").val(),
            identificacion: $("#nuevoIdentificacion").val(),
            correo: $("#nuevoCorreo").val()
        };

        fetch(API_URL, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        })
        .then(r => r.json().then(b => ({ status: r.status, body: b })))
        .then(({ status, body }) => {
            if (status === 201 && body.success) {
                Swal.fire({
                    icon: "success",
                    title: "¡Éxito!",
                    text: body.message,
                    timer: 1800,
                    showConfirmButton: false
                });
                $("#modal-agregarUsuario").modal("hide");
                $("#frmAgregarUsuario")[0].reset();
                $("#tblUsuarios").DataTable().ajax.reload(null, false);
            } else {
                Swal.fire({ icon: "error", title: "Error", text: body.message });
            }
        })
        .catch(() => Swal.fire({ icon: "error", title: "Error de conexión", text: "No se pudo comunicar con el servidor." }));
    });
});
```

---

## 6. POLÍTICA DE ARCHIVOS DIGITALES Y NOTIFICACIONES

- **Archivos:** Renombrado inmutable preventivo obligatorio:  
  `[PREFIJO]_[RADICADO]_[YYYYMMDD]_[HASH/CONSECUTIVO].[ext]`.
- Almacenamiento en jerarquía mensual: `vistas/archivos/[tipo]/YYYY/MM/`.
- Verificación estricta de MIME real binario (`mime_content_type`) y directiva `.htaccess` anti-ejecución.
- En base de datos guardar **solo ruta relativa y metadatos**, nunca BLOBs.
- **Correos:** Desacoplados en `servicios/correo.servicio.php`. Fallos de SMTP se atrapan con `try/catch` y `error_log` sin cancelar transacciones de BD.

---

## 7. CHECKLIST DE AUTO-VALIDACIÓN PREVIA A ENTREGAR CÓDIGO
- [ ] ¿La vista aplica rigurosamente la estructura y clases de AdminLTE 3 (`content-header`, `content`, `card card-outline`, `btn`, `fas fa-*`)?
- [ ] ¿El archivo de vista carece al 100% de llamadas PHP a controladores o consultas SQL?
- [ ] ¿El controlador REST retorna únicamente JSON y códigos de estado HTTP semánticos?
- [ ] ¿El archivo del backend no contiene scripts `<script>Swal.fire` embebidos?
- [ ] ¿Toda consulta SQL utiliza sentencias preparadas de PDO con `bindParam` tipificado?
- [ ] ¿El formulario se envía asíncronamente con `preventDefault()` y `fetch` o `$.ajax`?
- [ ] ¿DataTables se alimenta mediante la opción `ajax: { url: "api/...", dataSrc: "data" }`?
