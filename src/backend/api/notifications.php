<?php
session_start();
require_once "../../database/conexionDB.php";

if (!isset($_SESSION['usuario_id'])) {
  echo json_encode(['success' => false, 'message' => 'No estás autenticado']);
  exit();
}

$user_id = $_SESSION['usuario_id'];

try {
  $db = conexionDB::getConexion();
  $query = "SELECT id, tipo, mensaje, relacionado_id, leida, fecha 
            FROM notificaciones 
            WHERE usuario_id = :id 
            ORDER BY fecha DESC 
            LIMIT 20";
  $stmt = $db->prepare($query);
  $stmt->execute([':id' => $user_id]);
  $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Marcar notificaciones como leídas al visualizarlas
  $query = "UPDATE notificaciones SET leida = TRUE WHERE usuario_id = :id AND leida = FALSE";
  $stmt = $db->prepare($query);
  $stmt->execute([':id' => $user_id]);

  echo json_encode($notificaciones);
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'message' => 'Error al obtener notificaciones']);
}
?>