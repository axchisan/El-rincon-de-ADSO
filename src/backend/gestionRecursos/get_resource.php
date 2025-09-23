<?php
session_start();
require_once "../../database/conexionDB.php";

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No estás autenticado.']);
    exit();
}

try {
    $db = conexionDB::getConexion();
    $user_id = $_SESSION['usuario_id'];
    $resource_id = isset($_GET['resource_id']) ? intval($_GET['resource_id']) : 0;

    if ($resource_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de recurso inválido.']);
        exit();
    }

    // Obtener el recurso
    $query = "SELECT * FROM documentos WHERE id = :id AND autor_id = :usuario_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $resource_id, ':usuario_id' => $user_id]);
    $resource = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$resource) {
        echo json_encode(['success' => false, 'message' => 'Recurso no encontrado o no tienes permiso.']);
        exit();
    }

    // Obtener las categorías
    $query = "SELECT c.id, c.nombre FROM categorias c
              JOIN documento_categorias dc ON c.id = dc.categoria_id
              WHERE dc.documento_id = :documento_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':documento_id' => $resource_id]);
    $resource['categorias'] = $stmt->fetchAll(PDO::FETCH_COLUMN, 0); // Solo los IDs
    error_log("Categorías para el recurso $resource_id: " . json_encode($resource['categorias']));

    // Obtener las etiquetas (nombres en lugar de IDs)
    $query = "SELECT e.nombre FROM etiquetas e
              JOIN documento_etiqueta de ON e.id = de.etiqueta_id
              WHERE de.documento_id = :documento_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':documento_id' => $resource_id]);
    $resource['etiquetas'] = $stmt->fetchAll(PDO::FETCH_COLUMN, 0); // Nombres de las etiquetas

    echo json_encode(['success' => true, 'resource' => $resource]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener el recurso: ' . $e->getMessage()]);
}
?>