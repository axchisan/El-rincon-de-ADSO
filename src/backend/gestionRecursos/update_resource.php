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
    $resource_id = isset($_POST['resource_id']) ? intval($_POST['resource_id']) : 0;

    if ($resource_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de recurso inválido.']);
        exit();
    }

    // Verificar que el recurso pertenece al usuario
    $query = "SELECT * FROM documentos WHERE id = :id AND autor_id = :usuario_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $resource_id, ':usuario_id' => $user_id]);
    $resource = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$resource) {
        echo json_encode(['success' => false, 'message' => 'Recurso no encontrado o no tienes permiso.']);
        exit();
    }

    // Recolectar los datos del formulario
    $title = isset($_POST['title']) ? trim($_POST['title']) : $resource['titulo'];
    $description = isset($_POST['description']) ? trim($_POST['description']) : $resource['descripcion'];
    $author = isset($_POST['author']) ? trim($_POST['author']) : $resource['autor'];
    $type = isset($_POST['type']) ? trim($_POST['type']) : $resource['tipo'];
    $video_url = isset($_POST['video_url']) && trim($_POST['video_url']) !== '' ? trim($_POST['video_url']) : null;
    // Manejar duracion: si no se envía o está vacío, asignar null
    $video_duration = null;
    if (isset($_POST['video_duration']) && trim($_POST['video_duration']) !== '') {
        $duration = trim($_POST['video_duration']);
        // Si duracion está en formato HH:MM:SS, convertir a segundos
        $parts = explode(':', $duration);
        if (count($parts) === 3) {
            $hours = intval($parts[0]);
            $minutes = intval($parts[1]);
            $seconds = intval($parts[2]);
            $video_duration = ($hours * 3600) + ($minutes * 60) + $seconds;
        }
    }
    $publication_date = isset($_POST['publication_date']) && trim($_POST['publication_date']) !== '' ? trim($_POST['publication_date']) : $resource['fecha_publicacion'];
    $relevance = isset($_POST['relevance']) ? trim($_POST['relevance']) : $resource['relevancia'];
    $visibility = isset($_POST['visibility']) ? trim($_POST['visibility']) : $resource['visibilidad'];
    $group_id = isset($_POST['group_id']) && trim($_POST['group_id']) !== '' ? intval($_POST['group_id']) : null;
    $language = isset($_POST['language']) ? trim($_POST['language']) : $resource['idioma'];
    $license = isset($_POST['license']) ? trim($_POST['license']) : $resource['licencia'];
    $status = isset($_POST['status']) ? trim($_POST['status']) : $resource['estado'];

    // Manejo de la imagen
    $image_path = $resource['portada'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image = $_FILES['image'];
        $image_name = time() . '_' . basename($image['name']);
        $image_path = "../../uploads/" . $image_name;
        if (!move_uploaded_file($image['tmp_name'], $image_path)) {
            echo json_encode(['success' => false, 'message' => 'Error al subir la imagen.']);
            exit();
        }
    }

    // Manejo del archivo
    $file_path = $resource['url_archivo'];
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['file'];
        $file_name = time() . '_' . basename($file['name']);
        $file_path = "../../uploads/" . $file_name;
        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            echo json_encode(['success' => false, 'message' => 'Error al subir el archivo.']);
            exit();
        }
    }

    // Actualizar el recurso en la tabla documentos
    $query = "UPDATE documentos SET 
        titulo = :titulo, 
        descripcion = :descripcion, 
        autor = :autor, 
        portada = :portada, 
        tipo = :tipo, 
        url_archivo = :url_archivo, 
        url_video = :url_video, 
        duracion = :duracion, 
        fecha_publicacion = :fecha_publicacion, 
        relevancia = :relevancia, 
        visibilidad = :visibilidad, 
        grupo_id = :grupo_id, 
        idioma = :idioma, 
        licencia = :licencia, 
        estado = :estado 
        WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':titulo' => $title,
        ':descripcion' => $description,
        ':autor' => $author,
        ':portada' => $image_path,
        ':tipo' => $type,
        ':url_archivo' => $file_path,
        ':url_video' => $video_url,
        ':duracion' => $video_duration,
        ':fecha_publicacion' => $publication_date,
        ':relevancia' => $relevance,
        ':visibilidad' => $visibility,
        ':grupo_id' => $group_id,
        ':idioma' => $language,
        ':licencia' => $license,
        ':estado' => $status,
        ':id' => $resource_id
    ]);

    // Actualizar categorías
    $categories = isset($_POST['categories']) ? json_decode($_POST['categories'], true) : null;
    if (is_array($categories)) {
        $query = "DELETE FROM documento_categorias WHERE documento_id = :documento_id";
        $stmt = $db->prepare($query);
        $stmt->execute([':documento_id' => $resource_id]);
        foreach ($categories as $category_id) {
            $query = "INSERT INTO documento_categorias (documento_id, categoria_id) VALUES (:documento_id, :categoria_id)";
            $stmt = $db->prepare($query);
            $stmt->execute([':documento_id' => $resource_id, ':categoria_id' => $category_id]);
        }
    }

    // Actualizar etiquetas
    $tags = isset($_POST['tags']) ? json_decode($_POST['tags'], true) : [];
    $query = "DELETE FROM documento_etiqueta WHERE documento_id = :documento_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':documento_id' => $resource_id]);
    foreach ($tags as $tag_name) {
        $query = "SELECT id FROM etiquetas WHERE nombre = :nombre";
        $stmt = $db->prepare($query);
        $stmt->execute([':nombre' => $tag_name]);
        $tag_id = $stmt->fetchColumn();
        if (!$tag_id) {
            $query = "INSERT INTO etiquetas (nombre) VALUES (:nombre) RETURNING id";
            $stmt = $db->prepare($query);
            $stmt->execute([':nombre' => $tag_name]);
            $tag_id = $stmt->fetchColumn();
        }
        $query = "INSERT INTO documento_etiqueta (documento_id, etiqueta_id) VALUES (:documento_id, :etiqueta_id)";
        $stmt = $db->prepare($query);
        $stmt->execute([':documento_id' => $resource_id, ':etiqueta_id' => $tag_id]);
    }

    echo json_encode(['success' => true, 'message' => 'Recurso actualizado exitosamente.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el recurso: ' . $e->getMessage()]);
}
?>