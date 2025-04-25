<?php
session_start();
require_once "../../database/conexionDB.php";

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para editar un comentario.']);
    exit();
}

// Verificar si se proporcionaron los datos necesarios
if (!isset($_POST['comentario_id']) || !isset($_POST['contenido'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos.']);
    exit();
}

$comentario_id = intval($_POST['comentario_id']);
$contenido = trim($_POST['contenido']);
$usuario_id = $_SESSION['usuario_id'];

// Validar que el contenido no esté vacío
if (empty($contenido)) {
    echo json_encode(['success' => false, 'message' => 'El comentario no puede estar vacío.']);
    exit();
}

try {
    $db = conexionDB::getConexion();
    
    // Verificar que el comentario pertenezca al usuario
    $query = "SELECT autor_id FROM comentarios WHERE id = :comentario_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':comentario_id' => $comentario_id]);
    $comentario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$comentario) {
        echo json_encode(['success' => false, 'message' => 'El comentario no existe.']);
        exit();
    }
    
    if ($comentario['autor_id'] != $usuario_id) {
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para editar este comentario.']);
        exit();
    }
    
    // Actualizar el comentario
    $query = "UPDATE comentarios SET contenido = :contenido, fecha_edicion = NOW() WHERE id = :comentario_id";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':contenido' => $contenido,
        ':comentario_id' => $comentario_id
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Comentario actualizado correctamente.']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el comentario: ' . $e->getMessage()]);
}
?>
