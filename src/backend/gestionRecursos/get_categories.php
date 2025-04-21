<?php
require_once "../../database/conexionDB.php";

try {
    $db = conexionDB::getConexion();
    $query = "SELECT id, nombre FROM categorias ORDER BY nombre";
    $stmt = $db->query($query);
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($categories);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error al obtener categorías: ' . $e->getMessage()]);
}
?>