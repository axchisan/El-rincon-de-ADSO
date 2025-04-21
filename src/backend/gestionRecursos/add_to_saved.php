<?php
session_start();
require_once "../../database/conexionDB.php";

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para guardar un recurso.']);
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

    // Verificar si el recurso ya está guardado
    $query = "SELECT COUNT(*) FROM guardados WHERE usuario_id = :usuario_id AND documento_id = :documento_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':usuario_id' => $usuario_id, ':documento_id' => $documento_id]);
    $exists = $stmt->fetchColumn();

    if ($exists) {
        echo json_encode(['success' => false, 'message' => 'El recurso ya está guardado.']);
        exit;
    }

    // Guardar el recurso
    $query = "INSERT INTO guardados (usuario_id, documento_id, fecha_guardado) 
              VALUES (:usuario_id, :documento_id, CURRENT_TIMESTAMP)";
    $stmt = $db->prepare($query);
    $stmt->execute([':usuario_id' => $usuario_id, ':documento_id' => $documento_id]);

    echo json_encode(['success' => true, 'message' => 'Recurso guardado.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al guardar el recurso: ' . $e->getMessage()]);
}
?>