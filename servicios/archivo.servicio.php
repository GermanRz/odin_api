<?php

/**
 * Servicio Desacoplado de Gestión de Archivos Digitales — Proyecto ODIN
 * 
 * Aplica validación estricta de tipo MIME binario real, control de extensiones,
 * cálculo de checksum SHA-256, renombrado inmutable preventivo y almacenamiento
 * particionado por jerarquía mensual (vistas/archivos/radicados/YYYY/MM/).
 */
class ArchivoServicio
{
    /**
     * Mapeo estricto de tipos MIME permitidos y sus extensiones oficiales
     */
    public const MIMES_PERMITIDOS = [
        "application/pdf" => ["pdf"],
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" => ["xlsx"],
        "application/vnd.ms-excel" => ["xls"],
        "image/jpeg" => ["jpg", "jpeg"],
        "image/png" => ["png"]
    ];

    /**
     * Tamaño máximo permitido por archivo (15 MB en bytes)
     */
    public const MAX_TAMANO_BYTES = 15728640; // 15 * 1024 * 1024

    /**
     * Directorio base de almacenamiento relativo desde la raíz de odin_api
     */
    public const DIR_BASE_ARCHIVOS = "vistas/archivos/radicados";

    /**
     * Valida y procesa un archivo subido vía $_FILES.
     * 
     * @param array $archivo Array proveniente de $_FILES (ej. $_FILES['adjunto'])
     * @param string $numeroRadicado Número de radicado para la convención de nombrado
     * @param string $prefijo Prefijo del archivo (por defecto "DOC")
     * @return array Metadatos del archivo procesado
     * @throws InvalidArgumentException En caso de validación fallida (MIME, tamaño, etc.)
     * @throws RuntimeException En caso de error de sistema de archivos
     */
    public static function procesarArchivo(array $archivo, string $numeroRadicado, string $prefijo = "DOC"): array
    {
        // 1. Validar errores de subida nativos de PHP
        if (!isset($archivo["error"]) || is_array($archivo["error"])) {
            throw new InvalidArgumentException("Parámetros de subida de archivo inválidos.");
        }

        switch ($archivo["error"]) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new InvalidArgumentException("El archivo excede el límite máximo de tamaño permitido por el servidor.");
            case UPLOAD_ERR_NO_FILE:
                throw new InvalidArgumentException("No se recibió ningún archivo para procesar.");
            default:
                throw new RuntimeException("Error desconocido en la subida del archivo (código: " . $archivo["error"] . ").");
        }

        // 2. Validar tamaño máximo
        if ($archivo["size"] > self::MAX_TAMANO_BYTES) {
            $limiteMB = self::MAX_TAMANO_BYTES / (1024 * 1024);
            throw new InvalidArgumentException("El archivo '" . htmlspecialchars($archivo["nombre"] ?? $archivo["name"]) . "' supera el límite permitido de {$limiteMB}MB.");
        }

        $tmpPath = $archivo["tmp_name"];
        if (!is_uploaded_file($tmpPath) && !file_exists($tmpPath)) {
            throw new InvalidArgumentException("El archivo temporal no es válido o no existe.");
        }

        // 3. Verificación de tipo MIME binario real (finfo)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeReal = finfo_file($finfo, $tmpPath);
        finfo_close($finfo);

        if (!array_key_exists($mimeReal, self::MIMES_PERMITIDOS)) {
            throw new InvalidArgumentException(
                "Tipo de archivo no permitido. Tipo detectado: '{$mimeReal}'. " .
                "Formatos permitidos: PDF, Excel (XLSX, XLS), JPG y PNG."
            );
        }

        // 4. Validar extensión informada contra el MIME detectado
        $nombreOriginal = basename($archivo["name"]);
        $extensionOriginal = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        $extensionesValidas = self::MIMES_PERMITIDOS[$mimeReal];

        if (!in_array($extensionOriginal, $extensionesValidas, true)) {
            $extensionOriginal = $extensionesValidas[0]; // Forzar extensión canónica acorde al MIME real
        }

        // 5. Cálculo de Checksum criptográfico (SHA-256)
        $checksum = hash_file("sha256", $tmpPath);

