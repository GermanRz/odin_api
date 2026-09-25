<?php

/**
 * Clase RespuestaHelper
 * 
 * Formateador unificado de respuestas HTTP/JSON para la API RESTful de ODIN.
 * Estandariza la estructura de salida y garantiza el uso adecuado de códigos
 * de respuesta HTTP semánticos y cabeceras JSON.
 */
class RespuestaHelper {

    /**
     * Emite una respuesta JSON de éxito y detiene la ejecución del script.
     * 
     * @param mixed $data Datos o colección que se entrega en la respuesta.
     * @param string $mensaje Mensaje descriptivo de la operación.
     * @param int $codigo Código de estado HTTP (200 OK, 201 Created por defecto).
     * @return void
     */
    public static function exito($data = [], string $mensaje = "Operación exitosa", int $codigo = 200): void {
        http_response_code($codigo);
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode([
            "status"  => $codigo,
            "success" => true,
            "message" => $mensaje,
            "data"    => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Emite una respuesta JSON de error y detiene la ejecución del script.
     * 
     * @param string $mensaje Mensaje amigable de error para el cliente.
     * @param int $codigo Código de estado HTTP de error (400, 401, 403, 404, 409, 500).
     * @param array $errores Lista detallada opcional de errores o validaciones.
     * @return void
     */
    public static function error(string $mensaje = "Error en la petición", int $codigo = 400, array $errores = []): void {
        http_response_code($codigo);
        header("Content-Type: application/json; charset=UTF-8");
        $payload = [
            "status"  => $codigo,
            "success" => false,
            "message" => $mensaje
        ];

        if (!empty($errores)) {
            $payload["errors"] = $errores;
        }

        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
