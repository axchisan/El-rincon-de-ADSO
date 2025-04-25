<?php
session_start();
require_once "../../database/conexionDB.php";

header('Content-Type: application/json');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para comentar.']);
    exit;
}

// Verificar si se recibieron los datos necesarios
if (!isset($_POST['documento_id']) || !isset($_POST['comentario']) || empty($_POST['comentario'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos.']);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$documento_id = intval($_POST['documento_id']);
$contenido = trim($_POST['comentario']);

try {
    $db = conexionDB::getConexion();
    
    // Verificar si el documento existe en la tabla documentos
    $query = "SELECT id, titulo FROM documentos WHERE id = :documento_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':documento_id' => $documento_id]);
    $documento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$documento) {
        echo json_encode(['success' => false, 'message' => 'El recurso no existe.']);
        exit;
    }
    
    // Crear una publicación para este documento si no existe
    $query = "SELECT id FROM publicaciones WHERE titulo = :titulo AND autor_id = :autor_id";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':titulo' => $documento['titulo'],
        ':autor_id' => $usuario_id
    ]);
    $publicacion = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $publicacion_id = null;
    
    if ($publicacion) {
        // Si existe una publicación asociada, usar ese ID
        $publicacion_id = $publicacion['id'];
    } else {
        // Si no existe, crear una nueva entrada en la tabla publicaciones
        $query = "INSERT INTO publicaciones (titulo, contenido, autor_id, fecha_creacion) 
                  VALUES (:titulo, :contenido, :autor_id, NOW()) RETURNING id";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':titulo' => $documento['titulo'],
            ':contenido' => "Comentarios para el documento ID: " . $documento_id,
            ':autor_id' => $usuario_id
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $publicacion_id = $result['id'];
    }
    
    // Insertar el comentario usando el ID de publicación correcto
    $query = "INSERT INTO comentarios (autor_id, publicacion_id, contenido, fecha_creacion) 
              VALUES (:autor_id, :publicacion_id, :contenido, NOW())";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':autor_id' => $usuario_id,
        ':publicacion_id' => $publicacion_id,
        ':contenido' => $contenido
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Comentario añadido correctamente.']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al añadir el comentario: ' . $e->getMessage()]);
}
?>

