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

    // Consulta principal: eliminamos d.etiquetas y usamos los campos disponibles
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

        // Obtener etiquetas desde documento_etiqueta y etiquetas
        $query = "SELECT e.nombre 
                  FROM documento_etiqueta de
                  JOIN etiquetas e ON de.etiqueta_id = e.id
                  WHERE de.documento_id = :documento_id";
        $stmt = $db->prepare($query);
        $stmt->execute([':documento_id' => $documento_id]);
        $etiquetas = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $resource['etiquetas'] = $etiquetas ?: [];

        // Formatear la duración si el recurso es un video
        if ($resource['tipo'] === 'video' && $resource['duracion']) {
            // En PostgreSQL, duracion es de tipo interval, lo convertimos a segundos
            $interval = $resource['duracion']; // Ejemplo: "16:12:00"
            $time = explode(':', $interval);
            $hours = (int)$time[0];
            $minutes = (int)$time[1];
            $seconds = (int)$time[2];
            $totalSeconds = $hours * 3600 + $minutes * 60 + $seconds;
            
            // Formatear de nuevo a HH:MM:SS
            $hours = floor($totalSeconds / 3600);
            $minutes = floor(($totalSeconds % 3600) / 60);
            $seconds = $totalSeconds % 60;
            $resource['duracion'] = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
        } else {
            $resource['duracion'] = null; // Asegurarse de que no se muestre si no es un video
        }
    }

    echo json_encode($resources);
} catch (PDOException $e) {
    error_log("Error en get_user_resources.php: " . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}
?>