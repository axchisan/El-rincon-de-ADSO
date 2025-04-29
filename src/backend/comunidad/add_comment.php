<?php
session_start();
require_once "../../database/conexionDB.php";

header('Content-Type: application/json');


if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para agregar un comentario.']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$libro = isset($_POST['libro']) ? trim($_POST['libro']) : '';
$comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';
$valoracion = isset($_POST['valoracion']) ? (int)$_POST['valoracion'] : 0;

// Validaciones
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
    $query = "INSERT INTO comentarios_comunidad (usuario_id, libro, comentario, valoracion) VALUES (:usuario_id, :libro, :comentario, :valoracion)";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':usuario_id' => $usuario_id,
        ':libro' => $libro,
        ':comentario' => $comentario,
        ':valoracion' => $valoracion
    ]);

    echo json_encode(['success' => true, 'message' => 'Comentario agregado exitosamente.']);
} catch (PDOException $e) {
    error_log("Error al agregar comentario: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al agregar el comentario. Por favor, intenta de nuevo.']);
}
?>