        // 6. Preparar estructura jerárquica de carpetas YYYY/MM
        $anio = date("Y");
        $mes  = date("m");
        $raizProyecto = dirname(__DIR__);
        $dirRelativo = self::DIR_BASE_ARCHIVOS . "/{$anio}/{$mes}";
        $dirAbsoluto = $raizProyecto . "/" . $dirRelativo;

        if (!is_dir($dirAbsoluto)) {
            if (!mkdir($dirAbsoluto, 0755, true) && !is_dir($dirAbsoluto)) {
                throw new RuntimeException("No fue posible crear el directorio de almacenamiento: {$dirRelativo}");
            }
        }

        // 7. Asegurar archivo .htaccess de protección anti-ejecución en vistas/archivos/
        self::asegurarHtaccessProteccion($raizProyecto . "/vistas/archivos");

        // 8. Renombrado preventivo inmutable obligatorio
        // Formato: [PREFIJO]_[RADICADO_SANITIZADO]_[YYYYMMDD]_[HASH8].[ext]
        $radicadoSanitizado = preg_replace("/[^A-Za-z0-9_-]/", "_", $numeroRadicado);
        $fecha = date("Ymd");
        $hash8 = substr($checksum, 0, 8);
        $nombreFinal = "{$prefijo}_{$radicadoSanitizado}_{$fecha}_{$hash8}.{$extensionOriginal}";

        $rutaAbsolutaDestino = $dirAbsoluto . "/" . $nombreFinal;
        $rutaRelativaDestino = $dirRelativo . "/" . $nombreFinal;

        // 9. Mover archivo a su destino final
        if (is_uploaded_file($tmpPath)) {
            if (!move_uploaded_file($tmpPath, $rutaAbsolutaDestino)) {
                throw new RuntimeException("Error al persistir el archivo en el sistema de almacenamiento.");
            }
        } else {
            // Soporte para pruebas locales o tests de integración
            if (!copy($tmpPath, $rutaAbsolutaDestino)) {
                throw new RuntimeException("Error al copiar el archivo al almacenamiento.");
            }
        }

        return [
            "nombre_original" => $nombreOriginal,
            "nombre_archivo"  => $nombreFinal,
            "ruta_archivo"    => $rutaRelativaDestino,
            "ruta_absoluta"   => $rutaAbsolutaDestino,
            "tipo"            => $extensionOriginal,
            "tamano"          => (int)$archivo["size"],
            "mime_type"       => $mimeReal,
            "checksum"        => $checksum
        ];
    }

    /**
     * Garantiza la presencia de un archivo .htaccess que inhabilite la ejecución
     * de scripts de servidor dentro del directorio de archivos cargados.
     */
    private static function asegurarHtaccessProteccion(string $dirArchivos): void
    {
        if (!is_dir($dirArchivos)) {
            @mkdir($dirArchivos, 0755, true);
        }

        $htaccessPath = $dirArchivos . "/.htaccess";
        if (!file_exists($htaccessPath)) {
            $reglas = "# Seguridad ODIN: Prohibir ejecución de scripts en carpeta de uploads\n" .
                      "<FilesMatch \"\\.(php|phtml|php3|php4|php5|php7|php8|phps|cgi|pl|exe|sh|py)$\">\n" .
                      "    Require all denied\n" .
                      "</FilesMatch>\n" .
                      "<IfModule mod_php.c>\n" .
                      "    php_flag engine off\n" .
                      "</IfModule>\n" .
                      "<IfModule mod_php7.c>\n" .
                      "    php_flag engine off\n" .
                      "</IfModule>\n" .
                      "<IfModule mod_php8.c>\n" .
                      "    php_flag engine off\n" .
                      "</IfModule>\n" .
                      "Options -ExecCGI\n";
            @file_put_contents($htaccessPath, $reglas);
        }
    }

    /**
     * Elimina un archivo físico del disco (útil para rollback ante fallos de BD).
     */
    public static function revertirArchivo(string $rutaRelativa): bool
    {
        $raiz = dirname(__DIR__);
        $rutaCompleta = $raiz . "/" . ltrim($rutaRelativa, "/\\");
        if (file_exists($rutaCompleta) && is_file($rutaCompleta)) {
            return @unlink($rutaCompleta);
        }
        return false;
    }
}
