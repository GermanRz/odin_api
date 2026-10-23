# Guía de Integración Frontend y Pruebas Postman — API RESTful de Radicación
## Sistema de Seguimiento y Gestión Documental — Proyecto ODIN

**Dirigido a:** Desarrolladores Frontend, Integradores de API y Analistas de Aseguramiento de Calidad (QA)  
**Entorno Tecnológico:** API RESTful Nativa PHP 8.2+ | JavaScript ES6+ / Fetch API / jQuery | SweetAlert2 | Postman v10+  
**Módulo:** Radicación Documental de Entrada  
**Versión de la API:** 2.0.0  
**Fecha de Emisión:** Octubre de 2026  

---

## 1. Información General y Entorno de Conexión

### 1.1. URLs Base del Servicio
| Entorno | URL Base | Protocolo |
| :--- | :--- | :---: |
| **Desarrollo Local (XAMPP Estándar)** | `http://localhost/odin_api/api` | HTTP |
| **Desarrollo Local (Puerto Alterno)** | `http://localhost:8080/odin_api/api` | HTTP |

### 1.2. Políticas de Comunicación y Cabeceras (Headers)
- **Para consultas `GET`:**
  ```http
  Accept: application/json
  ```
- **Para envío de radicación `POST /api/radicados`:**
  - Debido al envío de archivos binarios, se utiliza `multipart/form-data`.
  - **REGLA CRÍTICA EN JAVASCRIPT:** **NO** establezca manualmente la cabecera `Content-Type` al usar `FormData` con `fetch()` o `$.ajax({ contentType: false })`. El navegador debe calcular e inyectar automáticamente el encabezado con su respectivo `boundary`.

### 1.3. Estructura Unificada de Respuestas JSON
Todas las respuestas de la API emiten `Content-Type: application/json; charset=UTF-8` a través de `RespuestaHelper`:

#### Respuesta Exitosa (`200 OK` / `201 Created`):
```json
{
  "status": 200,
  "success": true,
  "message": "Operación completada con éxito",
  "data": { ... }
}
```

#### Respuesta de Error (`400 Bad Request` / `404 Not Found` / `500 Internal Error`):
```json
{
  "status": 400,
  "success": false,
  "message": "Descripción clara del error para el usuario",
  "errors": [ ... ]
}
```

---

## 2. Catálogo Detallado de Endpoints RESTful

### 2.1. `GET /api/radicados/catalogos`
Carga en una única petición todos los catálogos maestros requeridos para poblar el formulario de radicación (trámites, dependencias con encargados y estados).

- **Método HTTP:** `GET`
- **URL:** `/api/radicados/catalogos`
- **Parámetros:** Ninguno
- **Respuesta Exitosa (`200 OK`):**
```json
{
  "status": 200,
  "success": true,
  "message": "Catálogos de radicación obtenidos correctamente",
  "data": {
    "tramites": [
      {
        "id_tramite": 1,
        "nombre": "Derecho de Petición",
        "dias_respuesta": 15,
        "prioridad_default": "MEDIA",
        "id_dependencia_responsable": 1,
        "id_usuario_responsable": 2
      }
    ],
    "dependencias": [
      {
        "id_dependencia": 1,
        "codigo": "DIR",
        "nombre": "Dirección General",
        "id_responsable": 2,
        "nombre_responsable": "Carlos Rodríguez",
        "rol_responsable": "Director General"
      },
      {
        "id_dependencia": 4,
        "codigo": "JUR",
        "nombre": "Área Jurídica",
        "id_responsable": 6,
        "nombre_responsable": "Dra. Marcela Rincón",
        "rol_responsable": "Jefe Jurídico"
      }
    ],
    "estados": [
      {
        "id_estado": 1,
        "nombre": "Pendiente",
        "color": "warning"
      }
    ]
  }
}
```

