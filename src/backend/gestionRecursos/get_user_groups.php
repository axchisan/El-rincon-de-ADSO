<?php
session_start();
require_once "../../database/conexionDB.php";

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([]);
    exit;
}

try {
    $db = conexionDB::getConexion();
    $usuario_id = $_SESSION['usuario_id'];

    $query = "SELECT g.id, g.nombre 
              FROM grupos g
              JOIN usuario_grupo ug ON g.id = ug.grupo_id
              WHERE ug.usuario_id = :usuario_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':usuario_id' => $usuario_id]);
    $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($grupos);
} catch (PDOException $e) {
    echo json_encode([]);
}
?>