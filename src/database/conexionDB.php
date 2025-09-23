<?php
require_once "configuracion.php";

class conexionDB {
    private static $conexion = null;

    public static function getConexion() {
        if (self::$conexion === null) {
            try {
                $dsn = "pgsql:host=" . DB_HOST .
                       ";port=" . DB_PORT .
                       ";dbname=" . DB_NAME;

                // Solo añadir sslmode si es necesario
                if (DB_SSLMODE && DB_SSLMODE !== "disable") {
                    $dsn .= ";sslmode=" . DB_SSLMODE;
                }

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