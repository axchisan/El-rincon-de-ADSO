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

    // Manejar solicitudes POST (acciones como crear evento o inscribirse)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';

        if ($action === 'create_event') {
            $title = $input['title'] ?? '';
            $description = $input['description'] ?? null;
            $start = $input['start'] ?? '';
            $end = $input['end'] ?? null;
            $location = $input['location'] ?? null;

            if (empty($title) || empty($start)) {
                $response['message'] = 'Título y fecha de inicio son obligatorios';
                echo json_encode($response);
                exit();
            }

            $query = "INSERT INTO eventos (organizador_id, titulo, descripcion, fecha_inicio, fecha_fin, lugar) 
                      VALUES (:organizador_id, :titulo, :descripcion, :fecha_inicio, :fecha_fin, :lugar) RETURNING id";
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':organizador_id' => $user_id,
                ':titulo' => $title,
                ':descripcion' => $description,
                ':fecha_inicio' => $start,
                ':fecha_fin' => $end,
                ':lugar' => $location
            ]);
            $event_id = $stmt->fetchColumn();

            $response['status'] = 'success';
            $response['message'] = 'Evento creado con éxito';
            $response['data'] = ['event_id' => $event_id];
        } elseif ($action === 'register') {
            $event_id = $input['event_id'] ?? 0;
            if (empty($event_id)) {
                $response['message'] = 'ID del evento es obligatorio';
                echo json_encode($response);
                exit();
            }

            $query = "INSERT INTO inscripciones_eventos (evento_id, usuario_id) VALUES (:evento_id, :usuario_id) RETURNING id";
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':evento_id' => $event_id,
                ':usuario_id' => $user_id
            ]);
            $inscription_id = $stmt->fetchColumn();

            $response['status'] = 'success';
            $response['message'] = 'Inscripción realizada con éxito';
            $response['data'] = ['inscription_id' => $inscription_id];
        } elseif ($action === 'unregister') {
            $event_id = $input['event_id'] ?? 0;
            if (empty($event_id)) {
                $response['message'] = 'ID del evento es obligatorio';
                echo json_encode($response);
                exit();
            }

            $query = "DELETE FROM inscripciones_eventos WHERE evento_id = :evento_id AND usuario_id = :usuario_id";
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':evento_id' => $event_id,
                ':usuario_id' => $user_id
            ]);

            $response['status'] = 'success';
            $response['message'] = 'Inscripción cancelada con éxito';
        } else {
            $response['message'] = 'Acción no válida';
        }

        echo json_encode($response);
        exit();
    }

    // Manejar solicitudes GET (obtener lista de eventos)
    $query = "SELECT e.id, e.titulo, e.descripcion, e.fecha_inicio, e.fecha_fin, e.lugar, e.fecha_creacion, 
                     u.nombre_usuario AS organizador,
                     EXISTS (
                         SELECT 1 FROM inscripciones_eventos ie 
                         WHERE ie.evento_id = e.id AND ie.usuario_id = :user_id
                     ) AS is_registered
              FROM eventos e 
              JOIN usuarios u ON e.organizador_id = u.id 
              WHERE e.fecha_inicio >= CURRENT_TIMESTAMP 
              ORDER BY e.fecha_inicio ASC";
    $stmt = $db->prepare($query);
    $stmt->execute([':user_id' => $user_id]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['status'] = 'success';
    $response['data'] = ['events' => $events];
} catch (PDOException $e) {
    $response['message'] = 'Error de base de datos: ' . $e->getMessage();
}

echo json_encode($response);
exit();