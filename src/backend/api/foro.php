<?php
header('Content-Type: application/json');

// Establecer configuraciones de la sesión ANTES de iniciarla
ini_set('session.gc_maxlifetime', 3600);
session_set_cookie_params(3600, '/');

// Iniciar la sesión
session_start();
require_once "../../database/conexionDB.php";

$response = ['status' => 'error', 'message' => '', 'data' => []];

try {
    if (!isset($_SESSION['usuario_id'])) {
        $response['message'] = 'Sesión no encontrada';
        echo json_encode($response);
        exit();
    }

    $db = conexionDB::getConexion();
    $user_id = $_SESSION['usuario_id'];

    // Manejar solicitudes POST (acciones como crear tema o mensaje)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';

        if ($action === 'create_topic') {
            $title = $input['title'] ?? '';
            $content = $input['content'] ?? '';
            if (empty($title) || empty($content)) {
                $response['message'] = 'Título y contenido son obligatorios';
                echo json_encode($response);
                exit();
            }

            $query = "INSERT INTO temas_foro (usuario_id, titulo, contenido) VALUES (:usuario_id, :titulo, :contenido) RETURNING id";
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':usuario_id' => $user_id,
                ':titulo' => $title,
                ':contenido' => $content
            ]);
            $topic_id = $stmt->fetchColumn();

            $response['status'] = 'success';
            $response['message'] = 'Tema creado con éxito';
            $response['data'] = ['topic_id' => $topic_id];
        } elseif ($action === 'post_message') {
            $topic_id = $input['topic_id'] ?? 0;
            $content = $input['content'] ?? '';
            if (empty($topic_id) || empty($content)) {
                $response['message'] = 'Tema y contenido son obligatorios';
                echo json_encode($response);
                exit();
            }

            $query = "INSERT INTO mensajes_foro (tema_id, usuario_id, contenido) VALUES (:tema_id, :usuario_id, :contenido) RETURNING id";
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':tema_id' => $topic_id,
                ':usuario_id' => $user_id,
                ':contenido' => $content
            ]);
            $message_id = $stmt->fetchColumn();

            $response['status'] = 'success';
            $response['message'] = 'Mensaje enviado con éxito';
            $response['data'] = ['message_id' => $message_id];
        } else {
            $response['message'] = 'Acción no válida';
        }

        echo json_encode($response);
        exit();
    }

    // Manejar solicitudes GET
    $topic_id = $_GET['topic_id'] ?? null;

    if ($topic_id) {
        // Obtener mensajes de un tema específico
        $query = "SELECT m.id, m.contenido, m.fecha_creacion, u.nombre_usuario AS autor 
                  FROM mensajes_foro m 
                  JOIN usuarios u ON m.usuario_id = u.id 
                  WHERE m.tema_id = :tema_id 
                  ORDER BY m.fecha_creacion ASC";
        $stmt = $db->prepare($query);
        $stmt->execute([':tema_id' => $topic_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response['status'] = 'success';
        $response['data'] = ['messages' => $messages];
    } else {
        // Obtener todos los temas
        $query = "SELECT t.id, t.titulo, t.fecha_creacion, u.nombre_usuario AS autor 
                  FROM temas_foro t 
                  JOIN usuarios u ON t.usuario_id = u.id 
                  ORDER BY t.fecha_creacion DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
        $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response['status'] = 'success';
        $response['data'] = ['topics' => $topics];
    }
} catch (PDOException $e) {
    $response['message'] = 'Error de base de datos: ' . $e->getMessage();
}

echo json_encode($response);
exit();