#### Implementación Frontend (JavaScript):
```javascript
fetch("api/radicados/catalogos")
  .then(res => res.json())
  .then(res => {
    if (res.success && res.data) {
      // Poblar Trámites
      const $tramite = $("#tramite").empty().append('<option value="">Seleccione...</option>');
      res.data.tramites.forEach(t => {
        $tramite.append(`<option value="${t.id_tramite}" data-dep="${t.id_dependencia_responsable}">${t.nombre}</option>`);
      });

      // Poblar Dependencias
      const $dep = $("#dependenciaResponsable").empty().append('<option value="">Seleccione...</option>');
      res.data.dependencias.forEach(d => {
        $dep.append(`<option value="${d.id_dependencia}">[${d.codigo}] ${d.nombre}</option>`);
      });

      // Fijar Estado Inicial obligatorio "Pendiente"
      $("#estadoInicial").val("1");
      $("#estadoInicialVisible").val("Pendiente");
    }
  });
```

---

### 2.2. `GET /api/remitentes/{documento}`
Consulta en tiempo real la existencia de un solicitante en el directorio de `remitentes_externos` para autollenar automáticamente los campos del formulario.

- **Método HTTP:** `GET`
- **URL:** `/api/remitentes/{documento}` (También disponible en `/api/radicados/remitente/{documento}`)
- **Parámetros de Ruta:**
  - `documento` (string/numérico, obligatorio): Número de identificación a buscar (mínimo 3 caracteres).
- **Parámetros de Query (Opcional):**
  - `tipo` (string, ej. `?tipo=cc`): Tipo de identificación para desambiguar.
- **Respuesta Exitosa (`200 OK` — Remitente Encontrado):**
```json
{
  "status": 200,
  "success": true,
  "message": "Remitente externo encontrado",
  "data": {
    "id_remitente": 3,
    "tipo_identificacion": "CC",
    "num_identificacion": "1020304050",
    "nombre": "Carlos",
    "apellido": "Pérez Gómez",
    "nombre_completo": "Carlos Pérez Gómez",
    "correo": "carlos.perez@example.com",
    "telefono": "3109876543",
    "direccion": "Calle 45 # 12-34, Bogotá"
  }
}
```
- **Respuesta (`404 Not Found` — Remitente Nuevo):**
```json
{
  "status": 404,
  "success": false,
  "message": "No se encontró ningún remitente registrado con el número de documento proporcionado."
}
```

#### Implementación Frontend con Debounce Reactivo (JavaScript):
```javascript
let debounceTimer = null;

$("#numeroDocumento").on("input", function() {
  const documento = $(this).val().trim();
  clearTimeout(debounceTimer);

  if (documento.length < 3) {
    $("#remitenteFeedback").empty();
    return;
  }

  // Esperar 400ms después de que el usuario deja de escribir
  debounceTimer = setTimeout(() => {
    fetch(`api/remitentes/${encodeURIComponent(documento)}`)
      .then(res => res.json().then(data => ({ status: res.status, body: data })))
      .then(({ status, body }) => {
        if (status === 200 && body.success && body.data) {
          const r = body.data;
          $("#nombreSolicitante").val(r.nombre_completo || `${r.nombre} ${r.apellido}`);
          $("#correoSolicitante").val(r.correo || "");
          $("#telefonoSolicitante").val(r.telefono || "");
          $("#ciudadSolicitante").val(r.direccion || "");
          
          $("#remitenteFeedback").html(
            '<span class="text-success small font-weight-bold"><i class="fas fa-check-circle mr-1"></i>Remitente registrado: Datos autollenados.</span>'
          );
        } else {
          $("#remitenteFeedback").html(
            '<span class="text-muted small"><i class="fas fa-user-plus mr-1"></i>Remitente nuevo: Complete los datos para su registro.</span>'
          );
        }
      });
  }, 400);
});
```

---

### 2.3. `GET /api/dependencias/{id}/responsable`
Obtiene los datos del funcionario activo encargado del área designada para asignar el trámite y bloquear el campo visualmente.

