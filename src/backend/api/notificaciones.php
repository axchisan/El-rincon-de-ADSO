<?php
header('Content-Type: application/json');

ini_set('session.gc_maxlifetime', 3600);
session_set_cookie_params(3600, '/');


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

    // Manejar solicitudes POST (acciones como marcar como leída)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';

        if ($action === 'mark_as_read') {
            $notification_id = $input['notification_id'] ?? 0;
            $query = "UPDATE notificaciones SET leida = TRUE WHERE id = :notification_id AND usuario_id = :user_id";
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':notification_id' => $notification_id,
                ':user_id' => $user_id
            ]);
            $response['status'] = 'success';
            $response['message'] = 'Notificación marcada como leída';
        } elseif ($action === 'mark_all_as_read') {
            $query = "UPDATE notificaciones SET leida = TRUE WHERE usuario_id = :user_id AND leida = FALSE";
            $stmt = $db->prepare($query);
            $stmt->execute([':user_id' => $user_id]);
            $response['status'] = 'success';
            $response['message'] = 'Todas las notificaciones marcadas como leídas';
        } else {
            $response['message'] = 'Acción no válida';
        }

        echo json_encode($response);
        exit();
    }

    // Manejar solicitudes GET (obtener notificaciones)
    // Contar notificaciones no leídas
    $query = "SELECT COUNT(*) FROM notificaciones WHERE usuario_id = :user_id AND leida = FALSE";
    $stmt = $db->prepare($query);
    $stmt->execute([':user_id' => $user_id]);
    $unread_count = $stmt->fetchColumn();

    // Obtener todas las notificaciones del usuario
    $query = "SELECT n.id, n.tipo, n.relacionado_id, n.mensaje, n.leida, n.fecha_creacion, u.nombre_usuario AS relacionado_nombre 
              FROM notificaciones n 
              LEFT JOIN usuarios u ON n.relacionado_id = u.id 
              WHERE n.usuario_id = :user_id 
              ORDER BY n.fecha_creacion DESC";
    $stmt = $db->prepare($query);
    $stmt->execute([':user_id' => $user_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['status'] = 'success';
    $response['data'] = [
        'notifications' => $notifications,
        'unread_count' => $unread_count
    ];
} catch (PDOException $e) {
    $response['message'] = 'Error de base de datos: ' . $e->getMessage();
}

echo json_encode($response);
exit();