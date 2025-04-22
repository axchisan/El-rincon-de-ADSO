<?php
session_start();
require_once "../../database/conexionDB.php";

if (!isset($_SESSION['usuario_id'])) {
  echo json_encode(['success' => false, 'message' => 'No estás autenticado']);
  exit();
}

$friend_id = isset($_POST['friend_id']) ? (int)$_POST['friend_id'] : 0;
$user_id = $_SESSION['usuario_id'];

if ($friend_id === 0 || $friend_id === $user_id) {
  echo json_encode(['success' => false, 'message' => 'Usuario inválido']);
  exit();
}

try {
  $db = conexionDB::getConexion();
  
  // Verificar si ya existe una solicitud o amistad
  $query = "SELECT * FROM amistades 
            WHERE (usuario_id_1 = :user1 AND usuario_id_2 = :user2) 
            OR (usuario_id_1 = :user2 AND usuario_id_2 = :user1)";
  $stmt = $db->prepare($query);
  $stmt->execute([':user1' => $user_id, ':user2' => $friend_id]);
  $existing = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($existing) {
    echo json_encode(['success' => false, 'message' => 'Ya existe una solicitud o amistad con este usuario']);
    exit();
  }

  // Crear solicitud de amistad
  $query = "INSERT INTO amistades (usuario_id_1, usuario_id_2, estado, fecha_solicitud) 
            VALUES (:user1, :user2, 'pendiente', NOW())";
  $stmt = $db->prepare($query);
  $stmt->execute([':user1' => $user_id, ':user2' => $friend_id]);

  // Obtener nombre del usuario que envía la solicitud
  $query = "SELECT nombre_usuario FROM usuarios WHERE id = :id";
  $stmt = $db->prepare($query);
  $stmt->execute([':id' => $user_id]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);
  $nombre_usuario = $user['nombre_usuario'];

  // Crear notificación
  $mensaje = "$nombre_usuario te ha enviado una solicitud de amistad.";
  $query = "INSERT INTO notificaciones (usuario_id, tipo, mensaje, relacionado_id, leida, fecha) 
            VALUES (:user_id, 'solicitud_amistad', :mensaje, :solicitud_id, FALSE, NOW())";
  $stmt = $db->prepare($query);
  $stmt->execute([
    ':user_id' => $friend_id,
    ':mensaje' => $mensaje,
    ':solicitud_id' => $db->lastInsertId()
  ]);

  // Notificar a través de WebSocket
  $ws_message = json_encode([
    'type' => 'notification',
    'user_id' => $friend_id,
    'message' => $mensaje
  ]);
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/notify');
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $ws_message);
  curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
  curl_exec($ch);
  curl_close($ch);

  echo json_encode(['success' => true, 'message' => 'Solicitud de amistad enviada']);
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'message' => 'Error al enviar solicitud']);
}
?>