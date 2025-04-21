<?php
session_start();
require_once "../../database/conexionDB.php";

header('Content-Type: application/json');

try {
    $db = conexionDB::getConexion();
    $usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;

    // Obtener los grupos del usuario (si está logueado)
    $user_groups = [];
    if ($usuario_id) {
        $query = "SELECT grupo_id FROM usuario_grupo WHERE usuario_id = :usuario_id";
        $stmt = $db->prepare($query);
        $stmt->execute([':usuario_id' => $usuario_id]);
        $user_groups = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Consulta para obtener recursos accesibles
    $query = "
        SELECT d.id, d.titulo, d.descripcion, d.autor, d.portada, d.tipo, d.url_archivo, d.duracion,
               d.fecha_publicacion, d.relevancia, d.visibilidad, d.grupo_id, d.idioma, d.licencia, d.estado,
               d.autor_id, u.nombre_usuario AS autor_nombre,
               ARRAY_AGG(c.nombre) AS categorias,
               ARRAY_AGG(e.nombre) AS etiquetas
        FROM documentos d
        JOIN usuarios u ON d.autor_id = u.id
        LEFT JOIN documento_categorias dc ON d.id = dc.documento_id
        LEFT JOIN categorias c ON dc.categoria_id = c.id
        LEFT JOIN documento_etiqueta de ON d.id = de.documento_id
        LEFT JOIN etiquetas e ON de.etiqueta_id = e.id
        WHERE d.estado = 'Draft'
        AND (
            d.visibilidad = 'Public'
            OR (d.visibilidad = 'Private' AND d.autor_id = :usuario_id)
            OR (d.visibilidad = 'Group' AND d.grupo_id = ANY(:grupos))
        )
        GROUP BY d.id, u.nombre_usuario
        ORDER BY d.fecha_publicacion DESC
    ";

    $stmt = $db->prepare($query);
    $stmt->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->bindValue(':grupos', '{' . implode(',', $user_groups) . '}', PDO::PARAM_STR);
    $stmt->execute();
    $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Verificar si el recurso está en favoritos (si el usuario está logueado)
    if ($usuario_id) {
        $query = "SELECT documento_id FROM favoritos WHERE usuario_id = :usuario_id";
        $stmt = $db->prepare($query);
        $stmt->execute([':usuario_id' => $usuario_id]);
        $favorites = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($resources as &$resource) {
            $resource['es_favorito'] = in_array($resource['id'], $favorites);
        }
    }

    echo json_encode($resources);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error al cargar recursos: ' . $e->getMessage()]);
}
?>