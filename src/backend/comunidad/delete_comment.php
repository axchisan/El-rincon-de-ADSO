<?php
session_start();
require_once "../../database/conexionDB.php";

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para eliminar un comentario.']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$comentario_id = isset($_POST['comentario_id']) ? (int)$_POST['comentario_id'] : 0;

if ($comentario_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de comentario inválido.']);
    exit;
}

try {
    $db = conexionDB::getConexion();
    $db->beginTransaction();

    // Verificar que el comentario pertenece al usuario
    $query = "SELECT usuario_id FROM comentarios_comunidad WHERE id = :comentario_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':comentario_id' => $comentario_id]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$comment || $comment['usuario_id'] != $usuario_id) {
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para eliminar este comentario.']);
        exit;
    }

    // Eliminar respuestas asociadas
    $query = "DELETE FROM respuestas_comentarios_comunidad WHERE comentario_id = :comentario_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':comentario_id' => $comentario_id]);

    // Eliminar likes asociados
    $query = "DELETE FROM likes_comentarios_comunidad WHERE comentario_id = :comentario_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':comentario_id' => $comentario_id]);

    // Eliminar el comentario
    $query = "DELETE FROM comentarios_comunidad WHERE id = :comentario_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':comentario_id' => $comentario_id]);

    $db->commit();
    echo json_encode(['success' => true, 'message' => 'Comentario eliminado exitosamente.']);
} catch (PDOException $e) {
    $db->rollBack();
    error_log("Error al eliminar comentario: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al eliminar el comentario. Por favor, intenta de nuevo.']);
}
?>