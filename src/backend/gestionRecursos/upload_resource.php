<?php
session_start();
require_once "../../database/conexionDB.php";

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para subir un recurso.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$author = trim($_POST['author'] ?? '');
$resource_type = $_POST['type'] ?? '';
$video_url = $_POST['video_url'] ?? null;
$video_duration = $_POST['video_duration'] ?? null;
$categories = json_decode($_POST['categories'] ?? '[]', true);
$tags = json_decode($_POST['tags'] ?? '[]', true);
$publication_date = $_POST['publication_date'] ?? null;
$relevance = $_POST['relevance'] ?? 'Medium';
$visibility = $_POST['visibility'] ?? 'Public';
$group_id = $_POST['group_id'] ?? null;
$language = $_POST['language'] ?? 'es';
$license = $_POST['license'] ?? 'CC BY-SA';
$status = $_POST['status'] ?? 'Draft';
$autor_id = $_SESSION['usuario_id'];


if (empty($title) || empty($author) || empty($resource_type) || empty($categories) || empty($relevance) || empty($visibility) || empty($language) || empty($license) || empty($status)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben estar completos.']);
    exit;
}

if ($visibility === 'Group' && empty($group_id)) {
    echo json_encode(['success' => false, 'message' => 'Debes seleccionar un grupo para la visibilidad "Solo para un grupo".']);
    exit;
}

// portada (obligatoria)
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Debes subir una imagen de portada.']);
    exit;
}

$allowed_image_types = ['image/jpeg', 'image/png', 'image/gif'];
$max_image_size = 5 * 1024 * 1024; // 5 MB

$image = $_FILES['image'];
if (!in_array($image['type'], $allowed_image_types)) {
    echo json_encode(['success' => false, 'message' => 'Tipo de imagen no permitido. Solo se permiten JPEG, PNG y GIF.']);
    exit;
}

if ($image['size'] > $max_image_size) {
    echo json_encode(['success' => false, 'message' => 'La imagen es demasiado grande. El tamaño máximo es 5 MB.']);
    exit;
}

$upload_dir = __DIR__ . '/../../uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$image_extension = pathinfo($image['name'], PATHINFO_EXTENSION);
$image_name = uniqid() . '_cover.' . $image_extension;
$image_path = $upload_dir . $image_name;

if (!move_uploaded_file($image['tmp_name'], $image_path)) {
    echo json_encode(['success' => false, 'message' => 'Error al subir la imagen de portada. Intenta de nuevo.']);
    exit;
}

$image_url = '../../uploads/' . $image_name;


$file_url = null;
if ($resource_type === 'video') {
    if (empty($video_url)) {
        unlink($image_path);
        echo json_encode(['success' => false, 'message' => 'Debes proporcionar una URL de video válida.']);
        exit;
    }
    $youtube_regex = '/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/(watch\?v=)?([a-zA-Z0-9_-]{11})/';
    if (!preg_match($youtube_regex, $video_url)) {
        unlink($image_path);
        echo json_encode(['success' => false, 'message' => 'La URL del video no es válida. Debe ser un enlace de YouTube.']);
        exit;
    }
    $file_url = $video_url;
} else {
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        unlink($image_path);
        echo json_encode(['success' => false, 'message' => 'Debes subir un archivo para este tipo de recurso.']);
        exit;
    }

    $allowed_file_types = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'image/jpeg',
        'image/png',
        'image/gif'
    ];
    $max_file_size = 10 * 1024 * 1024; // 10 MB

    $file = $_FILES['file'];
    if (!in_array($file['type'], $allowed_file_types)) {
        unlink($image_path);
        echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido. Solo se permiten PDF, DOC, DOCX, PPT, PPTX, JPEG, PNG y GIF.']);
        exit;
    }

    if ($file['size'] > $max_file_size) {
        unlink($image_path);
        echo json_encode(['success' => false, 'message' => 'El archivo es demasiado grande. El tamaño máximo es 10 MB.']);
        exit;
    }

    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $file_name = uniqid() . '.' . $file_extension;
    $file_path = $upload_dir . $file_name;

    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
        unlink($image_path);
        echo json_encode(['success' => false, 'message' => 'Error al subir el archivo. Intenta de nuevo.']);
        exit;
    }

    $file_url = '/uploads/' . $file_name;
}

try {
    $db = conexionDB::getConexion();
    $db->beginTransaction();

    // Insertar deel documento
    $query = "INSERT INTO documentos (titulo, descripcion, autor, tipo, url_archivo, portada, fecha_publicacion, relevancia, visibilidad, grupo_id, idioma, licencia, estado, autor_id, duracion) 
              VALUES (:titulo, :descripcion, :autor, :tipo, :url_archivo, :portada, :fecha_publicacion, :relevancia, :visibilidad, :grupo_id, :idioma, :licencia, :estado, :autor_id, :duracion)";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':titulo' => $title,
        ':descripcion' => $description ?: null,
        ':autor' => $author,
        ':tipo' => $resource_type,
        ':url_archivo' => $file_url,
        ':portada' => $image_url,
        ':fecha_publicacion' => $publication_date ?: null,
        ':relevancia' => $relevance,
        ':visibilidad' => $visibility,
        ':grupo_id' => $visibility === 'Group' ? $group_id : null,
        ':idioma' => $language,
        ':licencia' => $license,
        ':estado' => $status,
        ':autor_id' => $autor_id,
        ':duracion' => $resource_type === 'video' ? $video_duration : null
    ]);

    $documento_id = $db->lastInsertId();

    // Insert categorías
    foreach ($categories as $categoria_id) {
        $query = "INSERT INTO documento_categorias (documento_id, categoria_id) VALUES (:documento_id, :categoria_id)";
        $stmt = $db->prepare($query);
        $stmt->execute([':documento_id' => $documento_id, ':categoria_id' => $categoria_id]);
    }

    // Insert etiquetas
    foreach ($tags as $tag_name) {
        $query = "SELECT id FROM etiquetas WHERE nombre = :nombre";
        $stmt = $db->prepare($query);
        $stmt->execute([':nombre' => $tag_name]);
        $etiqueta = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($etiqueta) {
            $etiqueta_id = $etiqueta['id'];
        } else {
            $query = "INSERT INTO etiquetas (nombre) VALUES (:nombre)";
            $stmt = $db->prepare($query);
            $stmt->execute([':nombre' => $tag_name]);
            $etiqueta_id = $db->lastInsertId();
        }

        $query = "INSERT INTO documento_etiqueta (documento_id, etiqueta_id) VALUES (:documento_id, :etiqueta_id)";
        $stmt = $db->prepare($query);
        $stmt->execute([':documento_id' => $documento_id, ':etiqueta_id' => $etiqueta_id]);
    }

    $db->commit();
    echo json_encode(['success' => true, 'message' => 'Recurso subido exitosamente.']);
} catch (PDOException $e) {
    $db->rollBack();
    if (file_exists($image_path)) unlink($image_path);
    if (isset($file_path) && file_exists($file_path)) unlink($file_path);
    echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos: ' . $e->getMessage()]);
}
