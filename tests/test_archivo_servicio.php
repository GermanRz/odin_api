<?php

require_once __DIR__ . "/../servicios/archivo.servicio.php";

class TestArchivoServicio
{
    private static int $pasadas = 0;
    private static int $fallidas = 0;

    public static function run(): array
    {
        echo "=== [TEST] Servicio de Archivos y Validación MIME ===\n";

        self::testPdfValido();
        self::testArchivoFalsoRechazado();
        self::testImagenValida();
        self::testLimiteTamano();
        self::testProteccionHtaccess();

        echo "\n";
        return ["pasadas" => self::$pasadas, "fallidas" => self::$fallidas];
    }

    private static function assert(bool $condicion, string $mensaje): void
    {
        if ($condicion) {
            self::$pasadas++;
            echo "  [OK] {$mensaje}\n";
        } else {
            self::$fallidas++;
            echo "  [FALLO] {$mensaje}\n";
        }
    }

    private static function testPdfValido(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), "pdf_test_");
        // Cabecera binaria real mínima de un documento PDF
        $contenidoPdf = "%PDF-1.4\n1 0 obj\n<<\n/Type /Catalog\n>>\nendobj\ntrailer\n<<\n>>\n%%EOF";
        file_put_contents($tmp, $contenidoPdf);

        $fakeFile = [
            "name"     => "comunicacion_oficial.pdf",
            "type"     => "application/pdf",
            "tmp_name" => $tmp,
            "error"    => UPLOAD_ERR_OK,
            "size"     => strlen($contenidoPdf)
        ];

        try {
            $resultado = ArchivoServicio::procesarArchivo($fakeFile, "TEST-2026-001");
            self::assert(
                $resultado["mime_type"] === "application/pdf",
                "PDF válido reconocido con MIME 'application/pdf'"
            );
            self::assert(
                !empty($resultado["checksum"]) && strlen($resultado["checksum"]) === 64,
                "Checksum SHA-256 generado correctamente (" . substr($resultado["checksum"], 0, 8) . "...)"
            );
            self::assert(
                file_exists(dirname(__DIR__) . "/" . $resultado["ruta_archivo"]),
                "Archivo persistido físicamente en ruta particionada"
            );

            // Limpieza del archivo de test
            ArchivoServicio::revertirArchivo($resultado["ruta_archivo"]);
        } catch (Exception $e) {
            self::assert(false, "Excepción no esperada al procesar PDF válido: " . $e->getMessage());
        } finally {
            if (file_exists($tmp)) @unlink($tmp);
        }
    }

    private static function testArchivoFalsoRechazado(): void
    {
        // Archivo que simula ser PDF por nombre, pero su binario es script PHP o texto
        $tmp = tempnam(sys_get_temp_dir(), "mal_test_");
        file_put_contents($tmp, "<?php echo 'malicioso'; ?>");

        $fakeFile = [
            "name"     => "documento_falso.pdf",
            "type"     => "application/pdf", // Tipo engañoso informado por el cliente
            "tmp_name" => $tmp,
            "error"    => UPLOAD_ERR_OK,
            "size"     => 30
        ];

        $rechazadoCorrectamente = false;
        try {
            ArchivoServicio::procesarArchivo($fakeFile, "TEST-2026-002");
        } catch (InvalidArgumentException $e) {
            $rechazadoCorrectamente = str_contains($e->getMessage(), "Tipo de archivo no permitido");
        } catch (Exception $e) {
            $rechazadoCorrectamente = false;
        } finally {
            if (file_exists($tmp)) @unlink($tmp);
        }

        self::assert(
            $rechazadoCorrectamente,
            "Archivo PHP disfrazado de PDF rechazado estrictamente por validación MIME binaria"
        );
    }

    private static function testImagenValida(): void
    {
        $tmp = tempnam(sys_get_temp_dir(), "png_test_");
        // Binario mínimo de un PNG de 1x1 transparente
        $binPng = base64_decode("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=");
        file_put_contents($tmp, $binPng);

        $fakeFile = [
            "name"     => "evidencia.png",
            "type"     => "image/png",
            "tmp_name" => $tmp,
            "error"    => UPLOAD_ERR_OK,
            "size"     => strlen($binPng)
        ];

        try {
            $resultado = ArchivoServicio::procesarArchivo($fakeFile, "TEST-2026-003");
            self::assert(
                $resultado["mime_type"] === "image/png",
                "Imagen PNG binaria reconocida con MIME 'image/png'"
            );
            ArchivoServicio::revertirArchivo($resultado["ruta_archivo"]);
        } catch (Exception $e) {
            self::assert(false, "Excepción con imagen PNG válida: " . $e->getMessage());
        } finally {
            if (file_exists($tmp)) @unlink($tmp);
        }
    }

    private static function testLimiteTamano(): void
    {
        $fakeFile = [
            "name"     => "archivo_pesado.pdf",
            "type"     => "application/pdf",
            "tmp_name" => "invalido",
            "error"    => UPLOAD_ERR_OK,
            "size"     => ArchivoServicio::MAX_TAMANO_BYTES + 1024
        ];

        $rechazadoPorTamano = false;
        try {
            ArchivoServicio::procesarArchivo($fakeFile, "TEST-2026-004");
        } catch (InvalidArgumentException $e) {
            $rechazadoPorTamano = str_contains($e->getMessage(), "supera el límite");
        } catch (Exception $e) {
            $rechazadoPorTamano = false;
        }

        self::assert(
            $rechazadoPorTamano,
            "Archivo que excede 15MB rechazado correctamente por límite de tamaño"
        );
    }

    private static function testProteccionHtaccess(): void
    {
        $dirArchivos = dirname(__DIR__) . "/vistas/archivos";
        $htaccess = $dirArchivos . "/.htaccess";
        $existe = file_exists($htaccess);
        $tieneReglas = false;

        if ($existe) {
            $contenido = file_get_contents($htaccess);
            $tieneReglas = str_contains($contenido, "Require all denied") || str_contains($contenido, "engine off");
        }

        self::assert(
            $existe && $tieneReglas,
            "Archivo .htaccess de seguridad anti-ejecución verificado en vistas/archivos/"
        );
    }
}

if (php_sapi_name() === "cli" && basename(__FILE__) === basename($_SERVER["SCRIPT_FILENAME"] ?? "")) {
    TestArchivoServicio::run();
}
