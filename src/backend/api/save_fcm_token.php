<?php
header('Content-Type: application/json');
require_once "../../database/conexionDB.php";

$response = ['success' => false, 'message' => ''];

try {
    $db = conexionDB::getConexion();

    // Obtener datos del cuerpo de la solicitud
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['user_id']) || !isset($data['fcm_token'])) {
        throw new Exception('Datos incompletos');
    }

    $user_id = $data['user_id'];
    $fcm_token = $data['fcm_token'];

    // Actualizar el token en la base de datos
    $query = "UPDATE usuarios SET fcm_token = :fcm_token WHERE id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':fcm_token' => $fcm_token,
        ':user_id' => $user_id
    ]);

    $response['success'] = true;
    $response['message'] = 'Token guardado correctamente';

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(400);
}

echo json_encode($response);
?>