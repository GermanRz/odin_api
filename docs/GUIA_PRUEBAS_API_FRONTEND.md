# Guía de Integración y Pruebas de la API RESTful — Módulo de Usuarios
## Sistema de Seguimiento y Gestión Documental — Proyecto ODIN

**Dirigido a:** Equipo de Desarrollo Frontend  
**Entorno Tecnológico:** PHP 8.x Nativo (API RESTful) | JavaScript ES6+ / Fetch / AJAX | AdminLTE 3 / Bootstrap 4 | SweetAlert2 | DataTables  
**Versión de la API:** 1.0.0  
**Fecha de Emisión:** 25 de Septiembre de 2026  

---

## 1. Información General y Entorno de Conexión

### 1.1. URLs Base
| Entorno | URL Base | Protocolo |
| :--- | :--- | :--- |
| **Desarrollo Local (XAMPP Estándar)** | `http://localhost/odin_api/api` | HTTP |
| **Desarrollo Local (Puerto Alterno)** | `http://localhost:8080/odin_api/api` | HTTP |

### 1.2. Cabeceras Obligatorias (Headers)
Para todas las peticiones con envío de datos (`POST`, `PUT`, `PATCH`):
```http
Content-Type: application/json; charset=UTF-8
Accept: application/json
```

> [!NOTE]
> La API tiene habilitado soporte completo para **CORS** (`Access-Control-Allow-Origin: *`, `GET`, `POST`, `PUT`, `PATCH`, `DELETE`, `OPTIONS`).

---

## 2. Estructura Unificada de Respuestas

Todas las respuestas emitidas por la API siguen estrictamente el estándar unificado ODIN:

### 2.1. Respuesta Exitosa (`200 OK` / `201 Created`)
```json
{
  "status": 200,
  "success": true,
  "message": "Operación ejecutada correctamente",
  "data": []
}
```

### 2.2. Respuesta de Error (`400`, `404`, `409`, `500`)
```json
{
  "status": 400,
  "success": false,
  "message": "Mensaje descriptivo del error",
  "errors": [
    "Descripción detallada del error (opcional)"
  ]
}
```

---

## 3. Catálogo Completo de Endpoints

### 3.1. `GET /api/usuarios` — Listar Todos los Usuarios
Consulta la colección completa de usuarios con sus relaciones (rol y dependencia). Diseñado para consumirse directamente desde DataTables.

* **Método:** `GET`
* **URL:** `/api/usuarios`
* **Payload:** Ninguno
* **Respuesta Exitosa (`200 OK`):**
```json
{
  "status": 200,
  "success": true,
  "message": "Listado de usuarios obtenido correctamente",
  "data": [
    {
      "id_usuario": 1,
      "nombre": "German Ramirez",
      "num_identificacion": "123",
      "correo": "germaneramirez@gmail.com",
      "rol": "Administrador del Sistema",
      "dependencia": "Dirección General",
      "estado": "Activo"
    }
  ]
}
```

#### Ejemplo de Integración en DataTables (JavaScript):
```javascript
$("#tblUsuarios").DataTable({
    ajax: {
        url: "api/usuarios",
        type: "GET",
        dataSrc: "data"
    },
    columns: [
        { data: "id_usuario" },
        { data: "nombre" },
        { data: "num_identificacion" },
        { data: "correo" },
        { data: "rol", defaultContent: "Sin rol" },
        { data: "dependencia", defaultContent: "Sin dependencia" },
        { 
            data: "estado",
            render: (data, type, row) => {
                const btnClass = data === "Activo" ? "btn-success" : "btn-danger";
                const nuevoEstado = data === "Activo" ? "Inactivo" : "Activo";
                return `<button class="btn btn-xs ${btnClass} btnCambiarEstado" data-id="${row.id_usuario}" data-estado="${nuevoEstado}">${data}</button>`;
            }
        },
        {
            data: null,
            render: (data, type, row) => `
                <button class="btn btn-warning btn-sm btnCargarEditar" data-id="${row.id_usuario}">
                    <i class="fas fa-edit"></i>
                </button>`
        }
    ],
    responsive: true
});
```

---

### 3.2. `GET /api/usuarios/stats` — Métricas y Estadísticas
Devuelve el conteo general de usuarios para poblar tarjetas (widgets) informativas en la interfaz.

* **Método:** `GET`
* **URL:** `/api/usuarios/stats`
* **Payload:** Ninguno
* **Respuesta Exitosa (`200 OK`):**
```json
{
  "status": 200,
  "success": true,
  "message": "Estadísticas de usuarios obtenidas correctamente",
  "data": {
    "total": 7,
    "activos": 6,
    "inactivos": 1
  }
}
```

