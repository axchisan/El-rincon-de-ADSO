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
$comentario = trim($_POST['comentario']);

try {
    $db = conexionDB::getConexion();
    
    // Verificar si el documento existe
    $query = "SELECT id FROM documentos WHERE id = :documento_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':documento_id' => $documento_id]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'El recurso no existe.']);
        exit;
    }
    
    // Insertar el comentario
    $query = "INSERT INTO comentarios (autor_id, publicacion_id, contenido, fecha_creacion) 
              VALUES (:autor_id, :publicacion_id, :contenido, :fecha_creacion NOW())";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':autor_id' => $autor_id,
        ':documento_id' => $documento_id,
        ':contenido' => $comentario
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Comentario añadido correctamente.']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al añadir el comentario: ' . $e->getMessage()]);
}
?>
