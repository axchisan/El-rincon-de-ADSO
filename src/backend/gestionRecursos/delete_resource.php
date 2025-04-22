<?php
session_start();
require_once "../../database/conexionDB.php";

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para eliminar un recurso.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$documento_id = isset($_POST['documento_id']) ? (int)$_POST['documento_id'] : 0;

if ($documento_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de recurso inválido.']);
    exit;
}

try {
    $db = conexionDB::getConexion();

    // Verificar si el usuario es el autor del recurso
    $query = "SELECT autor_id, portada, url_archivo FROM documentos WHERE id = :documento_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':documento_id' => $documento_id]);
    $resource = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$resource) {
        echo json_encode(['success' => false, 'message' => 'Recurso no encontrado.']);
        exit;
    }

    if ($resource['autor_id'] != $usuario_id) {
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para eliminar este recurso.']);
        exit;
    }

    // Eliminar archivos asociados
    if (file_exists($resource['portada'])) {
        unlink($resource['portada']);
    }
    if ($resource['url_archivo'] && file_exists(__DIR__ . '/../../' . $resource['url_archivo'])) {
        unlink(__DIR__ . '/../../' . $resource['url_archivo']);
    }

    $query = "DELETE FROM documentos WHERE id = :documento_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':documento_id' => $documento_id]);

    echo json_encode(['success' => true, 'message' => 'Recurso eliminado exitosamente.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar el recurso: ' . $e->getMessage()]);
}
?>