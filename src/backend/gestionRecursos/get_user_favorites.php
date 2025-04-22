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
    $query = "SELECT d.id, d.titulo, d.descripcion, d.autor, d.tipo, d.url_archivo, d.portada, 
                     d.fecha_publicacion, d.relevancia, d.visibilidad, d.idioma, d.licencia, d.estado,
                     f.fecha_agregado
              FROM documentos d
              JOIN favoritos f ON d.id = f.documento_id
              WHERE f.usuario_id = :usuario_id
              ORDER BY f.fecha_agregado DESC";
    $stmt = $db->prepare($query);
    $stmt->execute([':usuario_id' => $usuario_id]);
    $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resources as &$resource) {
        $documento_id = $resource['id'];
        $query = "SELECT c.nombre 
                  FROM documento_categorias dc
                  JOIN categorias c ON dc.categoria_id = c.id
                  WHERE dc.documento_id = :documento_id";
        $stmt = $db->prepare($query);
        $stmt->execute([':documento_id' => $documento_id]);
        $categorias = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $resource['categorias'] = $categorias ?: [];

        if ($resource['tipo'] === 'video') {
            $query = "SELECT duracion FROM documentos WHERE id = :documento_id";
            $stmt = $db->prepare($query);
            $stmt->execute([':documento_id' => $documento_id]);
            $resource['duracion'] = $stmt->fetchColumn() ?: null;
        }
    }

    echo json_encode($resources);
} catch (PDOException $e) {
    echo json_encode([]);
}
?>