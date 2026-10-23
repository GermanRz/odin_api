<?php

require_once __DIR__ . "/../modelos/seguimiento.modelo.php";
require_once __DIR__ . "/../controladores/seguimiento.controlador.php";
require_once __DIR__ . "/../api/helpers/respuesta.helper.php";

/**
 * Suite de Pruebas: Módulo de Consulta, Seguimiento y Trazabilidad (AGN / Ley 594 de 2000)
 */
class TestSeguimientoCompleto
{
    public static function run(): array
    {
        echo "=== [TEST] MÓDULO DE CONSULTA, SEGUIMIENTO Y TRAZABILIDAD (AGN / LEY 594) ===\n";

        $pasadas = 0;
        $fallidas = 0;

        $assertCheck = function(bool $cond, string $msg) use (&$pasadas, &$fallidas) {
            if ($cond) {
                echo "  [OK] {$msg}\n";
                $pasadas++;
            } else {
                echo "  [FAIL] {$msg}\n";
                $fallidas++;
            }
        };

        // 1. Semáforo RF-30
        $semVerde = ModeloSeguimiento::calcularSemaforo(date("Y-m-d", strtotime("+10 days")), date("Y-m-d"), 15, 1);
        $assertCheck($semVerde["color"] === "verde" && $semVerde["dias_restantes"] > 3, "Semáforo RF-30 calcula Verde (> 3 días)");

        $semAmarillo = ModeloSeguimiento::calcularSemaforo(date("Y-m-d", strtotime("+2 days")), date("Y-m-d"), 15, 1);
        $assertCheck($semAmarillo["color"] === "amarillo" && $semAmarillo["dias_restantes"] <= 3 && $semAmarillo["dias_restantes"] >= 0, "Semáforo RF-30 calcula Amarillo (0 a 3 días)");

        $semRojo = ModeloSeguimiento::calcularSemaforo(date("Y-m-d", strtotime("-5 days")), date("Y-m-d", strtotime("-20 days")), 15, 1);
        $assertCheck($semRojo["color"] === "rojo" && $semRojo["dias_restantes"] < 0, "Semáforo RF-30 calcula Rojo (< 0 días)");

        $semCerrado = ModeloSeguimiento::calcularSemaforo(null, date("Y-m-d"), 15, 6); // 6 = Finalizado
        $assertCheck($semCerrado["color"] === "finalizado", "Semáforo RF-30 calcula Estado Finalizado/Cerrado");

        // 2. Consulta de radicados con filtros
        $radicados = ModeloSeguimiento::mdlConsultarRadicados([], null, true);
        $assertCheck(count($radicados) > 0, "Consulta de radicados retorna registros (" . count($radicados) . " encontrados)");

        $primerRad = $radicados[0] ?? [];
        $camposObligatorios = ["id_radicado", "numero_radicado", "remitente", "asunto", "tipo_radicado", "estado", "semaforo_color", "semaforo_badge", "semaforo_texto", "responsable", "dependencia"];
        $todosCampos = true;
        foreach ($camposObligatorios as $campo) {
            if (!array_key_exists($campo, $primerRad)) {
                $todosCampos = false;
                break;
            }
        }
        $assertCheck($todosCampos, "Registros de seguimiento incluyen campos de negocio y semáforo RF-30");

        // 3. Detalle y Documentos
        $radId = (int)($primerRad["id_radicado"] ?? 1);
        $detalle = ModeloSeguimiento::mdlObtenerRadicadoDetalle($radId);
        $assertCheck($detalle !== null && isset($detalle["documentos"]), "Detalle del radicado obtenido con array de documentos");

        // 4. Línea de tiempo (Timeline AGN)
        $timeline = ModeloSeguimiento::mdlObtenerTrazabilidad($radId);
        $assertCheck(count($timeline) > 0 && isset($timeline[0]["fecha"]) && isset($timeline[0]["usuario_nombre"]), "Línea de tiempo contiene hitos cronológicos con usuario y fecha");

        // 5. Registro de nuevo movimiento inmutable
        $obsPrueba = "Observación de prueba automatizada para verificación de auditoría AGN - " . date("Y-m-d H:i:s");
        $okMov = ModeloSeguimiento::mdlRegistrarMovimiento($radId, 1, "observacion", $obsPrueba);
        $assertCheck($okMov, "Registro de nuevo movimiento en trazabilidad inmutable completado exitosamente");

        // Verificar que el movimiento aparece en la línea de tiempo
        $timelineActualizado = ModeloSeguimiento::mdlObtenerTrazabilidad($radId);
        $encontrado = false;
        foreach ($timelineActualizado as $t) {
            if (strpos($t["descripcion"], $obsPrueba) !== false) {
                $encontrado = true;
                break;
            }
        }
        $assertCheck($encontrado, "El movimiento registrado es inmediatamente visible y persistido en la línea de tiempo");

        // 6. Consulta pública y ciudadana (Ley 1712 de Transparencia)
        $publico = ModeloSeguimiento::mdlConsultaPublica($primerRad["numero_radicado"] ?? "");
        $assertCheck($publico !== null && isset($publico["semaforo"]) && isset($publico["trazabilidad"]), "Consulta pública ciudadana retorna ficha oficial con semáforo y trazabilidad");

        // 7. Catálogos para filtros
        $catalogos = ModeloSeguimiento::mdlObtenerCatalogosFiltros();
        $assertCheck(!empty($catalogos["dependencias"]) && !empty($catalogos["estados"]), "Catálogos de filtros cargados correctamente (dependencias y estados)");

        return [
            "pasadas"  => $pasadas,
            "fallidas" => $fallidas
        ];
    }
}

if (basename(__FILE__) === basename($_SERVER["SCRIPT_FILENAME"] ?? "")) {
    $res = TestSeguimientoCompleto::run();
    echo "\n--- Resumen: Pasadas: {$res['pasadas']} | Fallidas: {$res['fallidas']} ---\n";
    if ($res["fallidas"] > 0) {
        exit(1);
    }
    echo ">>> TODOS LOS COMPONENTES DE SEGUIMIENTO Y TRAZABILIDAD FUNCIONAN CORRECTAMENTE <<<\n";
}