- **Método HTTP:** `GET`
- **URL:** `/api/dependencias/{id}/responsable`
- **Parámetros de Ruta:** `id` (entero): ID de la dependencia.
- **Respuesta Exitosa (`200 OK`):**
```json
{
  "status": 200,
  "success": true,
  "message": "Encargado de la dependencia obtenido exitosamente",
  "data": {
    "id_usuario": 6,
    "nombre": "Dra. Marcela Rincón",
    "correo": "marcela.rincon@empresa.com",
    "id_dependencia": 4,
    "rol": "Jefe Jurídico"
  }
}
```

#### Implementación Frontend (JavaScript):
```javascript
$("#dependenciaResponsable").on("change", function() {
  const idDep = $(this).val();
  if (!idDep) {
    $("#responsable").val("");
    $("#responsableVisible").val("");
    return;
  }

  fetch(`api/dependencias/${idDep}/responsable`)
    .then(r => r.json())
    .then(res => {
      if (res.success && res.data) {
        $("#responsable").val(res.data.id_usuario);
        $("#responsableVisible").val(`${res.data.nombre} (${res.data.rol})`);
      } else {
        $("#responsable").val("");
        $("#responsableVisible").val("Sin encargado asignado");
      }
    });
});
```

---

### 2.4. `POST /api/radicados`
Envío multipart para la creación oficial del radicado, validación de adjuntos, almacenamiento físico y registro de trazabilidad.

- **Método HTTP:** `POST`
- **URL:** `/api/radicados`
- **Cabecera HTTP:** `Content-Type: multipart/form-data`
- **Campos del Formulario (`FormData`):**

| Parámetro | Tipo | Obligatorio | Descripción / Valores |
| :--- | :---: | :---: | :--- |
| `tipoRadicacion` | Text | **SÍ** | Siempre `"entrada"` (fijado por defecto). |
| `canalRecepcion` | Text | **SÍ** | `"ventanilla"`, `"web"`, `"correo"`, etc. |
| `tramite` | Number | **SÍ** | ID del trámite seleccionado (`tramites.id_tramite`). |
| `estadoInicial` | Number | **SÍ** | Siempre `1` (`estados.id_estado = 1: Pendiente`). |
| `dependenciaResponsable`| Number | **SÍ** | ID de la dependencia asignada (`dependencias.id_dependencia`). |
| `responsable` | Number | No | ID del usuario encargado (calculado automáticamente). |
| `prioridad` | Text | **SÍ** | `"baja"`, `"media"`, `"alta"` o `"urgente"`. |
| `fechaLimite` | Date | **SÍ** | Fecha límite en formato `YYYY-MM-DD`. |
| `radicacionAnonima` | Bool/Text | No | `"1"` o `true` si el solicitante opta por reserva de identidad. |
| `tipoDocumento` | Text | Condicional | `"cc"`, `"ce"`, `"nit"`, `"pasaporte"`. Obligatorio si no es anónimo. |
| `numeroDocumento` | Text | Condicional | Número de documento. Obligatorio si no es anónimo. |
| `nombreSolicitante` | Text | Condicional | Nombre o razón social. Obligatorio si no es anónimo. |
| `correoSolicitante` | Text | No | Correo para notificaciones. Formato de correo válido. |
| `telefonoSolicitante`| Text | No | Número de teléfono de contacto. |
| `ciudadSolicitante` | Text | No | Dirección o municipio de residencia. |
| `asuntoDocumento` | Text | **SÍ** | Asunto principal de la comunicación (máximo 255 caracteres). |
| `descripcionTramite`| Text | **SÍ** | Resumen o texto detallado de la solicitud. |
| `numeroFolios` | Number | **SÍ** | Cantidad de hojas o folios físicos/digitales (`>= 1`). |
| `soporte` | Text | **SÍ** | `"fisico"`, `"digital"` o `"mixto"`. |
| `fechaDocumento` | Date | **SÍ** | Fecha de expedición del documento original (`YYYY-MM-DD`). |
| `etiquetas` | Text | No | Palabras clave separadas por comas. |
| `adjuntos[]` | File(s) | **SÍ** | **Uno o más archivos binarios.** Tipos permitidos: PDF, Excel, JPG, PNG. Máximo 15 MB por archivo. |

