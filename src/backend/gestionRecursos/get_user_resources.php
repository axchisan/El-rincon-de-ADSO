<?php
session_start();
require_once "../../database/conexionDB.php";

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode([]);
    exit;
}

try {
    $db = conexionDB::getConexion();
    $query = "SELECT d.id, d.titulo, d.descripcion, d.url_archivo, d.fecha_creacion, c.nombre AS categoria
              FROM documentos d
              JOIN categorias c ON d.categoria_id = c.id
              WHERE d.autor_id = :autor_id
              ORDER BY d.fecha_creacion DESC";
    $stmt = $db->prepare($query);
    $stmt->execute([':autor_id' => $_SESSION['id']]);
    $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($resources);
} catch (PDOException $e) {
    echo json_encode([]);
}
?>