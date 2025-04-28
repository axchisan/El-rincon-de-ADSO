<?php
header('Content-Type: application/json');
require_once "../../database/conexionDB.php";

$response = ['success' => false, 'message' => ''];

try {
    $db = conexionDB::getConexion();

    // Verificar el método de la solicitud
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Obtener datos del cuerpo de la solicitud
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['tipo']) || !isset($data['usuario_id']) || !isset($data['relacionado_id']) || !isset($data['mensaje'])) {
        throw new Exception('Datos incompletos');
    }

    $tipo = $data['tipo'];
    $usuario_id = $data['usuario_id'];
    $relacionado_id = $data['relacionado_id'];
    $mensaje = $data['mensaje'];

    // Validar tipo de notificación
    if (!in_array($tipo, ['friend_request', 'message_received'])) {
        throw new Exception('Tipo de notificación inválido');
    }

    // Guardar la notificación en la base de datos
    $query = "INSERT INTO notificaciones (usuario_id, tipo, relacionado_id, mensaje, leida, fecha_creacion) 
              VALUES (:usuario_id, :tipo, :relacionado_id, :mensaje, FALSE, NOW())";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':usuario_id' => $usuario_id,
        ':tipo' => $tipo,
        ':relacionado_id' => $relacionado_id,
        ':mensaje' => $mensaje
    ]);

    // Obtener el token FCM del usuario
    $query = "SELECT fcm_token FROM usuarios WHERE id = :usuario_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':usuario_id' => $usuario_id]);
    $fcm_token = $stmt->fetchColumn();

    if ($fcm_token) {
        // Enviar notificación push a través de FCM
        $url = 'https://fcm.googleapis.com/fcm/send';
        $server_key = 'tu-server-key'; // Reemplaza con tu clave de servidor de FCM

        $headers = [
            'Authorization: key=' . $server_key,
            'Content-Type: application/json'
        ];

        $payload = [
            'to' => $fcm_token,
            'notification' => [
                'title' => 'Nueva Notificación - El Rincón de ADSO',
                'body' => $mensaje,
                'icon' => '/inicio/img/icono.png'
            ],
            'data' => [
                'tipo' => $tipo,
                'relacionado_id' => $relacionado_id
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $result = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($result, true);
        if (isset($result['success']) && $result['success'] == 1) {
            $response['success'] = true;
            $response['message'] = 'Notificación enviada correctamente';
        } else {
            $response['message'] = 'Error al enviar la notificación push: ' . json_encode($result);
        }
    } else {
        $response['success'] = true;
        $response['message'] = 'Notificación guardada, pero el usuario no tiene un token FCM';
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(400);
}

echo json_encode($response);
?>