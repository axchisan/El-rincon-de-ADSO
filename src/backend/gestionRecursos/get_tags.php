<?php
session_start();
require_once "../../database/conexionDB.php";

header('Content-Type: application/json');

try {
    $db = conexionDB::getConexion();
    if (!$db) {
        throw new Exception("No se pudo conectar a la base de datos.");
    }
    
    $query = "SELECT id, nombre FROM etiquetas";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($tags);
} catch (Exception $e) {
    // Registrar el error en un archivo de log para depuración
    error_log("Error en get_tags.php: " . $e->getMessage());
    echo json_encode(['error' => 'Error al obtener las etiquetas: ' . $e->getMessage()]);
}
?>