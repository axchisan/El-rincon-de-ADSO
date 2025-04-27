<?php
session_start();
require_once "../../database/conexionDB.php";

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['usuario_id'])) {
    $response['message'] = 'Debes iniciar sesión para eliminar comentarios.';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método no permitido.';
    echo json_encode($response);
    exit();
}

$comentario_id = isset($_POST['comentario_id']) ? intval($_POST['comentario_id']) : 0;
$usuario_id = $_SESSION['usuario_id'];

if ($comentario_id <= 0) {
    $response['message'] = 'ID de comentario inválido.';
    echo json_encode($response);
    exit();
}

try {
    $db = conexionDB::getConexion();

    // Verificar que el comentario pertenece al usuario
    $query = "SELECT autor_id FROM comentarios WHERE id = :comentario_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':comentario_id' => $comentario_id]);
    $autor_id = $stmt->fetchColumn();

    if ($autor_id != $usuario_id) {
        $response['message'] = 'No tienes permiso para eliminar este comentario.';
        echo json_encode($response);
        exit();
    }

    // Eliminar el comentario
    $query = "DELETE FROM comentarios WHERE id = :comentario_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':comentario_id' => $comentario_id]);

    $response['success'] = true;
    $response['message'] = 'Comentario eliminado exitosamente.';
} catch (PDOException $e) {
    $response['message'] = 'Error al eliminar el comentario: ' . $e->getMessage();
}

echo json_encode($response);
?>