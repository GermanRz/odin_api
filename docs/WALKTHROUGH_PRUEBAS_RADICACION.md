# Manual y Walkthrough de Pruebas: Módulo de Radicación Documental
## Sistema de Seguimiento y Gestión Documental — Proyecto ODIN

**Dirigido a:** Equipo de Desarrollo, QA y Aprendices de Software  
**Entorno Tecnológico:** PHP 8.x Nativo | MySQL 8.x | API RESTful | AdminLTE 3 / Bootstrap 4 | SweetAlert2  
**Módulo:** Radicación Documental (`UC-03`, `UC-04`, `UC-05`, `RF-05`, `RF-06`, `RF-09`, `RF-10`)  
**Fecha de Emisión:** Octubre de 2026  
**Rama Git:** `features/radicacion`

---

## 1. Objetivo del Walkthrough

Este documento proporciona una guía paso a paso para que cualquier programador del proyecto pueda:
1. **Ejecutar la suite de pruebas automatizadas continuas** desde la consola de comandos.
2. **Realizar pruebas funcionales e interactivas en la interfaz web (UI/UX)** bajo AdminLTE 3.
3. **Probar los endpoints de la API RESTful** mediante clientes HTTP (Postman, cURL, Thunder Client).
4. **Verificar la seguridad documental:** validación estricta de tipo MIME binario real, anti-suplantación de extensiones y protección `.htaccess`.
5. **Asegurar la no regresión:** garantizar que futuras modificaciones no rompan las reglas de negocio establecidas.

---

## 2. Requisitos Previos del Entorno

Antes de iniciar la ejecución de pruebas, compruebe que se cumplen las siguientes condiciones en su entorno de desarrollo:

1. **Servicios de XAMPP iniciados:**
   - Apache (puerto 80 o configurado).
   - MySQL (puerto 3306).