> [!NOTE]
> Las series y subseries (`serieDocumental`, `subserieDocumental`) ya **no** son enviadas ni requeridas en la radicación. El backend inicializa `id_serie`, `id_subserie`, `codigo_serie` y `codigo_subserie` en `NULL`.

- **Respuesta Exitosa (`201 Created`):**
```json
{
  "status": 201,
  "success": true,
  "message": "Documento radicado exitosamente con el número E-2026-000042",
  "data": {
    "id_radicado": 80,
    "numero_radicado": "E-2026-000042",
    "tipo_radicado": "ENTRADA",
    "remitente": "Carlos Pérez Gómez",
    "asunto": "Solicitud de Certificación Contractual 2026",
    "fecha_radicado": "2026-10-23 10:45:12",
    "documentos": [
      {
        "id_documento": 41,
        "nombre_original": "solicitud_firmada.pdf",
        "nombre_archivo": "DOC_E-2026-000042_20261023_a9b8c7d6.pdf",
        "ruta_archivo": "vistas/archivos/DOC/2026/10/DOC_E-2026-000042_20261023_a9b8c7d6.pdf",
        "tamano": 1048576,
        "mime_type": "application/pdf",
        "checksum": "e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855"
      }
    ]
  }
}
```

- **Respuesta de Error de Validación (`400 Bad Request`):**
```json
{
  "status": 400,
  "success": false,
  "message": "Debe adjuntar al menos un documento digital válido (PDF, Excel o imagen) para radicar."
}
```

#### Implementación Completa de Envío Frontend (JavaScript + SweetAlert2):
```javascript
$("#formRadicacion").on("submit", function(e) {
  e.preventDefault();

  if (uploadedFiles.length === 0) {
    Swal.fire({
      icon: "warning",
      title: "Documento requerido",
      text: "Debe adjuntar al menos un archivo digital para radicar."
    });
    return;
  }

  const formData = new FormData(this);
  formData.delete("adjuntos[]");
  uploadedFiles.forEach(file => formData.append("adjuntos[]", file));

  const $btn = $("#btnRadicar");
  $btn.prop("disabled", true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Radicando...');

  fetch("api/radicados", {
    method: "POST",
    body: formData
  })
  .then(res => res.json().then(data => ({ status: res.status, body: data })))
  .then(({ status, body }) => {
    if (status === 201 && body.success) {
      Swal.fire({
        icon: "success",
        title: "¡Radicación Exitosa!",
        html: `
          <div class="text-center my-3">
            <span class="text-muted">Número oficial asignado:</span>
            <h2 class="text-success font-weight-bold mt-1 mb-2">${body.data.numero_radicado}</h2>
            <p class="mb-1"><strong>Fecha:</strong> ${body.data.fecha_radicado}</p>
            <p class="mb-0"><strong>Documentos anexados:</strong> ${body.data.documentos.length}</p>
          </div>
        `,
        confirmButtonColor: "#28a745"
      });
      limpiarFormulario();
    } else {
      Swal.fire({
        icon: "error",
        title: "Error al radicar",
        text: body.message || "No fue posible procesar la solicitud."
      });
    }
  })
  .catch(() => {
    Swal.fire({
      icon: "error",
      title: "Error de conexión",
      text: "No fue posible conectar con el servidor."
    });
  })
  .finally(() => {
    $btn.prop("disabled", false).html('<i class="fas fa-save mr-1"></i> Radicación Documental');
  });
});
```

---

## 3. Colección Completa de Postman v2.1.0

Guarde el siguiente bloque JSON en un archivo llamado `ODIN_Radicacion_API.postman_collection.json` o cópielo e impórtelo directamente en Postman mediante **Import -> Raw Text**:

