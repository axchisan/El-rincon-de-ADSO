<?php
session_start();
require_once "../../database/conexionDB.php";

header('Content-Type: application/json');

try {
    $db = conexionDB::getConexion();
    $usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;

    $user_groups = [];
    if ($usuario_id) {
        $query = "SELECT grupo_id FROM usuario_grupo WHERE usuario_id = :usuario_id";
        $stmt = $db->prepare($query);
        $stmt->execute([':usuario_id' => $usuario_id]);
        $user_groups = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 3;
    $search = isset($_GET['search']) ? '%' . $_GET['search'] . '%' : null;
    $category = isset($_GET['category']) ? (int)$_GET['category'] : null;
    $type = isset($_GET['type']) ? $_GET['type'] : null;
    $relevance = isset($_GET['relevance']) ? $_GET['relevance'] : null;
    $language = isset($_GET['language']) ? $_GET['language'] : null;

    $query = "
        SELECT d.id, d.titulo, d.descripcion, d.autor, d.portada, d.tipo, d.url_archivo, d.duracion,
               d.fecha_publicacion, d.relevancia, d.visibilidad, d.grupo_id, d.idioma, d.licencia, d.estado,
               d.autor_id, u.nombre_usuario AS autor_nombre,
               COALESCE(ARRAY_AGG(c.nombre) FILTER (WHERE c.nombre IS NOT NULL), '{}') AS categorias,
               COALESCE(ARRAY_AGG(e.nombre) FILTER (WHERE e.nombre IS NOT NULL), '{}') AS etiquetas
        FROM documentos d
        JOIN usuarios u ON d.autor_id = u.id
        LEFT JOIN documento_categorias dc ON d.id = dc.documento_id
        LEFT JOIN categorias c ON dc.categoria_id = c.id
        LEFT JOIN documento_etiqueta de ON d.id = de.documento_id
        LEFT JOIN etiquetas e ON de.etiqueta_id = e.id
        WHERE d.estado = 'Published'
    ";

    $params = [];

    // Ajuster de la condición de visibilidad
    if ($usuario_id) {
        $query .= " AND (
            d.visibilidad = 'Public'
            OR (d.visibilidad = 'Private' AND d.autor_id = :usuario_id)
            OR (d.visibilidad = 'Group' AND d.grupo_id = ANY(:grupos))
        )";
        $params[':usuario_id'] = $usuario_id;
        $params[':grupos'] = '{' . implode(',', $user_groups) . '}';
    } else {
        $query .= " AND d.visibilidad = 'Public'";
    }

    // Aplicar filtros
    if ($search) {
        $query .= " AND (d.titulo ILIKE :search OR d.descripcion ILIKE :search OR d.autor ILIKE :search)";
        $params[':search'] = $search;
    }

    if ($category) {
        $query .= " AND dc.categoria_id = :category";
        $params[':category'] = $category;
    }

    if ($type) {
        $query .= " AND d.tipo = :type";
        $params[':type'] = $type;
    }

    if ($relevance) {
        $query .= " AND d.relevancia = :relevance";
        $params[':relevance'] = $relevance;
    }

    if ($language) {
        $query .= " AND d.idioma = :language";
        $params[':language'] = $language;
    }

    $query .= " GROUP BY d.id, u.nombre_usuario ORDER BY d.fecha_publicacion DESC LIMIT :limit";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($resources as &$resource) {
        $resource['categorias'] = $resource['categorias'] === '{}'
            ? []
            : array_map('trim', explode(',', trim($resource['categorias'], '{}')));

        $resource['etiquetas'] = $resource['etiquetas'] === '{}'
            ? []
            : array_map('trim', explode(',', trim($resource['etiquetas'], '{}')));

        if (empty($resource['categorias'])) {
            $resource['categorias'] = [];
        }
        if (empty($resource['etiquetas'])) {
            $resource['etiquetas'] = [];
        }
    }


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
    echo json_encode(['error' => 'Error al obtener recursos recientes: ' . $e->getMessage()]);
}
?>