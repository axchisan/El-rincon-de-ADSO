<?php
session_start();
require_once "../../database/conexionDB.php";

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id']) || !isset($_POST['documento_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado o ID de documento no proporcionado']);
    exit;
}

try {
    $db = conexionDB::getConexion();
    $usuario_id = $_SESSION['usuario_id'];
    $documento_id = $_POST['documento_id'];

    $query = "SELECT COUNT(*) FROM favoritos WHERE usuario_id = :usuario_id AND documento_id = :documento_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':usuario_id' => $usuario_id, ':documento_id' => $documento_id]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'Este recurso ya está en tus favoritos']);
        exit;
    }

   
    $query = "INSERT INTO favoritos (usuario_id, documento_id, fecha_agregado) VALUES (:usuario_id, :documento_id, NOW())";
    $stmt = $db->prepare($query);
    $stmt->execute([':usuario_id' => $usuario_id, ':documento_id' => $documento_id]);

    echo json_encode(['success' => true, 'message' => 'Recurso añadido a favoritos']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al añadir a favoritos']);
}
?>