```json
{
  "info": {
    "_postman_id": "c9cd3019-b2cf-4165-9a2a-radicacion-odin-v2",
    "name": "ODIN — API RESTful Radicación Documental v2.0",
    "description": "Colección completa de pruebas para el Módulo de Radicación Documental de Entrada del Proyecto ODIN.",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "variable": [
    {
      "key": "baseUrl",
      "value": "http://localhost/odin_api/api",
      "type": "string"
    },
    {
      "key": "docExistente",
      "value": "1020304050",
      "type": "string"
    },
    {
      "key": "docInexistente",
      "value": "999888777666",
      "type": "string"
    },
    {
      "key": "idDependenciaTest",
      "value": "1",
      "type": "string"
    }
  ],
  "item": [
    {
      "name": "1. Catálogos y Auxiliares",
      "item": [
        {
          "name": "GET Catálogos Maestros de Radicación",
          "request": {
            "method": "GET",
            "header": [
              { "key": "Accept", "value": "application/json" }
            ],
            "url": {
              "raw": "{{baseUrl}}/radicados/catalogos",
              "host": ["{{baseUrl}}"],
              "path": ["radicados", "catalogos"]
            }
          },
          "event": [
            {
              "listen": "test",
              "script": {
                "exec": [
                  "pm.test('Status code es 200 OK', function () {",
                  "    pm.response.to.have.status(200);",
                  "});",
                  "pm.test('Respuesta estructurada con success=true', function () {",
                  "    var json = pm.response.json();",
                  "    pm.expect(json.success).to.be.true;",
                  "    pm.expect(json.data).to.have.property('tramites');",
                  "    pm.expect(json.data).to.have.property('dependencias');",
                  "    pm.expect(json.data).to.have.property('estados');",
                  "});"
                ],
                "type": "text/javascript"
              }
            }
          ]
        },
        {
          "name": "GET Encargado de Dependencia",
          "request": {
            "method": "GET",
            "header": [
              { "key": "Accept", "value": "application/json" }
            ],
            "url": {
              "raw": "{{baseUrl}}/dependencias/{{idDependenciaTest}}/responsable",
              "host": ["{{baseUrl}}"],
              "path": ["dependencias", "{{idDependenciaTest}}", "responsable"]
            }
          },
          "event": [
            {
              "listen": "test",
              "script": {
                "exec": [
                  "pm.test('Status code es 200 OK', function () {",
                  "    pm.response.to.have.status(200);",
                  "});",
                  "pm.test('Retorna responsable con id_usuario y nombre', function () {",
                  "    var json = pm.response.json();",
                  "    pm.expect(json.data).to.have.property('id_usuario');",
                  "    pm.expect(json.data).to.have.property('nombre');",
                  "});"
                ],
                "type": "text/javascript"
              }
            }
          ]
        }
      ]
    },
    {
      "name": "2. Remitentes Externos",
      "item": [
        {
          "name": "GET Buscar Remitente Existente (Autollenado 200)",
          "request": {
            "method": "GET",
            "header": [
              { "key": "Accept", "value": "application/json" }
            ],
            "url": {
              "raw": "{{baseUrl}}/remitentes/{{docExistente}}",
              "host": ["{{baseUrl}}"],
              "path": ["remitentes", "{{docExistente}}"]
            }
          },
          "event": [
            {
              "listen": "test",
              "script": {
                "exec": [
                  "pm.test('Status code es 200 OK', function () {",
                  "    pm.response.to.have.status(200);",
                  "});",
                  "pm.test('Retorna datos del remitente registrado', function () {",
                  "    var json = pm.response.json();",
                  "    pm.expect(json.success).to.be.true;",
                  "    pm.expect(json.data).to.have.property('nombre');",
                  "    pm.expect(json.data).to.have.property('num_identificacion');",
                  "});"
                ],
                "type": "text/javascript"
              }
            }
          ]
        },
        {
          "name": "GET Buscar Remitente Inexistente (404 Not Found)",
          "request": {
            "method": "GET",
            "header": [
              { "key": "Accept", "value": "application/json" }
            ],
            "url": {
              "raw": "{{baseUrl}}/remitentes/{{docInexistente}}",
              "host": ["{{baseUrl}}"],
              "path": ["remitentes", "{{docInexistente}}"]
            }
          },
          "event": [
            {
              "listen": "test",
              "script": {
                "exec": [
                  "pm.test('Status code es 404 Not Found', function () {",
                  "    pm.response.to.have.status(404);",
                  "});",
                  "pm.test('Responde con success=false y mensaje descriptivo', function () {",
                  "    var json = pm.response.json();",
                  "    pm.expect(json.success).to.be.false;",
                  "});"
                ],
                "type": "text/javascript"
              }
            }
          ]
        }
      ]
    },
    {
      "name": "3. Radicación Documental",
      "item": [
        {
          "name": "POST Radicación Completa Ordinaria (201 Created)",
          "request": {
            "method": "POST",
            "header": [],
            "body": {
              "mode": "formdata",
              "formdata": [
                { "key": "asuntoDocumento", "value": "Petición de Información Pública - Test Postman", "type": "text" },
                { "key": "tipoRadicacion", "value": "entrada", "type": "text" },
                { "key": "canalRecepcion", "value": "plataforma_web", "type": "text" },
                { "key": "prioridad", "value": "media", "type": "text" },
                { "key": "tramite", "value": "1", "type": "text" },
                { "key": "estadoInicial", "value": "1", "type": "text" },
                { "key": "dependenciaResponsable", "value": "1", "type": "text" },
                { "key": "tipoDocumento", "value": "cc", "type": "text" },
                { "key": "numeroDocumento", "value": "1020304050", "type": "text" },
                { "key": "nombreSolicitante", "value": "Ana Maria Torres", "type": "text" },
                { "key": "correoSolicitante", "value": "ana.torres@example.com", "type": "text" },
                { "key": "descripcionTramite", "value": "Solicitud formal de estados financieros vigencia 2026", "type": "text" },
                { "key": "numeroFolios", "value": "3", "type": "text" },
                { "key": "soporte", "value": "digital", "type": "text" },
                { "key": "fechaDocumento", "value": "2026-10-23", "type": "text" },
                { "key": "adjuntos[]", "type": "file", "src": "" }
              ]
            },
            "url": {
              "raw": "{{baseUrl}}/radicados",
              "host": ["{{baseUrl}}"],
              "path": ["radicados"]
            }
          },
          "event": [
            {
              "listen": "test",
              "script": {
                "exec": [
                  "pm.test('Status code es 201 Created', function () {",
                  "    pm.response.to.have.status(201);",
                  "});",
                  "pm.test('Consecutivo oficial inicia con E-2026-', function () {",
                  "    var json = pm.response.json();",
                  "    pm.expect(json.data.numero_radicado).to.match(/^E-2026-\\d{6}$/);",
                  "});",
                  "pm.test('Documento adjunto guardado con checksum', function () {",
                  "    var json = pm.response.json();",
                  "    pm.expect(json.data.documentos.length).to.be.above(0);",
                  "    pm.expect(json.data.documentos[0]).to.have.property('checksum');",
                  "});"
                ],
                "type": "text/javascript"
              }
            }
          ]
        },
        {
          "name": "POST Radicación Anónima (Ley 1712) (201 Created)",
          "request": {
            "method": "POST",
            "header": [],
            "body": {
              "mode": "formdata",
              "formdata": [
                { "key": "asuntoDocumento", "value": "Denuncia Anónima de Gestión Ambiental", "type": "text" },
                { "key": "tipoRadicacion", "value": "entrada", "type": "text" },
                { "key": "canalRecepcion", "value": "buzon_transparencia", "type": "text" },
                { "key": "prioridad", "value": "alta", "type": "text" },
                { "key": "tramite", "value": "1", "type": "text" },
                { "key": "estadoInicial", "value": "1", "type": "text" },
                { "key": "dependenciaResponsable", "value": "4", "type": "text" },
                { "key": "radicacionAnonima", "value": "1", "type": "text" },
                { "key": "descripcionTramite", "value": "Información aportada bajo reserva de identidad según Ley 1712 de 2014", "type": "text" },
                { "key": "numeroFolios", "value": "1", "type": "text" },
                { "key": "soporte", "value": "digital", "type": "text" },
                { "key": "fechaDocumento", "value": "2026-10-23", "type": "text" },
                { "key": "adjuntos[]", "type": "file", "src": "" }
              ]
            },
            "url": {
              "raw": "{{baseUrl}}/radicados",
              "host": ["{{baseUrl}}"],
              "path": ["radicados"]
            }
          },
          "event": [
            {
              "listen": "test",
              "script": {
                "exec": [
                  "pm.test('Status code es 201 Created', function () {",
                  "    pm.response.to.have.status(201);",
                  "});",
                  "pm.test('Remitente registrado como ANÓNIMO', function () {",
                  "    var json = pm.response.json();",
                  "    pm.expect(json.data.remitente).to.include('ANÓNIMO');",
                  "});"
                ],
                "type": "text/javascript"
              }
            }
          ]
        },
        {
          "name": "POST Error: Asunto Faltante (400 Bad Request)",
          "request": {
            "method": "POST",
            "header": [],
            "body": {
              "mode": "formdata",
              "formdata": [
                { "key": "tipoRadicacion", "value": "entrada", "type": "text" },
                { "key": "dependenciaResponsable", "value": "1", "type": "text" }
              ]
            },
            "url": {
              "raw": "{{baseUrl}}/radicados",
              "host": ["{{baseUrl}}"],
              "path": ["radicados"]
            }
          },
          "event": [
            {
              "listen": "test",
              "script": {
                "exec": [
                  "pm.test('Status code es 400 Bad Request', function () {",
                  "    pm.response.to.have.status(400);",
                  "});",
                  "pm.test('Mensaje indica que el asunto es obligatorio', function () {",
                  "    var json = pm.response.json();",
                  "    pm.expect(json.message).to.include('asunto');",
                  "});"
                ],
                "type": "text/javascript"
              }
            }
          ]
        },
        {
          "name": "POST Error: Sin Archivo Adjunto (400 Bad Request)",
          "request": {
            "method": "POST",
            "header": [],
            "body": {
              "mode": "formdata",
              "formdata": [
                { "key": "asuntoDocumento", "value": "Petición sin archivos", "type": "text" },
                { "key": "tipoRadicacion", "value": "entrada", "type": "text" },
                { "key": "dependenciaResponsable", "value": "1", "type": "text" },
                { "key": "numeroDocumento", "value": "102030", "type": "text" },
                { "key": "nombreSolicitante", "value": "Pedro Gómez", "type": "text" }
              ]
            },
            "url": {
              "raw": "{{baseUrl}}/radicados",
              "host": ["{{baseUrl}}"],
              "path": ["radicados"]
            }
          },
          "event": [
            {
              "listen": "test",
              "script": {
                "exec": [
                  "pm.test('Status code es 400 Bad Request', function () {",
                  "    pm.response.to.have.status(400);",
                  "});",
                  "pm.test('Mensaje indica obligatoriedad de adjuntos', function () {",
                  "    var json = pm.response.json();",
                  "    pm.expect(json.message).to.include('adjuntar');",
                  "});"
                ],
                "type": "text/javascript"
              }
            }
          ]
        }
      ]
    },
    {
      "name": "4. Consultas y Detalle",
      "item": [
        {
          "name": "GET Listado General de Radicados (DataTables)",
          "request": {
            "method": "GET",
            "header": [
              { "key": "Accept", "value": "application/json" }
            ],
            "url": {
              "raw": "{{baseUrl}}/radicados",
              "host": ["{{baseUrl}}"],
              "path": ["radicados"]
            }
          },
          "event": [
            {
              "listen": "test",
              "script": {
                "exec": [
                  "pm.test('Status code es 200 OK', function () {",
                  "    pm.response.to.have.status(200);",
                  "});",
                  "pm.test('Retorna arreglo data para DataTables', function () {",
                  "    var json = pm.response.json();",
                  "    pm.expect(json.data).to.be.an('array');",
                  "});"
                ],
                "type": "text/javascript"
              }
            }
          ]
        }
      ]
    }
  ]
}
```

