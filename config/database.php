<?php

class Database
{
    //Via Online
    /*
    public static function connect() {
        $db = new mysqli('srv699.hstgr.io', 'u873002419_campo', 'LgBuRjxeYRnEi!8', 'u873002419_campo');
        $db->query("SET NAMES 'utf8'");
        return $db;
    }*/

    //via Offline
    public static function connect()
    {
        $db = new mysqli('localhost', 'root', '', 'u873002419_campo');
        $db->query("SET NAMES 'utf8'");
        return $db;
    }

    // Método para probar la conexión
    public static function testConnection()
    {
        try {
            $db = self::connect();
            if ($db->connect_error) {
                return [
                    'success' => false,
                    'error' => 'Error de conexión: ' . $db->connect_error
                ];
            }

            // Probar una consulta simple
            $result = $db->query("SELECT 1 as test");
            if ($result) {
                $db->close();
                return [
                    'success' => true,
                    'message' => 'Conexión exitosa a la base de datos'
                ];
            } else {
                $db->close();
                return [
                    'success' => false,
                    'error' => 'Error en consulta de prueba'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => 'Excepción: ' . $e->getMessage()
            ];
        }
    }
}