#### Ejemplo de Consumo con `fetch`:
```javascript
fetch("api/usuarios/stats")
    .then(res => res.json())
    .then(res => {
        if (res.success && res.data) {
            $("#totalUsuarios").text(res.data.total);
            $("#totalUsuariosActivos").text(res.data.activos);
            $("#totalUsuariosInactivos").text(res.data.inactivos);
        }
    });
```

---

### 3.3. `GET /api/usuarios/{id}` — Consultar Detalle de un Usuario
Obtiene la información individual de un usuario por su clave primaria `id_usuario`. Por seguridad, el campo `password` nunca se incluye en la respuesta.

* **Método:** `GET`
* **URL:** `/api/usuarios/1` (donde `1` es el ID numérico)
* **Payload:** Ninguno
* **Respuesta Exitosa (`200 OK`):**
```json
{
  "status": 200,
  "success": true,
  "message": "Usuario encontrado",
  "data": {
    "id_usuario": 1,
    "tipo_identificacion": "CC",
    "num_identificacion": "123",
    "nombre": "German Ramirez",
    "correo": "germaneramirez@gmail.com",
    "id_rol": 1,
    "id_dependencia": 1,
    "estado": "Activo",
    "direccion": "Calle Principal 123",
    "telefono": "3001234567"
  }
}
```
* **Respuesta Error (`404 Not Found`):**
```json
{
  "status": 404,
  "success": false,
  "message": "Usuario no encontrado"
}
```

---

### 3.4. `POST /api/usuarios` — Registrar Nuevo Usuario
Crea un usuario en el sistema. La contraseña se asigna automáticamente cifrada con el número de identificación.

* **Método:** `POST`
* **URL:** `/api/usuarios`
* **Headers:** `Content-Type: application/json`
* **Estructura del Payload JSON:**
```json
{
  "tipoIdentificacion": "CC",
  "numeroIdentificacion": "1002345678",
  "nombre": "Carlos Alberto Gómez",
  "correo": "carlos.gomez@entidad.gov.co",
  "idRol": 2,
  "idDependencia": 1,
  "direccion": "Calle 45 # 12-34",
  "telefono": "3109876543"
}
```

#### Diccionario de Datos del Payload:
| Campo | Tipo | Requerido | Descripción / Opciones |
| :--- | :--- | :---: | :--- |
| `tipoIdentificacion` | String | Sí | `"CC"`, `"TI"`, `"CE"`, `"PA"` |
| `numeroIdentificacion` | String | Sí | Documento único sin puntos ni comas |
| `nombre` | String | Sí | Nombre completo del usuario |
| `correo` | String | Sí | Correo electrónico con formato válido |
| `idRol` | Entero | Sí | ID del rol (`1`, `2`, etc.) |
| `idDependencia` | Entero | Sí | ID de la dependencia asignada |
| `direccion` | String | No | Dirección física de correspondencia |
| `telefono` | String | No | Teléfono de contacto |

* **Respuesta Exitosa (`201 Created`):**
```json
{
  "status": 201,
  "success": true,
  "message": "Usuario creado exitosamente",
  "data": []
}
```

* **Errores de Validación Comunes:**
  - `400 Bad Request`: `"Los campos Nombre, Identificación y Correo electrónico son obligatorios"`
  - `400 Bad Request`: `"El correo electrónico no posee un formato válido"`
  - `409 Conflict`: `"El número de identificación ya se encuentra registrado en el sistema"`
  - `409 Conflict`: `"El correo electrónico ya se encuentra registrado para otro usuario"`

#### Ejemplo de Envío en Formulario con SweetAlert2:
```javascript
$("#frmAgregarUsuario").on("submit", function(e) {
    e.preventDefault();

    const payload = {
        tipoIdentificacion:   $("#nuevoTipoDocumento").val(),
        numeroIdentificacion: $("#nuevoNumeroIdentificacion").val(),
        nombre:               $("#nuevoNombre").val(),
        correo:               $("#nuevoCorreo").val(),
        idRol:                parseInt($("#nuevoRol").val()),
        idDependencia:        parseInt($("#nuevaDependencia").val()),
        direccion:            $("#nuevaDireccion").val(),
        telefono:             $("#nuevoTelefono").val()
    };

    fetch("api/usuarios", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
    })
    .then(res => res.json().then(data => ({ status: res.status, body: data })))
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
            Swal.fire({ icon: "error", title: "Atención", text: body.message });
        }
    })
    .catch(() => Swal.fire({ icon: "error", title: "Error de red", text: "No se pudo conectar con el servidor." }));
});
```

---

### 3.5. `PUT /api/usuarios/{id}` — Actualizar Información de Usuario
Actualiza los datos modificables de un usuario existente.

