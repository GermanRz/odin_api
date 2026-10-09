# Scripts de Estructura de Base de Datos (DDL) - ODIN

Este directorio contiene los scripts SQL requeridos para aplicar **cambios de estructura (DDL)** a las bases de datos locales o remotas (MySQL / MariaDB / PostgreSQL / Supabase) de cualquier desarrollador o entorno de despliegue.

---

## 📌 Cambio Estructural: Control Atómico de Consecutivos Anuales

- **Requerimiento:** RF-05 y RF-06 (Generación de número de radicado consecutivo único, anual e independiente por tipo: Entrada `E-AAAA-000001`, Salida `S-AAAA-000001`, Interno `INT-AAAA-000001`).
- **Problema que resuelve:** Evita condiciones de carrera (`race conditions`) y números duplicados en concurrencia alta mediante una tabla transaccional atómica con bloqueos de fila (`SELECT ... FOR UPDATE`).
- **Tabla afectada:** `radicado_consecutivos` (Nueva tabla estructural).

---

## 🚀 Instrucciones de Ejecución

### 1. Entorno Local o Servidor MySQL / MariaDB (XAMPP / Docker / RDS)

Ejecuta el archivo [01_ddl_radicado_consecutivos_mysql.sql](file:///c:/xampp/htdocs/odin_api/scripts/database/01_ddl_radicado_consecutivos_mysql.sql):

- **Desde Terminal / CLI:**
  ```bash
  mysql -u root -p odin_api_db < scripts/database/01_ddl_radicado_consecutivos_mysql.sql
  ```
- **Desde phpMyAdmin o DBeaver / Navicat:**
  Abrir el archivo SQL en el editor y ejecutarlo sobre la base de datos `odin_api_db`.

---

### 2. Servidor Remoto / Nube PostgreSQL / Supabase

Ejecuta el archivo [01_ddl_radicado_consecutivos_postgresql.sql](file:///c:/xampp/htdocs/odin_api/scripts/database/01_ddl_radicado_consecutivos_postgresql.sql):

- **Desde psql CLI:**
  ```bash
  psql -h <HOST_REMOTA> -U <USUARIO> -d <DB_NAME> -f scripts/database/01_ddl_radicado_consecutivos_postgresql.sql
  ```
- **Desde el SQL Editor de Supabase / pgAdmin:**
  Copiar y pegar el contenido del script y presionar `Run`.

---

> **Nota Importante:** Este script es idempotente (`CREATE TABLE IF NOT EXISTS`), por lo que puede ejecutarse de forma segura sin riesgo de sobrescribir información existente.
