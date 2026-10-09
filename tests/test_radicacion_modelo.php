<?php

require_once __DIR__ . "/../modelos/radicados.modelo.php";

class TestRadicacionModelo
{
    private static int $pasadas = 0;
    private static int $fallidas = 0;

    public static function run(): array
    {
        echo "=== [TEST] Modelo de Persistencia de Radicación (MySQL Local) ===\n";

        self::testGeneracionConsecutivo();
        self::testRadicacionCompletaConDocumento();
        self::testRadicacionAnonima();
        self::testListadoYDetalle();

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

    private static function testGeneracionConsecutivo(): void
    {
        $anio = date("Y");
        $numE = ModeloRadicados::mdlGenerarNumeroRadicado("ENTRADA");
        $numS = ModeloRadicados::mdlGenerarNumeroRadicado("SALIDA");
        $numI = ModeloRadicados::mdlGenerarNumeroRadicado("INTERNO");

        self::assert(
            str_starts_with($numE, "E-{$anio}-"),
            "Consecutivo de Entrada generado con formato 'E-{$anio}-XXXXXX' ({$numE})"
        );
        self::assert(
            str_starts_with($numS, "S-{$anio}-"),
            "Consecutivo de Salida generado con formato 'S-{$anio}-XXXXXX' ({$numS})"
        );
        self::assert(
            str_starts_with($numI, "INT-{$anio}-"),
            "Consecutivo Interno generado con formato 'INT-{$anio}-XXXXXX' ({$numI})"
        );
    }

    private static function testRadicacionCompletaConDocumento(): void
    {
        $datosRadicado = [
            "asunto"          => "Solicitud de Certificación Laboral - Test Unitario",
            "tipo_radicado"   => "ENTRADA",
            "medio_recepcion" => "Ventanilla",
            "prioridad"       => "MEDIA",
            "id_tramite"      => 1,
            "id_estado"       => 1,
            "id_dependencia"  => 1,
            "id_usuario"      => 1,
            "es_anonimo"      => false,
            "remitente"       => "Carlos Mendoza"
        ];

        $datosRemitente = [
            "tipo_identificacion" => "CC",
            "num_identificacion"  => "9988776655",
            "nombre"              => "Carlos",
            "apellido"            => "Mendoza",
            "nombre_completo"     => "Carlos Mendoza",
            "correo"              => "carlos.mendoza@test.com",
            "telefono"            => "3001234567",
            "direccion"           => "Calle 10 # 20-30"
        ];

        $documentosMetadatos = [
            [
                "nombre_original" => "formulario_certificacion.pdf",
                "nombre_archivo"  => "DOC_TEST_001.pdf",
                "ruta_archivo"    => "vistas/archivos/radicados/2026/10/DOC_TEST_001.pdf",
                "tipo"            => "pdf",
                "tamano"          => 2048,
                "mime_type"       => "application/pdf",
                "checksum"        => hash("sha256", "contenido_de_prueba")
            ]
        ];

        try {
            $resultado = ModeloRadicados::mdlRadicarDocumento($datosRadicado, $documentosMetadatos, $datosRemitente);

            self::assert(
                !empty($resultado["id_radicado"]) && $resultado["id_radicado"] > 0,
                "Radicado persistido exitosamente con ID " . $resultado["id_radicado"]
            );
            self::assert(
                !empty($resultado["numero_radicado"]),
                "Número de radicado asignado: " . $resultado["numero_radicado"]
            );
            self::assert(
                count($resultado["documentos"]) === 1 && !empty($resultado["documentos"][0]["id_documento"]),
                "Documento adjunto vinculado correctamente en tabla documentos con ID " . ($resultado["documentos"][0]["id_documento"] ?? "")
            );
        } catch (Exception $e) {
            self::assert(false, "Excepción en mdlRadicarDocumento: " . $e->getMessage());
        }
    }

    private static function testRadicacionAnonima(): void
    {
        $datosRadicado = [
            "asunto"          => "Denuncia Anónima por Transparencia - Test",
            "tipo_radicado"   => "ENTRADA",
            "medio_recepcion" => "Plataforma Web",
            "prioridad"       => "ALTA",
            "id_tramite"      => 2,
            "id_estado"       => 1,
            "id_dependencia"  => 2,
            "id_usuario"      => 1,
            "es_anonimo"      => true
        ];

        $documentosMetadatos = [
            [
                "nombre_original" => "evidencia_anonima.png",
                "nombre_archivo"  => "DOC_ANON_001.png",
                "ruta_archivo"    => "vistas/archivos/radicados/2026/10/DOC_ANON_001.png",
                "tipo"            => "png",
                "tamano"          => 1024,
                "mime_type"       => "image/png",
                "checksum"        => hash("sha256", "png_anon")
            ]
        ];

        try {
            $resultado = ModeloRadicados::mdlRadicarDocumento($datosRadicado, $documentosMetadatos, null);

            self::assert(
                str_contains($resultado["remitente"], "ANÓNIMO"),
                "Radicación anónima protegida por Ley de Transparencia (remitente: " . $resultado["remitente"] . ")"
            );
        } catch (Exception $e) {
            self::assert(false, "Excepción en radicación anónima: " . $e->getMessage());
        }
    }

    private static function testListadoYDetalle(): void
    {
        $lista = ModeloRadicados::mdlListarRadicados();
        self::assert(
            is_array($lista) && count($lista) > 0,
            "Consulta general de radicados retornó " . count($lista) . " registros con relaciones"
        );

        $ultimoId = (int)$lista[0]["id_radicado"];
        $detalle = ModeloRadicados::mdlObtenerRadicadoPorId($ultimoId);

        self::assert(
            $detalle !== null && isset($detalle["documentos"]),
            "Detalle de radicado por ID (#{$ultimoId}) obtenido con arreglo de documentos"
        );
    }
}

if (php_sapi_name() === "cli" && basename(__FILE__) === basename($_SERVER["SCRIPT_FILENAME"] ?? "")) {
    TestRadicacionModelo::run();
}