2. **Base de Datos Local Seleccionada:**
   - La base de datos local `odin_api_db` debe estar activa con sus credenciales en [`modelos/conexion.php`](file:///c:/xampp/htdocs/odin_api/modelos/conexion.php).
3. **PHP disponible en la línea de comandos:**
   - Abra una terminal (PowerShell o CMD) y valide:
     ```bash
     php -v
     ```
     *(Debe retornar PHP 8.0 o superior).*

---

## 3. Pruebas Automatizadas de Regresión Continua (CLI)

El proyecto cuenta con una suite completa de pruebas unitarias y de integración que no requieren herramientas externas pesadas (como PHPUnit) y se ejecutan directamente con el motor nativo de PHP.

### 3.1. Ejecución del Runner Maestro (Recomendado)

Para comprobar el **100% de los componentes** en una sola ejecución:

```bash
cd C:\xampp\htdocs\odin_api
php tests/run_all_tests.php
```

#### Salida Esperada:
```text
====================================================================
   ODIN GESTOR DOCUMENTAL — SUITE DE PRUEBAS AUTOMATIZADAS          
====================================================================
Fecha y hora de ejecución: 2026-10-09 ...

=== [TEST] Servicio de Archivos y Validación MIME ===
  [OK] PDF válido reconocido con MIME 'application/pdf'
  [OK] Checksum SHA-256 generado correctamente (6e104d02...)
  [OK] Archivo persistido físicamente en ruta particionada
  [OK] Archivo PHP disfrazado de PDF rechazado estrictamente por validación MIME binaria
  [OK] Imagen PNG binaria reconocida con MIME 'image/png'
  [OK] Archivo que excede 15MB rechazado correctamente por límite de tamaño
  [OK] Archivo .htaccess de seguridad anti-ejecución verificado en vistas/archivos/

=== [TEST] Modelo de Persistencia de Radicación (MySQL Local) ===
  [OK] Consecutivo de Entrada generado con formato 'E-2026-XXXXXX'
  [OK] Consecutivo de Salida generado con formato 'S-2026-XXXXXX'
  [OK] Consecutivo Interno generado con formato 'INT-2026-XXXXXX'
  [OK] Radicado persistido exitosamente con ID ...
  [OK] Número de radicado asignado: E-2026-...
  [OK] Documento adjunto vinculado correctamente en tabla documentos
  [OK] Radicación anónima protegida por Ley de Transparencia (remitente: ANÓNIMO (Ley de Transparencia))
  [OK] Consulta general de radicados retornó ... registros con relaciones
  [OK] Detalle de radicado por ID obtenido con arreglo de documentos

=== [TEST] Capa de Controlador RESTful de Radicación ===
  [OK] Endpoint de Catálogos retorna HTTP 200 con trámites, dependencias y estados en formato JSON estándar
  [OK] Petición sin asunto rechazada con código HTTP 400 y mensaje semántico
  [OK] Petición sin documentos adjuntos rechazada con código HTTP 400
  [OK] Radicación completa vía Controlador REST exitosa con código HTTP 201 y número: E-2026-...

====================================================================
   RESUMEN FINAL DE LA SUITE DE REGRESIÓN                          
====================================================================
  Total de aserciones ejecutadas : 20
  Aserciones exitosas (PASS)     : 20
  Aserciones fallidas (FAIL)     : 0
  RESULTADO: [EXITOSO] Todos los componentes funcionan correctamente.
====================================================================
```

---

### 3.2. Ejecución Modular de Pruebas Individuales

Si está trabajando en una capa específica, puede ejecutar únicamente la suite correspondiente:

#### A. Validación MIME y Servicio de Archivos
Valida la seguridad binaria, que no se puedan subir ejecutables disfrazados, el cálculo de hash SHA-256 y la jerarquía de carpetas:
```bash
php tests/test_archivo_servicio.php
```

#### B. Persistencia y Transacciones en Base de Datos (MySQL)
Valida la tabla `radicados`, inserción en `documentos`, generación de consecutivos concurrentes (`E-AAAA-000001`) y radicación anónima:
```bash
php tests/test_radicacion_modelo.php
```

#### C. Controlador RESTful y Códigos de Respuesta HTTP
Valida el comportamiento de los endpoints, validación de campos obligatorios y formato JSON unificado de respuesta:
```bash
php tests/test_radicacion_api.php
```

---

## 4. Pruebas Funcionales en la Interfaz de Usuario (Frontend AdminLTE 3)

### 4.1. Acceso a la Interfaz
1. Inicie sesión en el sistema: `http://localhost/odin_api/`
2. En el menú lateral izquierdo de AdminLTE, haga clic en **Radicación Documental** (o navegue a `http://localhost/odin_api/radicacion`).

---

### 4.2. Caso de Prueba 1: Radicación Estándar con Documento Válido
* **Objetivo:** Verificar el flujo regular de radicación de entrada con datos del solicitante y adjunto PDF.
* **Pasos:**
  1. Seleccione **Canal de recepción** (ej. *Ventanilla* o *Correo electrónico*).
  2. Seleccione **Tipo de radicación** (*Entrada*).
  3. Seleccione un **Trámite** (ej. *Derecho de Petición*). Observe que la prioridad y el estado se configuran automáticamente.
  4. Ingrese los datos del solicitante:
     - Tipo de documento: *Cédula de ciudadanía*.
     - Número de documento: *1020304050*.
     - Nombre completo: *Juan Carlos Pérez*.
     - Correo electrónico: *juan.perez@empresa.com*.
  5. En **Clasificación Documental**, seleccione la Dependencia Destino.
  6. En **Información del Documento**, ingrese el Asunto (ej. *Solicitud formal de información pública*).
  7. En la zona de **Adjuntos**, haga clic o arrastre un archivo PDF real (menor a 15MB).
     - *Verificación visual:* Debe aparecer una tarjeta verde listando el archivo con icono de PDF, su nombre y peso en KB.
  8. Haga clic en el botón verde **Radicación Documental**.
* **Resultado Esperado:**
  - El botón cambia su estado a `<i class="fas fa-spinner fa-spin"></i> Radicando documento...` durante la petición asíncrona.
  - Aparece una alerta modal SweetAlert2 de éxito con el **Número Oficial Asignado** (ej. `E-2026-000015`), fecha y total de documentos.
  - Al presionar *Aceptar*, el formulario se reinicia y queda limpio para un nuevo registro.

---

### 4.3. Caso de Prueba 2: Seguridad — Intento de Subida de Archivo No Permitido
* **Objetivo:** Garantizar que el sistema rechace archivos con formatos no autorizados o disfrazados.
* **Pasos:**
  1. Cree un archivo de texto plano llamado `prueba_falsa.txt` o un script `malicioso.php` y renómbrelo a `prueba_falsa.pdf`.
  2. Intente arrastrarlo a la zona de **Adjuntos** en el formulario de radicación.
* **Resultado Esperado:**
  - El cliente o el backend detecta mediante inspección MIME binaria (`finfo_file`) que el contenido no es un PDF válido.
  - Aparece una alerta SweetAlert2 indicando: `Tipo de archivo no permitido. Formatos permitidos: PDF, Excel (XLSX, XLS), JPG y PNG.`
  - El archivo no es almacenado en el servidor ni registrado en la base de datos.

---

### 4.4. Caso de Prueba 3: Radicación Anónima (Ley de Transparencia — RF-09)
* **Objetivo:** Validar que los ciudadanos puedan radicar sin aportar datos personales cuando la ley lo faculte.
* **Pasos:**
  1. En la tarjeta **Datos del Solicitante**, active el switch:  
     `Radicación anónima (Ley de Transparencia - RF-09)`.
* **Resultado Esperado:**
  - Los campos de identificación, nombre, correo y teléfono se limpian automáticamente y pasan a estado `disabled`.
  - Desaparece la obligatoriedad (`required`) de dichos campos.
  - En el panel lateral de **Resumen**, el solicitante se muestra con una insignia amarilla: `ANÓNIMO (Ley de Transparencia)`.
  - Al radicar, el registro se guarda con remitente `ANÓNIMO (Ley de Transparencia)` y el trámite avanza normalmente.

---

### 4.5. Caso de Prueba 4: Gestión de Adjuntos Múltiples y Eliminación
* **Objetivo:** Verificar la selección múltiple y la posibilidad de retirar un archivo antes de guardar.
* **Pasos:**
  1. Arrastre 3 archivos válidos (ej. 2 PDFs y 1 imagen PNG).
  2. Verifique que la lista visual muestra los 3 archivos con su respectivo contador en el resumen lateral.
  3. Haga clic en el icono rojo de papelera (`btn btn-outline-danger btn-xs`) en uno de los archivos.
* **Resultado Esperado:**
  - El archivo es removido de la lista reactivamente sin recargar la página.
  - El contador de adjuntos en el panel lateral se actualiza inmediatamente.

---

## 5. Pruebas de la API RESTful (Postman / cURL / Thunder Client)

Para pruebas directas sobre el Backend desacoplado:

### 5.1. Consultar Catálogos para Selectores
```http
GET http://localhost/odin_api/api/radicados/catalogos
Accept: application/json
```
**Respuesta:** `200 OK` con colecciones `tramites`, `dependencias` y `estados`.

---

### 5.2. Radicar un Documento vía `multipart/form-data`
```http
POST http://localhost/odin_api/api/radicados
Accept: application/json
Content-Type: multipart/form-data
```
**Form Fields (Body):**
- `asuntoDocumento`: *Solicitud de prueba vía API*
- `tipoRadicacion`: *entrada*
- `canalRecepcion`: *plataforma_web*
- `prioridad`: *media*
- `tramite`: *1*
- `estadoInicial`: *1*
- `dependenciaDestino`: *1*
- `tipoDocumento`: *cc*
- `numeroDocumento`: *987654321*
- `nombreSolicitante`: *Laura Camila Gómez*
- `correoSolicitante`: *laura.gomez@test.com*
- `adjuntos[]`: *(Seleccionar archivo binario PDF o PNG)*

**Respuesta Exitosa (`201 Created`):**
```json
{
  "status": 201,
  "success": true,
  "message": "Documento radicado exitosamente con el número E-2026-000016",
  "data": {
    "id_radicado": 51,
    "numero_radicado": "E-2026-000016",
    "tipo_radicado": "ENTRADA",
    "remitente": "Laura Camila Gómez",
    "asunto": "Solicitud de prueba vía API",
    "fecha_radicado": "2026-10-09 10:22:00",
    "documentos": [
      {
        "nombre_original": "documento.pdf",
        "nombre_archivo": "DOC_TEMP_20261009102200_20261009_a1b2c3d4.pdf",
        "ruta_archivo": "vistas/archivos/radicados/2026/10/DOC_TEMP_20261009102200_20261009_a1b2c3d4.pdf",
        "tipo": "pdf",
        "tamano": 24500,
        "mime_type": "application/pdf",
        "checksum": "a1b2c3d4e5f6...",
        "id_documento": 12
      }
    ]
  }
}
```

---

### 5.3. Consultar Detalle del Radicado por ID
```http
GET http://localhost/odin_api/api/radicados/51
Accept: application/json
```
**Respuesta:** `200 OK` con toda la información del radicado y la lista de documentos adjuntos asociados.

---

## 6. Política Obligatoria de Regresión Continua

> [!IMPORTANT]
> **NORMA PARA EL EQUIPO DE DESARROLLO:**  
> Siempre que se realice cualquier modificación en:
> - `modelos/radicados.modelo.php`
> - `controladores/radicados.controlador.php`
> - `servicios/archivo.servicio.php`
> - `api/index.php`
> 
> Es **obligatorio ejecutar la suite de pruebas antes de confirmar cambios**:
> ```bash
> php tests/run_all_tests.php
> ```
> Si alguna aserción falla, el cambio **no debe ser enviado al repositorio** hasta solucionar la discrepancia.
