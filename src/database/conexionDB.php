<?php
require_once "configuracion.php";

class conexionDB {
    private static $conexion = null;

    public static function getConexion() {
        if (self::$conexion === null) {
            try {
                $dsn = "pgsql:host=" . DB_HOST .
                       ";port=" . DB_PORT .
                       ";dbname=" . DB_NAME .
                       ";sslmode=" . DB_SSLMODE .
                       ";sslrootcert=" . DB_SSLROOTCERT;

                self::$conexion = new PDO($dsn, DB_USER, DB_PASSWORD);
                self::$conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("❌ Error de conexión: " . $e->getMessage());
            }
        }
        return self::$conexion;
    }
}
?>