<?php
session_start();
require_once "../../database/conexionDB.php";

header('Content-Type: application/json');


if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para responder a un comentario.']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$comentario_id = isset($_POST['comentario_id']) ? (int)$_POST['comentario_id'] : 0;
$respuesta = isset($_POST['respuesta']) ? trim($_POST['respuesta']) : '';

// Validacines
if ($comentario_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de comentario inválido.']);
    exit;
}

if (empty($respuesta)) {
    echo json_encode(['success' => false, 'message' => 'La respuesta no puede estar vacía.']);
    exit;
}

try {
    $db = conexionDB::getConexion();
    $query = "INSERT INTO respuestas_comentarios_comunidad (comentario_id, usuario_id, respuesta) VALUES (:comentario_id, :usuario_id, :respuesta)";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':comentario_id' => $comentario_id,
        ':usuario_id' => $usuario_id,
        ':respuesta' => $respuesta
    ]);

    echo json_encode(['success' => true, 'message' => 'Respuesta agregada exitosamente.']);
} catch (PDOException $e) {
    error_log("Error al agregar respuesta: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al agregar la respuesta. Por favor, intenta de nuevo.']);
}
?>