---

## 4. Guía Paso a Paso para Pruebas en Postman

1. **Importar la Colección:**
   - Abra Postman.
   - Haga clic en el botón **Import** (esquina superior izquierda).
   - Seleccione la pestaña **Raw text**, pegue el contenido JSON del capítulo anterior y presione **Import**.
2. **Configurar el Archivo de Prueba en las Peticiones POST:**
   - Seleccione la petición `POST Radicación Completa Ordinaria`.
   - Vaya a la pestaña **Body** -> **form-data**.
   - En la fila `adjuntos[]`, pase el cursor sobre el campo **Value** y seleccione un archivo PDF, Excel o imagen real desde su equipo.
3. **Ejecutar la Colección Completa (Collection Runner):**
   - Haga clic derecho sobre la carpeta de la colección `ODIN — API RESTful Radicación Documental v2.0` y seleccione **Run collection**.
   - Presione **Run ODIN — API...**.
   - Todos los tests (`Status code es 200/201/400`, validaciones de formato `E-2026-XXXXXX` y checksums) se ejecutarán automáticamente en verde (PASS).

---

## 5. Matriz de Casos de Prueba (QA & Frontend)

| ID | Escenario de Prueba | Datos de Entrada | Comportamiento Esperado en Frontend | Código API |
| :---: | :--- | :--- | :--- | :---: |
| **CP-01** | Carga inicial del módulo | Acceso a `vistas/modulos/radicacion.php` | Campos `tipoRadicacion` ("Entrada") y `estadoInicial` ("Pendiente") cargados y bloqueados para edición. Listas de trámites y dependencias pobladas. | `200` |
| **CP-02** | Autollenado de remitente existente | Digitar cédula registrada `1020304050` en `#numeroDocumento` | Tras 400ms, los campos de nombre, correo, teléfono y dirección se autocompletan con destello verde de confirmación visual. | `200` |
| **CP-03** | Remitente nuevo | Digitar documento inexistente `999888777` | Mensaje informativo: *"Remitente nuevo: Complete los datos"*. Campos limpios y habilitados para captura. | `404` |
| **CP-04** | Activación de Radicación Anónima | Marcar switch `#radicacionAnonima` | Campos de datos del solicitante se limpian y deshabilitan inmediatamente. Resumen lateral indica *"ANÓNIMO (Ley de Transparencia)"*. | N/A |
| **CP-05** | Asignación por dependencia | Seleccionar `"Área Jurídica"` | Input `#responsableVisible` autollena `"Dra. Marcela Rincón (Jefe Jurídico)"` y permanece como solo lectura (`readonly`). | `200` |
| **CP-06** | Intento de radicación sin adjuntos | Formulario diligenciado sin adjuntar archivos | SweetAlert2 alerta: *"Debe adjuntar al menos un archivo digital para radicar"*. El formulario no se envía. | N/A |
| **CP-07** | Intento de adjuntar script malicioso | Arrastrar un archivo `.php` o `.exe` | SweetAlert2 rechaza el archivo inmediatamente en cliente por tipo MIME no permitido. | N/A |
| **CP-08** | Archivo que supera 15MB | Cargar archivo de 16 MB | SweetAlert2 advierte: *"Supera el límite de 15MB"*. No se añade a la cola de envío. | N/A |
| **CP-09** | Radicación exitosa completa | Formulario válido + PDF adjunto | Botón muestra spinner; al responder la API, modal SweetAlert2 muestra en verde el consecutivo generado (ej. `E-2026-000042`) y resetea el formulario limpiamente. | `201` |
| **CP-10** | Ausencia de clasificación documental | Inspección del formulario de radicación | No existen inputs de serie ni subserie documental. La persistencia guarda `id_serie = NULL` para posterior revisión. | `201` |
