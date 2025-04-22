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

  // Actualizar estado de la solicitud
  $query = "UPDATE amistades SET estado = 'aceptada', fecha_aceptacion = NOW() WHERE id = :id";
  $stmt = $db->prepare($query);
  $stmt->execute([':id' => $solicitud_id]);

  // Obtener nombre del usuario que acepta
  $query = "SELECT nombre_usuario FROM usuarios WHERE id = :id";
  $stmt = $db->prepare($query);
  $stmt->execute([':id' => $user_id]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);
  $nombre_usuario = $user['nombre_usuario'];

  // Notificar al usuario que envió la solicitud
  $mensaje = "$nombre_usuario ha aceptado tu solicitud de amistad.";
  $query = "INSERT INTO notificaciones (usuario_id, tipo, mensaje, relacionado_id, leida, fecha) 
            VALUES (:user_id, 'amistad_aceptada', :mensaje, :solicitud_id, FALSE, NOW())";
  $stmt = $db->prepare($query);
  $stmt->execute([
    ':user_id' => $solicitud['usuario_id_1'],
    ':mensaje' => $mensaje,
    ':solicitud_id' => $solicitud_id
  ]);

  // Notificar a través de WebSocket
  $ws_message = json_encode([
    'type' => 'notification',
    'user_id' => $solicitud['usuario_id_1'],
    'message' => $mensaje
  ]);
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/notify');
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $ws_message);
  curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
  curl_exec($ch);
  curl_close($ch);

  echo json_encode(['success' => true, 'message' => 'Solicitud aceptada']);
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'message' => 'Error al aceptar solicitud']);
}
?>