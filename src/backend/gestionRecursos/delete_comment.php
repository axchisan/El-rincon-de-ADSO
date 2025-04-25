<?php
session_start();
require_once "../../database/conexionDB.php";

header('Content-Type: application/json');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para eliminar un comentario.']);
    exit;
}

// Verificar si se recibió el ID del comentario
if (!isset($_POST['comentario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Falta el ID del comentario.']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$comentario_id = intval($_POST['comentario_id']);

try {
    $db = conexionDB::getConexion();
    
    // Verificar si el comentario existe y pertenece al usuario
    $query = "SELECT id FROM comentarios WHERE id = :comentario_id AND autor_id = :autor_id";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':comentario_id' => $comentario_id,
        ':autor_id' => $usuario_id
    ]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para eliminar este comentario o no existe.']);
        exit;
    }
    
    // Eliminar el comentario
    $query = "DELETE FROM comentarios WHERE id = :comentario_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':comentario_id' => $comentario_id]);
    
    echo json_encode(['success' => true, 'message' => 'Comentario eliminado correctamente.']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar el comentario: ' . $e->getMessage()]);
}
?>


