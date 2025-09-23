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

    // Parámetros de búsqueda, filtro y paginación
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $category = isset($_GET['category']) ? $_GET['category'] : '';
    $type = isset($_GET['type']) ? $_GET['type'] : '';
    $relevance = isset($_GET['relevance']) ? $_GET['relevance'] : '';
    $language = isset($_GET['language']) ? $_GET['language'] : '';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 6;

    $offset = ($page - 1) * $limit;

    // Consulta para contar el total de recursos
    $count_query = "
        SELECT COUNT(DISTINCT d.id) as total
        FROM documentos d
        JOIN usuarios u ON d.autor_id = u.id
        LEFT JOIN documento_categorias dc ON d.id = dc.documento_id
        LEFT JOIN categorias c ON dc.categoria_id = c.id
        LEFT JOIN documento_etiqueta de ON d.id = de.documento_id
        LEFT JOIN etiquetas e ON de.etiqueta_id = e.id
        WHERE d.estado = 'Published'
    ";

    $params = [];

    if ($usuario_id) {
        $count_query .= " AND (
            d.visibilidad = 'Public'
            OR (d.visibilidad = 'Private' AND d.autor_id = :usuario_id)
            OR (d.visibilidad = 'Group' AND d.grupo_id = ANY(:grupos))
        )";
        $params[':usuario_id'] = $usuario_id;
        $params[':grupos'] = '{' . implode(',', $user_groups) . '}';
    } else {
        $count_query .= " AND d.visibilidad = 'Public'";
    }

    if ($search) {
        $count_query .= " AND (d.titulo ILIKE :search OR d.autor ILIKE :search OR e.nombre ILIKE :search)";
        $params[':search'] = "%$search%";
    }
    if ($category) {
        $count_query .= " AND c.id = :category";
        $params[':category'] = $category;
    }
    if ($type) {
        $count_query .= " AND d.tipo = :type";
        $params[':type'] = $type;
    }
    if ($relevance) {
        $count_query .= " AND d.relevancia = :relevance";
        $params[':relevance'] = $relevance;
    }
    if ($language) {
        $count_query .= " AND d.idioma = :language";
        $params[':language'] = $language;
    }

    $stmt = $db->prepare($count_query);
    $stmt->execute($params);
    $total_resources = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Consulta para determinar los tipos de recursos
    $query_types = "
        SELECT DISTINCT d.tipo
        FROM documentos d
        WHERE d.estado = 'Published'
    ";

    $params_types = [];
    if ($usuario_id) {
        $query_types .= " AND (
            d.visibilidad = 'Public'
            OR (d.visibilidad = 'Private' AND d.autor_id = :usuario_id)
            OR (d.visibilidad = 'Group' AND d.grupo_id = ANY(:grupos))
        )";
        $params_types[':usuario_id'] = $usuario_id;
        $params_types[':grupos'] = '{' . implode(',', $user_groups) . '}';
    } else {
        $query_types .= " AND d.visibilidad = 'Public'";
    }

    $stmt = $db->prepare($query_types);
    $stmt->execute($params_types);
    $matching_types = $stmt->fetchAll(PDO::FETCH_COLUMN);

    error_log("Matching types: " . json_encode($matching_types));

    $types_to_filter = [];
    if (!$type && !empty($matching_types)) {
        $types_to_filter = $matching_types;
    } elseif ($type) {
        $types_to_filter = [$type];
    }

    if (empty($types_to_filter)) {
        echo json_encode(['resources' => [], 'total' => 0, 'types' => $matching_types]);
        exit;
    }

    // Consulta principal para obtener los recursos
    $query = "
        SELECT d.id, d.titulo, d.descripcion, d.autor, d.portada, d.tipo, d.url_archivo, d.url_video, d.duracion,
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

    if ($search) {
        $query .= " AND (d.titulo ILIKE :search OR d.autor ILIKE :search OR e.nombre ILIKE :search)";
        $params[':search'] = "%$search%";
    }
    if ($category) {
        $query .= " AND dc.categoria_id = :category";
        $params[':category'] = $category;
    }
    if (!empty($types_to_filter)) {
        $query .= " AND d.tipo IN (" . implode(',', array_map(function($t) use ($db) {
            return $db->quote($t);
        }, $types_to_filter)) . ")";
    }
    if ($relevance) {
        $query .= " AND d.relevancia = :relevance";
        $params[':relevance'] = $relevance;
    }
    if ($language) {
        $query .= " AND d.idioma = :language";
        $params[':language'] = $language;
    }

    $query .= " GROUP BY d.id, u.nombre_usuario ORDER BY d.fecha_publicacion DESC LIMIT :limit OFFSET :offset";
    $params[':limit'] = $limit;
    $params[':offset'] = $offset;

    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);

    error_log("Resources found for type " . ($type ?: 'all') . ": " . count($resources));
    error_log("Resources: " . json_encode($resources));

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

    echo json_encode([
        'resources' => $resources,
        'total' => (int)$total_resources,
        'types' => $matching_types
    ]);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['error' => 'Error al buscar recursos: ' . $e->getMessage()]);
}
?>