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

// Leer el cuerpo JSON de la solicitud
$input = file_get_contents('php://input');
$data = json_decode($input, true);

$usuario_id = $_SESSION['usuario_id'];
$documento_id = isset($data['documento_id']) ? (int)$data['documento_id'] : 0;

error_log("Intentando eliminar recurso con ID: $documento_id"); // Depuración

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
    if ($resource['portada'] && file_exists($resource['portada'])) {
        unlink($resource['portada']);
    }
    if ($resource['url_archivo'] && file_exists(__DIR__ . '/../../' . $resource['url_archivo'])) {
        unlink(__DIR__ . '/../../' . $resource['url_archivo']);
    }

    // Eliminar asociaciones con categorías
    $query = "DELETE FROM documento_categorias WHERE documento_id = :documento_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':documento_id' => $documento_id]);

    // Eliminar asociaciones con etiquetas
    $query = "DELETE FROM documento_etiqueta WHERE documento_id = :documento_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':documento_id' => $documento_id]);

    // Eliminar el recurso
    $query = "DELETE FROM documentos WHERE id = :documento_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':documento_id' => $documento_id]);

    echo json_encode(['success' => true, 'message' => 'Recurso eliminado exitosamente.']);
} catch (PDOException $e) {
    error_log("Error al eliminar recurso: " . $e->getMessage()); // Depuración
    echo json_encode(['success' => false, 'message' => 'Error al eliminar el recurso: ' . $e->getMessage()]);
}
?>