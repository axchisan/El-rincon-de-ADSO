<?php
session_start();
require_once "../../database/conexionDB.php";

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para registrar una vista.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$documento_id = $_POST['documento_id'] ?? null;

if (empty($documento_id)) {
    echo json_encode(['success' => false, 'message' => 'El ID del recurso es obligatorio.']);
    exit;
}

try {
    $db = conexionDB::getConexion();
    $usuario_id = $_SESSION['usuario_id'];

    // Verificar si el recurso ya está en vistos recientemente
    $query = "SELECT COUNT(*) FROM recientemente_vistos WHERE usuario_id = :usuario_id AND documento_id = :documento_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':usuario_id' => $usuario_id, ':documento_id' => $documento_id]);
    $exists = $stmt->fetchColumn();

    if ($exists) {
        // Actualizar la fecha de visualización
        $query = "UPDATE recientemente_vistos 
                  SET fecha_vista = CURRENT_TIMESTAMP 
                  WHERE usuario_id = :usuario_id AND documento_id = :documento_id";
    } else {
        // Insertar nueva entrada
        $query = "INSERT INTO recientemente_vistos (usuario_id, documento_id, fecha_vista) 
                  VALUES (:usuario_id, :documento_id, CURRENT_TIMESTAMP)";
    }

    $stmt = $db->prepare($query);
    $stmt->execute([':usuario_id' => $usuario_id, ':documento_id' => $documento_id]);

    echo json_encode(['success' => true, 'message' => 'Vista registrada.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al registrar la vista: ' . $e->getMessage()]);
}
?>