<?php
session_start();
require_once "../../database/conexionDB.php";

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para editar un comentario.']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$comentario_id = isset($_POST['comentario_id']) ? (int)$_POST['comentario_id'] : 0;
$libro = isset($_POST['libro']) ? trim($_POST['libro']) : '';
$comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';
$valoracion = isset($_POST['valoracion']) ? (int)$_POST['valoracion'] : 0;

// Validaciones
if ($comentario_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de comentario inválido.']);
    exit;
}

if (empty($libro) || empty($comentario)) {
    echo json_encode(['success' => false, 'message' => 'El nombre del libro y el comentario son obligatorios.']);
    exit;
}

if ($valoracion < 1 || $valoracion > 5) {
    echo json_encode(['success' => false, 'message' => 'La calificación debe estar entre 1 y 5 estrellas.']);
    exit;
}

try {
    $db = conexionDB::getConexion();

    // Verificar que el comentario pertenece al usuario
    $query = "SELECT usuario_id FROM comentarios_comunidad WHERE id = :comentario_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':comentario_id' => $comentario_id]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$comment || $comment['usuario_id'] != $usuario_id) {
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para editar este comentario.']);
        exit;
    }

    // Actualizar el comentario
    $query = "UPDATE comentarios_comunidad SET libro = :libro, comentario = :comentario, valoracion = :valoracion WHERE id = :comentario_id";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':libro' => $libro,
        ':comentario' => $comentario,
        ':valoracion' => $valoracion,
        ':comentario_id' => $comentario_id
    ]);

    echo json_encode(['success' => true, 'message' => 'Comentario actualizado exitosamente.']);
} catch (PDOException $e) {
    error_log("Error al editar comentario: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al editar el comentario. Por favor, intenta de nuevo.']);
}
?>