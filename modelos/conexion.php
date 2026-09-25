<?php

/**
 * Clase Conexion
 * 
 * Gestiona la conexión a la base de datos (MySQL o PostgreSQL) mediante PDO
 * aplicando el patrón creacional Singleton.
 * Garantiza que exista una única instancia del objeto PDO activa durante
 * todo el ciclo de vida de la petición HTTP, optimizando recursos y evitando
 * conexiones redundantes.
 */
class Conexion
{

    /**
     * =========================================================================
     * SELECTOR MANUAL DE BASE DE DATOS
     * =========================================================================
     * Cambie el valor de CONEXION_ACTIVA para alternar entre las bases de datos:
     * 
     *  'local'    => Conexión a MySQL local (odin_api_db)
     *  'postgres' => Conexión a PostgreSQL en la nube (Supabase)
     * =========================================================================
     */
    public const CONEXION_ACTIVA = "local"; // Opciones: "local" o "postgres"

    /**
     * Instancia única de PDO (Patrón Singleton)
     * @var PDO|null
     */
    private static ?PDO $instancia = null;

    /**
     * Driver y tipo de conexión activo en la instancia actual
     * @var string|null
     */
    private static ?string $driverActivo = null;

    // =========================================================================
    // 1. CONFIGURACIÓN: MySQL LOCAL
    // =========================================================================
    private const LOCAL_DRIVER  = "mysql";
    private const LOCAL_HOST    = "localhost";
    private const LOCAL_PORT    = "3306";
    private const LOCAL_NAME    = "odin_api_db";
    private const LOCAL_USER    = "root";
    private const LOCAL_PASS    = "root";
    private const LOCAL_CHARSET = "utf8mb4";

    // =========================================================================
    // 2. CONFIGURACIÓN: PostgreSQL REMOTO (Supabase)
    // =========================================================================
    private const PG_DRIVER     = "pgsql";
    private const PG_HOST       = "aws-1-us-east-1.pooler.supabase.com";
    private const PG_PORT       = "5432";
    private const PG_NAME       = "postgres";
    private const PG_USER       = "postgres.qafgkqsqzinxenyosmln";
    private const PG_PASS       = "G3st0rD0cD1n";
    private const PG_CHARSET    = "utf8";

    /**
     * Constructor privado para impedir la instanciación externa directa con 'new'
     */
    private function __construct()
    {
        // Prevenir instanciación
    }

    /**
     * Prevenir la clonación de la instancia
     */
    private function __clone()
    {
        // Prevenir clonación
    }

    /**
     * Prevenir la deserialización de la instancia
     * 
     * @throws Exception
     */
    public function __wakeup()
    {
        throw new Exception("No está permitido deserializar una instancia del Singleton Conexion.");
    }

    /**
     * Obtiene y retorna la instancia única de conexión PDO.
     * Si no ha sido creada previamente, inicializa la conexión según la
     * configuración activa (MySQL local o PostgreSQL Supabase).
     * 
     * @return PDO
     * @throws PDOException
     */
    public static function conectar(): PDO
    {
        if (self::$instancia === null) {
            try {
                // Permite sobreescribir mediante variable de entorno si existe, o usar la constante CONEXION_ACTIVA
                $tipoConexion = strtolower(getenv("DB_CONNECTION") ?: self::CONEXION_ACTIVA);

                if ($tipoConexion === "postgres" || $tipoConexion === "pgsql") {
                    // --- Conexión PostgreSQL (Supabase) ---
                    $host    = getenv("DB_PG_HOST")    ?: self::PG_HOST;
                    $port    = getenv("DB_PG_PORT")    ?: self::PG_PORT;
                    $dbName  = getenv("DB_PG_NAME")    ?: self::PG_NAME;
                    $user    = getenv("DB_PG_USER")    ?: self::PG_USER;
                    $pass    = getenv("DB_PG_PASS") !== false ? getenv("DB_PG_PASS") : self::PG_PASS;
                    $charset = getenv("DB_PG_CHARSET") ?: self::PG_CHARSET;

                    $dsn = "pgsql:host={$host};port={$port};dbname={$dbName};options='--client_encoding={$charset}'";

                    $opciones = [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false
                    ];

                    self::$driverActivo = "pgsql";
                    self::$instancia = new PDO($dsn, $user, $pass, $opciones);
                } else {
                    // --- Conexión MySQL (Local) ---
                    $host    = getenv("DB_HOST")    ?: self::LOCAL_HOST;
                    $port    = getenv("DB_PORT")    ?: self::LOCAL_PORT;
                    $dbName  = getenv("DB_NAME")    ?: self::LOCAL_NAME;
                    $user    = getenv("DB_USER")    ?: self::LOCAL_USER;
                    $pass    = getenv("DB_PASS") !== false ? getenv("DB_PASS") : self::LOCAL_PASS;
                    $charset = getenv("DB_CHARSET") ?: self::LOCAL_CHARSET;

                    $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset={$charset}";

                    $opciones = [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset} COLLATE utf8mb4_unicode_ci"
                    ];

                    self::$driverActivo = "mysql";
                    self::$instancia = new PDO($dsn, $user, $pass, $opciones);
                }
            } catch (PDOException $e) {
                error_log("Error de conexión en Conexion::conectar(): " . $e->getMessage());
                throw new PDOException("Fallo en la conexión a la base de datos: " . $e->getMessage());
            }
        }

        return self::$instancia;
    }

    /**
     * Retorna el tipo de motor/driver activo ("pgsql" o "mysql").
     * 
     * @return string
     */
    public static function getDriverActivo(): string
    {
        return self::$driverActivo ?? self::CONEXION_ACTIVA;
    }

    /**
     * Cierra y restablece la instancia de conexión actual.
     * Útil en pruebas automáticas o procesos por lotes.
     * 
     * @return void
     */
    public static function cerrarConexion(): void
    {
        self::$instancia = null;
        self::$driverActivo = null;
    }
}
