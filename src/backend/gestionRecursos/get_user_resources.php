<?php
session_start();
require_once "../../database/conexionDB.php";

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([]);
    exit;
}

try {
    $db = conexionDB::getConexion();
    $usuario_id = $_SESSION['usuario_id'];

    // Consulta principal: eliminamos 'visitas' y usamos los campos de la versión original
    $query = "SELECT d.id, d.titulo, d.descripcion, d.autor, d.tipo, d.url_archivo, d.portada, 
                     d.duracion, d.fecha_publicacion, d.relevancia, d.visibilidad, d.idioma, 
                     d.licencia, d.estado
           FROM documentos d
           WHERE d.autor_id = :autor_id
           ORDER BY d.fecha_publicacion DESC";
    $stmt = $db->prepare($query);
    $stmt->execute([':autor_id' => $usuario_id]);
    $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resources as &$resource) {
        $documento_id = $resource['id'];

        // Obtener categorías
        $query = "SELECT c.nombre 
                  FROM documento_categorias dc
                  JOIN categorias c ON dc.categoria_id = c.id
                  WHERE dc.documento_id = :documento_id";
        $stmt = $db->prepare($query);
        $stmt->execute([':documento_id' => $documento_id]);
        $categorias = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $resource['categorias'] = $categorias ?: [];

        // Formatear la duración si el recurso es un video
        if ($resource['tipo'] === 'video' && $resource['duracion']) {
            $seconds = (int)$resource['duracion'];
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $seconds = $seconds % 60;
            $resource['duracion'] = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
        } else {
            $resource['duracion'] = null; // Asegurarse de que no se muestre si no es un video
        }
    }

    echo json_encode($resources);
} catch (PDOException $e) {
    error_log("Error en get_user_resources.php: " . $e->getMessage());
    echo json_encode([]);
}
?>