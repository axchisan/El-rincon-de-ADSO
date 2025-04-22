<?php
session_start();
ini_set('session.gc_maxlifetime', 3600);
session_set_cookie_params(3600, '/');
require_once "../../database/conexionDB.php";

// Configurar el encabezado para devolver JSON
header('Content-Type: application/json');

// Función para enviar respuestas JSON
function sendResponse($status, $data, $message = '') {
  echo json_encode([
    'status' => $status,
    'data' => $data,
    'message' => $message
  ]);
  exit();
}

try {
  $db = conexionDB::getConexion();
  $user_id = isset($_SESSION['usuario_id']) ? (int)$_SESSION['usuario_id'] : null;

  // Verificar si el usuario está autenticado
  if (!$user_id) {
    sendResponse('error', null, 'Usuario no autenticado');
  }

  $method = $_SERVER['REQUEST_METHOD'];

  if ($method === 'GET') {
    // Endpoint: GET /api/mensajes?friend_id=ID
    // Devuelve los mensajes entre el usuario actual y el amigo especificado
    if (!isset($_GET['friend_id']) || !is_numeric($_GET['friend_id'])) {
      sendResponse('error', null, 'ID de amigo no proporcionado o inválido');
    }

    $friend_id = (int)$_GET['friend_id'];

    // Verificar que el usuario es un amigo
    $query = "SELECT COUNT(*) 
              FROM amistades 
              WHERE ((usuario_id = :user_id AND amigo_id = :friend_id) 
                     OR (usuario_id = :friend_id AND amigo_id = :user_id)) 
              AND estado = 'accepted'";
    $stmt = $db->prepare($query);
    $stmt->execute([
      ':user_id' => $user_id,
      ':friend_id' => $friend_id
    ]);
    $is_friend = $stmt->fetchColumn();

    if ($is_friend == 0) {
      sendResponse('error', null, 'El usuario no es un amigo');
    }

    // Obtener mensajes
    $query = "SELECT m.id, m.remitente_id, m.destinatario_id, m.contenido, m.fecha_envio, m.leido, 
                     u.nombre_usuario AS remitente_nombre 
              FROM mensajes m 
              JOIN usuarios u ON m.remitente_id = u.id 
              WHERE (m.remitente_id = :user_id AND m.destinatario_id = :friend_id) 
                 OR (m.remitente_id = :friend_id AND m.destinatario_id = :user_id) 
              ORDER BY m.fecha_envio ASC";
    $stmt = $db->prepare($query);
    $stmt->execute([
      ':user_id' => $user_id,
      ':friend_id' => $friend_id
    ]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Marcar mensajes como leídos (los recibidos por el usuario actual)
    $query = "UPDATE mensajes 
              SET leido = TRUE 
              WHERE remitente_id = :friend_id AND destinatario_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->execute([
      ':friend_id' => $friend_id,
      ':user_id' => $user_id
    ]);

    sendResponse('success', $messages);
  }

  if ($method === 'POST') {
    // Endpoint: POST /api/mensajes
    // Inserta un nuevo mensaje
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['friend_id']) || !isset($data['content']) || empty(trim($data['content']))) {
      sendResponse('error', null, 'Datos incompletos o inválidos');
    }

    $friend_id = (int)$data['friend_id'];
    $content = trim($data['content']);

    // Verificar que el usuario es un amigo
    $query = "SELECT COUNT(*) 
              FROM amistades 
              WHERE ((usuario_id = :user_id AND amigo_id = :friend_id) 
                     OR (usuario_id = :friend_id AND amigo_id = :user_id)) 
              AND estado = 'accepted'";
    $stmt = $db->prepare($query);
    $stmt->execute([
      ':user_id' => $user_id,
      ':friend_id' => $friend_id
    ]);
    $is_friend = $stmt->fetchColumn();

    if ($is_friend == 0) {
      sendResponse('error', null, 'El usuario no es un amigo');
    }

    // Insertar el mensaje
    $query = "INSERT INTO mensajes (remitente_id, destinatario_id, contenido) 
              VALUES (:sender_id, :receiver_id, :content)";
    $stmt = $db->prepare($query);
    $stmt->execute([
      ':sender_id' => $user_id,
      ':receiver_id' => $friend_id,
      ':content' => $content
    ]);

    // Obtener el nombre del usuario actual para la notificación
    $query = "SELECT nombre_usuario FROM usuarios WHERE id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':user_id' => $user_id]);
    $sender = $stmt->fetch(PDO::FETCH_ASSOC);
    $sender_name = $sender['nombre_usuario'];

    // Generar notificación para el amigo
    $query = "INSERT INTO notificaciones (usuario_id, tipo, relacionado_id, mensaje) 
              VALUES (:receiver_id, 'new_message', :sender_id, :message)";
    $stmt = $db->prepare($query);
    $stmt->execute([
      ':receiver_id' => $friend_id,
      ':sender_id' => $user_id,
      ':message' => "Tienes un nuevo mensaje de $sender_name."
    ]);

    sendResponse('success', null, 'Mensaje enviado correctamente');
  }

  // Si el método no es GET ni POST
  sendResponse('error', null, 'Método no permitido');

} catch (Exception $e) {
  error_log("Error en la API: " . $e->getMessage());
  sendResponse('error', null, 'Error del servidor');
}