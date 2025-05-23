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

    
    $query = "DELETE FROM guardados WHERE usuario_id = :usuario_id AND documento_id = :documento_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':usuario_id' => $usuario_id, ':documento_id' => $documento_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Recurso eliminado de guardados']);
    } else {
        echo json_encode(['success' => false, 'message' => 'El recurso no estaba en tus guardados']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar de guardados']);
}
?>