* **Método:** `PUT`
* **URL:** `/api/usuarios/1` (donde `1` es el ID numérico)
* **Headers:** `Content-Type: application/json`
* **Payload JSON:**
```json
{
  "nombre": "German Ramirez Actualizado",
  "correo": "germaneramirez@gmail.com",
  "idRol": 1,
  "idDependencia": 1,
  "direccion": "Avenida 10 # 20-30",
  "telefono": "3001234567"
}
```

* **Respuesta Exitosa (`200 OK`):**
```json
{
  "status": 200,
  "success": true,
  "message": "Usuario actualizado correctamente",
  "data": []
}
```

* **Errores Comunes:**
  - `400 Bad Request`: `"El nombre y el correo electrónico son obligatorios para actualizar"`
  - `404 Not Found`: `"El usuario a actualizar no existe"`
  - `409 Conflict`: `"El correo electrónico ya está en uso por otro usuario"`

---

### 3.6. `PATCH /api/usuarios/{id}/estado` — Modificación Atómica de Estado
Activa o desactiva un usuario de manera instantánea sin necesidad de recargar la página completa.

* **Método:** `PATCH`
* **URL:** `/api/usuarios/1/estado`
* **Headers:** `Content-Type: application/json`
* **Payload JSON:**
```json
{
  "estado": "Inactivo"
}
```
*(Valores admitidos únicamente: `"Activo"` o `"Inactivo"`)*

* **Respuesta Exitosa (`200 OK`):**
```json
{
  "status": 200,
  "success": true,
  "message": "Estado del usuario actualizado correctamente",
  "data": {
    "nuevoEstado": "Inactivo"
  }
}
```

#### Ejemplo de Implementación en Botón de Estado:
```javascript
$(document).on("click", ".btnCambiarEstado", function() {
    const idUsuario = $(this).data("id");
    const nuevoEstado = $(this).data("estado");

    fetch(`api/usuarios/${idUsuario}/estado`, {
        method: "PATCH",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ estado: nuevoEstado })
    })
    .then(res => res.json())
    .then(res => {
        if (res.success) {
            $("#tblUsuarios").DataTable().ajax.reload(null, false);
            cargarEstadisticas();
        } else {
            Swal.fire({ icon: "error", title: "Error", text: res.message });
        }
    });
});
```

---

### 3.7. Endpoints Auxiliares (Llenado Asíncrono de Selects)

Para mantener las vistas 100% libres de PHP, los `<select>` de roles y dependencias se pueblan asíncronamente al cargar la página:

#### A. Listar Roles: `GET /api/roles`
* **Respuesta Exitosa (`200 OK`):**
```json
{
  "status": 200,
  "success": true,
  "message": "Listado de roles obtenido correctamente",
  "data": [
    { "id_rol": 1, "nombre": "Administrador del Sistema" },
    { "id_rol": 2, "nombre": "Usuario Estándar" },
    { "id_rol": 3, "nombre": "Gestor de Contenido" },
    { "id_rol": 4, "nombre": "Auditor Externo" }
  ]
}
```

#### B. Listar Dependencias: `GET /api/dependencias`
* **Respuesta Exitosa (`200 OK`):**
```json
{
  "status": 200,
  "success": true,
  "message": "Listado de dependencias obtenido correctamente",
  "data": [
    { "id_dependencia": 1, "nombre": "Dirección General" },
    { "id_dependencia": 2, "nombre": "Atención al Ciudadano" },
    { "id_dependencia": 3, "nombre": "Gestión Humana y Talento" },
    { "id_dependencia": 4, "nombre": "Financiera y Presupuesto" },
    { "id_dependencia": 5, "nombre": "Jurídica y Normativa" }
  ]
}
```

---

## 4. Matriz de Comandos cURL para Pruebas Rápidas

Copia y pega cualquiera de estos comandos en tu terminal o en el diálogo **Import** de Postman:

```bash
# 1. Listar Usuarios
curl -X GET "http://localhost/odin_api/api/usuarios"

# 2. Obtener Métricas (Stats)
curl -X GET "http://localhost/odin_api/api/usuarios/stats"

# 3. Consultar Usuario por ID
curl -X GET "http://localhost/odin_api/api/usuarios/1"

# 4. Crear Usuario
curl -X POST "http://localhost/odin_api/api/usuarios" \
     -H "Content-Type: application/json" \
     -d '{"tipoIdentificacion":"CC","numeroIdentificacion":"1098765432","nombre":"Maria Lopez","correo":"maria@correo.com","idRol":2,"idDependencia":1,"direccion":"Calle 10 # 5-20","telefono":"3151234567"}'

# 5. Actualizar Usuario
curl -X PUT "http://localhost/odin_api/api/usuarios/1" \
     -H "Content-Type: application/json" \
     -d '{"nombre":"German Ramirez Modificado","correo":"germaneramirez@gmail.com","idRol":1,"idDependencia":1,"direccion":"Avenida Central # 50","telefono":"3001234567"}'

# 6. Cambiar Estado a Inactivo
curl -X PATCH "http://localhost/odin_api/api/usuarios/1/estado" \
     -H "Content-Type: application/json" \
     -d '{"estado":"Inactivo"}'

# 7. Cambiar Estado a Activo
curl -X PATCH "http://localhost/odin_api/api/usuarios/1/estado" \
     -H "Content-Type: application/json" \
     -d '{"estado":"Activo"}'

# 8. Listar Roles
curl -X GET "http://localhost/odin_api/api/roles"

# 9. Listar Dependencias
curl -X GET "http://localhost/odin_api/api/dependencias"
```

---

## 5. Colección Oficial de Postman (v2.1.0)

Para importar todas las peticiones con 1 clic en Postman:
1. Abre Postman.
2. Clic en **Import** > Pestaña **Raw text**.
3. Pega el siguiente bloque JSON y confirma:

```json
{
  "info": {
    "name": "ODIN API — Módulo Usuarios",
    "description": "Colección completa para pruebas de integración del módulo de Gestión de Usuarios.",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "1. GET — Listar Colección de Usuarios",
      "request": {
        "method": "GET",
        "header": [{ "key": "Accept", "value": "application/json" }],
        "url": { "raw": "http://localhost/odin_api/api/usuarios" }
      }
    },
    {
      "name": "2. GET — Métricas de Usuarios (Stats)",
      "request": {
        "method": "GET",
        "header": [{ "key": "Accept", "value": "application/json" }],
        "url": { "raw": "http://localhost/odin_api/api/usuarios/stats" }
      }
    },
    {
      "name": "3. GET — Consultar Usuario por ID",
      "request": {
        "method": "GET",
        "header": [{ "key": "Accept", "value": "application/json" }],
        "url": { "raw": "http://localhost/odin_api/api/usuarios/1" }
      }
    },
    {
      "name": "4. POST — Registrar Nuevo Usuario",
      "request": {
        "method": "POST",
        "header": [
          { "key": "Content-Type", "value": "application/json" },
          { "key": "Accept", "value": "application/json" }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"tipoIdentificacion\": \"CC\",\n  \"numeroIdentificacion\": \"1002345678\",\n  \"nombre\": \"Carlos Alberto Gómez\",\n  \"correo\": \"carlos.gomez@entidad.gov.co\",\n  \"idRol\": 2,\n  \"idDependencia\": 1,\n  \"direccion\": \"Calle 45 # 12-34\",\n  \"telefono\": \"3109876543\"\n}"
        },
        "url": { "raw": "http://localhost/odin_api/api/usuarios" }
      }
    },
    {
      "name": "5. PUT — Actualizar Usuario Existente",
      "request": {
        "method": "PUT",
        "header": [
          { "key": "Content-Type", "value": "application/json" },
          { "key": "Accept", "value": "application/json" }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"nombre\": \"German Ramirez Actualizado\",\n  \"correo\": \"germaneramirez@gmail.com\",\n  \"idRol\": 1,\n  \"idDependencia\": 1,\n  \"direccion\": \"Avenida Siempre Viva 123\",\n  \"telefono\": \"3001234567\"\n}"
        },
        "url": { "raw": "http://localhost/odin_api/api/usuarios/1" }
      }
    },
    {
      "name": "6. PATCH — Cambio Atómico de Estado (Inactivo/Activo)",
      "request": {
        "method": "PATCH",
        "header": [
          { "key": "Content-Type", "value": "application/json" },
          { "key": "Accept", "value": "application/json" }
        ],
        "body": {
          "mode": "raw",
          "raw": "{\n  \"estado\": \"Inactivo\"\n}"
        },
        "url": { "raw": "http://localhost/odin_api/api/usuarios/1/estado" }
      }
    },
    {
      "name": "7. GET — Listar Roles (Auxiliar)",
      "request": {
        "method": "GET",
        "header": [{ "key": "Accept", "value": "application/json" }],
        "url": { "raw": "http://localhost/odin_api/api/roles" }
      }
    },
    {
      "name": "8. GET — Listar Dependencias (Auxiliar)",
      "request": {
        "method": "GET",
        "header": [{ "key": "Accept", "value": "application/json" }],
        "url": { "raw": "http://localhost/odin_api/api/dependencias" }
      }
    }
  ]
}
```
