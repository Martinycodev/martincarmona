<?php

/**
 * Configuración de conexión a la base de datos
 * 
 * Este archivo lee las credenciales desde el archivo .env
 * para mayor seguridad (las credenciales no están en el código).
 * 
 * Patrón Singleton: Solo una conexión por petición (optimización).
 */

// Cargar las librerías instaladas con Composer
require_once BASE_PATH . '/vendor/autoload.php';

// Importar la clase Dotenv para leer el archivo .env
use Dotenv\Dotenv;

// Cargar variables de entorno desde .env (si existe)
if (file_exists(BASE_PATH . '/.env')) {
    $dotenv = Dotenv::createImmutable(BASE_PATH);
    $dotenv->load();
}

class Database
{
    /**
     * Almacena la conexión única (Singleton)
     * @var mysqli|null
     */
    private static $connection = null;

    /**
     * Obtiene la conexión a la base de datos
     * 
     * @return mysqli Conexión a la base de datos
     * @throws Exception Si falla la conexión
     */
    public static function connect()
    {
        // Si ya existe una conexión, reutilizarla (Singleton)
        if (self::$connection !== null) {
            return self::$connection;
        }

        // Leer credenciales del archivo .env
        // El operador ?? proporciona un valor por defecto si no existe
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? '';
        $name = $_ENV['DB_NAME'] ?? 'u873002419_campo';

        // Crear conexión a MySQL
        self::$connection = new mysqli($host, $user, $pass, $name);

        // Verificar si hubo error en la conexión
        if (self::$connection->connect_error) {
            // Registrar error en logs (para el administrador)
            error_log("Database connection failed: " . self::$connection->connect_error);

            // Mostrar mensaje según el ambiente
            if (($_ENV['APP_ENV'] ?? 'production') === 'production') {
                // En producción: mensaje genérico (no revelar detalles)
                die('Error de conexión a la base de datos');
            } else {
                // En desarrollo: mensaje detallado (para debugging)
                die('Database Error: ' . self::$connection->connect_error);
            }
        }

        // Configurar codificación UTF-8 (soporta ñ, acentos, emojis, etc.)
        self::$connection->query("SET NAMES 'utf8mb4'");

        return self::$connection;
    }

    /**
     * Prueba la conexión a la base de datos
     * 
     * @return array Resultado de la prueba
     */
    public static function testConnection()
    {
        try {
            $db = self::connect();
            
            // Ejecutar consulta simple de prueba
            $result = $db->query("SELECT 1 as test");

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Conexión exitosa a la base de datos'
                ];
            }

            return [
                'success' => false,
                'error' => 'Error en consulta de prueba'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Excepción: ' . $e->getMessage()
            ];
        }
    }
}
