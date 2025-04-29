<?php
session_start();
require_once "../../database/conexionDB.php";

header('Content-Type: application/json');


if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para dar me gusta.']);
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

    // Verificar si el usuario ya dio "me gusta"
    $query = "SELECT COUNT(*) FROM likes_comentarios_comunidad WHERE usuario_id = :usuario_id AND comentario_id = :comentario_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':usuario_id' => $usuario_id, ':comentario_id' => $comentario_id]);
    $user_liked = $stmt->fetchColumn() > 0;

    if ($user_liked) {
        // Quitar el "me gusta"
        $query = "DELETE FROM likes_comentarios_comunidad WHERE usuario_id = :usuario_id AND comentario_id = :comentario_id";
        $stmt = $db->prepare($query);
        $stmt->execute([':usuario_id' => $usuario_id, ':comentario_id' => $comentario_id]);

        // Decrementar el contador de likes
        $query = "UPDATE comentarios_comunidad SET likes = likes - 1 WHERE id = :comentario_id";
        $stmt = $db->prepare($query);
        $stmt->execute([':comentario_id' => $comentario_id]);

        $action = 'removed';
    } else {
        // Agregar el "me gusta"
        $query = "INSERT INTO likes_comentarios_comunidad (usuario_id, comentario_id) VALUES (:usuario_id, :comentario_id)";
        $stmt = $db->prepare($query);
        $stmt->execute([':usuario_id' => $usuario_id, ':comentario_id' => $comentario_id]);

        // Incrementar el contador de likes
        $query = "UPDATE comentarios_comunidad SET likes = likes + 1 WHERE id = :comentario_id";
        $stmt = $db->prepare($query);
        $stmt->execute([':comentario_id' => $comentario_id]);

        $action = 'added';
    }

    // Obtener el nuevo número de likes
    $query = "SELECT likes FROM comentarios_comunidad WHERE id = :comentario_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':comentario_id' => $comentario_id]);
    $likes = $stmt->fetchColumn();

    $db->commit();
    echo json_encode(['success' => true, 'action' => $action, 'likes' => $likes]);
} catch (PDOException $e) {
    $db->rollBack();
    error_log("Error al manejar el me gusta: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al procesar el me gusta. Por favor, intenta de nuevo.']);
}
?>