<?php

/**
 * Clase Conexion
 * 
 * Gestiona la conexión a la base de datos MySQL mediante PDO aplicando
 * el patrón creacional Singleton.
 * Garantiza que exista una única instancia del objeto PDO activa durante
 * todo el ciclo de vida de la petición HTTP, optimizando recursos y evitando
 * conexiones redundantes.
 */
class Conexion {

    /**
     * Instancia única de PDO (Patrón Singleton)
     * @var PDO|null
     */
    private static ?PDO $instancia = null;

    /**
     * Parámetros de configuración predeterminados de la base de datos
     */
    private const DB_HOST = "localhost";
    private const DB_NAME = "odin_db";
    private const DB_USER = "root";
    private const DB_PASS = "root";
    private const DB_CHARSET = "utf8mb4";

    /**
     * Constructor privado para impedir la instanciación externa directa con 'new'
     */
    private function __construct() {
        // Prevenir instanciación
    }

    /**
     * Prevenir la clonación de la instancia
     */
    private function __clone() {
        // Prevenir clonación
    }

    /**
     * Prevenir la deserialización de la instancia
     * 
     * @throws Exception
     */
    public function __wakeup() {
        throw new Exception("No está permitido deserializar una instancia del Singleton Conexion.");
    }

    /**
     * Obtiene y retorna la instancia única de conexión PDO.
     * Si no ha sido creada previamente, inicializa la conexión aplicando
     * directivas de seguridad, codificación UTF-8 y manejo de excepciones.
     * 
     * @return PDO
     * @throws PDOException
     */
    public static function conectar(): PDO {
        if (self::$instancia === null) {
            try {
                $host    = getenv("DB_HOST") ?: self::DB_HOST;
                $dbName  = getenv("DB_NAME") ?: self::DB_NAME;
                $user    = getenv("DB_USER") ?: self::DB_USER;
                $pass    = getenv("DB_PASS") !== false ? getenv("DB_PASS") : self::DB_PASS;
                $charset = getenv("DB_CHARSET") ?: self::DB_CHARSET;

                $dsn = "mysql:host={$host};dbname={$dbName};charset={$charset}";

                $opciones = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE utf8mb4_unicode_ci"
                ];

                self::$instancia = new PDO($dsn, $user, $pass, $opciones);

            } catch (PDOException $e) {
                error_log("Error de conexión en Conexion::conectar(): " . $e->getMessage());
                throw new PDOException("Fallo en la conexión a la base de datos.");
            }
        }

        return self::$instancia;
    }

    /**
     * Cierra y restablece la instancia de conexión actual.
     * Útil en pruebas automáticas o procesos por lotes.
     * 
     * @return void
     */
    public static function cerrarConexion(): void {
        self::$instancia = null;
    }
}