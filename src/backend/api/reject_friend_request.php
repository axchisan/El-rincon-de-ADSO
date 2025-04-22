<?php
session_start();
require_once "../../database/conexionDB.php";

if (!isset($_SESSION['usuario_id'])) {
  echo json_encode(['success' => false, 'message' => 'No estás autenticado']);
  exit();
}

$solicitud_id = isset($_POST['solicitud_id']) ? (int)$_POST['solicitud_id'] : 0;
$user_id = $_SESSION['usuario_id'];

if ($solicitud_id === 0) {
  echo json_encode(['success' => false, 'message' => 'Solicitud inválida']);
  exit();
}

try {
  $db = conexionDB::getConexion();

  // Verificar que la solicitud sea para el usuario actual
  $query = "SELECT usuario_id_1 FROM amistades WHERE id = :id AND usuario_id_2 = :user_id AND estado = 'pendiente'";
  $stmt = $db->prepare($query);
  $stmt->execute([':id' => $solicitud_id, ':user_id' => $user_id]);
  $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$solicitud) {
    echo json_encode(['success' => false, 'message' => 'Solicitud no encontrada o no autorizada']);
    exit();
  }

  // Eliminar la solicitud
  $query = "DELETE FROM amistades WHERE id = :id";
  $stmt = $db->prepare($query);
  $stmt->execute([':id' => $solicitud_id]);

  echo json_encode(['success' => true, 'message' => 'Solicitud rechazada']);
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'message' => 'Error al rechazar solicitud']);
}
?>