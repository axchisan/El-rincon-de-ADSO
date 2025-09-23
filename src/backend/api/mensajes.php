<?php
header('Content-Type: application/json');
session_start();
require_once "../../database/conexionDB.php";

if (!isset($_SESSION['usuario_id'])) {
  echo json_encode(['status' => 'error', 'message' => 'No estás autenticado']);
  exit();
}

try {
  $db = conexionDB::getConexion();
  $user_id = $_SESSION['usuario_id'];
  $method = $_SERVER['REQUEST_METHOD'];

  if ($method === 'GET') {
    // Obtener mensajes entre el usuario y un amigo
    if (!isset($_GET['friend_id']) || !is_numeric($_GET['friend_id'])) {
      echo json_encode(['status' => 'error', 'message' => 'Friend ID no proporcionado o inválido']);
      exit();
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
      echo json_encode(['status' => 'error', 'message' => 'El usuario no es un amigo']);
      exit();
    }

    // Obtener mensajes
    $query = "SELECT id, remitente_id, destinatario_id, contenido, fecha_envio 
              FROM mensajes 
              WHERE (remitente_id = :user_id AND destinatario_id = :friend_id) 
                 OR (remitente_id = :friend_id AND destinatario_id = :user_id) 
              ORDER BY fecha_envio ASC";
    $stmt = $db->prepare($query);
    $stmt->execute([
      ':user_id' => $user_id,
      ':friend_id' => $friend_id
    ]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $messages]);
    exit();

  } elseif ($method === 'POST') {
    // Enviar un nuevo mensaje
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['friend_id']) || !isset($data['content']) || empty(trim($data['content']))) {
      echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
      exit();
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
      echo json_encode(['status' => 'error', 'message' => 'El usuario no es un amigo']);
      exit();
    }

    // Insertar el mensaje
    $query = "INSERT INTO mensajes (remitente_id, destinatario_id, contenido, fecha_envio) 
              VALUES (:remitente_id, :destinatario_id, :contenido, CURRENT_TIMESTAMP)";
    $stmt = $db->prepare($query);
    $stmt->execute([
      ':remitente_id' => $user_id,
      ':destinatario_id' => $friend_id,
      ':contenido' => $content
    ]);

    // Generar notificación para el amigo
    $query = "SELECT nombre_usuario FROM usuarios WHERE id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':user_id' => $user_id]);
    $sender = $stmt->fetch(PDO::FETCH_ASSOC);
    $sender_name = $sender['nombre_usuario'];

    $query = "INSERT INTO notificaciones (usuario_id, tipo, relacionado_id, mensaje) 
              VALUES (:usuario_id, 'new_message', :relacionado_id, :mensaje)";
    $stmt = $db->prepare($query);
    $stmt->execute([
      ':usuario_id' => $friend_id,
      ':relacionado_id' => $user_id,
      ':mensaje' => "Tienes un nuevo mensaje de $sender_name."
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Mensaje enviado']);
    exit();

  } elseif ($method === 'PUT') {
    // Editar un mensaje existente
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['message_id']) || !isset($data['content']) || empty(trim($data['content']))) {
      echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
      exit();
    }

    $message_id = (int)$data['message_id'];
    $new_content = trim($data['content']);

    // Verificar que el mensaje pertenece al usuario y está en una conversación válida
    $query = "SELECT remitente_id, destinatario_id 
              FROM mensajes 
              WHERE id = :message_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':message_id' => $message_id]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$message) {
      echo json_encode(['status' => 'error', 'message' => 'Mensaje no encontrado']);
      exit();
    }

    if ($message['remitente_id'] != $user_id) {
      echo json_encode(['status' => 'error', 'message' => 'No tienes permiso para editar este mensaje']);
      exit();
    }

    $friend_id = $message['destinatario_id'];

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
      echo json_encode(['status' => 'error', 'message' => 'El usuario no es un amigo']);
      exit();
    }

    // Actualizar el mensaje
    $query = "UPDATE mensajes 
              SET contenido = :contenido, fecha_envio = CURRENT_TIMESTAMP 
              WHERE id = :message_id";
    $stmt = $db->prepare($query);
    $stmt->execute([
      ':contenido' => $new_content,
      ':message_id' => $message_id
    ]);

    echo json_encode(['status' => 'success', 'message' => 'Mensaje editado']);
    exit();

  } elseif ($method === 'DELETE') {
    // Borrar un mensaje
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['message_id'])) {
      echo json_encode(['status' => 'error', 'message' => 'Mensaje ID no proporcionado']);
      exit();
    }

    $message_id = (int)$data['message_id'];

    // Verificar que el mensaje pertenece al usuario y está en una conversación válida
    $query = "SELECT remitente_id, destinatario_id 
              FROM mensajes 
              WHERE id = :message_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':message_id' => $message_id]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$message) {
      echo json_encode(['status' => 'error', 'message' => 'Mensaje no encontrado']);
      exit();
    }

    if ($message['remitente_id'] != $user_id) {
      echo json_encode(['status' => 'error', 'message' => 'No tienes permiso para borrar este mensaje']);
      exit();
    }

    $friend_id = $message['destinatario_id'];

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
      echo json_encode(['status' => 'error', 'message' => 'El usuario no es un amigo']);
      exit();
    }

    // Borrar el mensaje
    $query = "DELETE FROM mensajes WHERE id = :message_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':message_id' => $message_id]);

    echo json_encode(['status' => 'success', 'message' => 'Mensaje borrado']);
    exit();

  } else {
    echo json_encode(['status' => 'error', 'message' => 'Método no soportado']);
    exit();
  }

} catch (PDOException $e) {
  error_log("Error de base de datos: " . $e->getMessage());
  echo json_encode(['status' => 'error', 'message' => 'Error en la base de datos']);
  exit();
}
?>