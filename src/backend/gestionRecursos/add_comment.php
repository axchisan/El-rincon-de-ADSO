<?php
session_start();
require_once "../../database/conexionDB.php";

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['usuario_id'])) {
    $response['message'] = 'Debes iniciar sesión para comentar.';
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método no permitido.';
    echo json_encode($response);
    exit();
}

$documento_id = isset($_POST['documento_id']) ? intval($_POST['documento_id']) : 0;
$contenido = isset($_POST['contenido']) ? trim($_POST['contenido']) : '';
$autor_id = $_SESSION['usuario_id'];

if ($documento_id <= 0 || empty($contenido)) {
    $response['message'] = 'Datos inválidos.';
    echo json_encode($response);
    exit();
}

try {
    $db = conexionDB::getConexion();

    // Insertar el comentario
    $query = "
        INSERT INTO comentarios (documento_id, autor_id, contenido, fecha_creacion)
        VALUES (:documento_id, :autor_id, :contenido, NOW())
        RETURNING id, fecha_creacion
    ";
    $stmt = $db->prepare($query);
    $stmt->execute([
        ':documento_id' => $documento_id,
        ':autor_id' => $autor_id,
        ':contenido' => $contenido
    ]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Obtener el nombre del autor para la respuesta
    $query = "SELECT nombre_usuario FROM usuarios WHERE id = :autor_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':autor_id' => $autor_id]);
    $autor_nombre = $stmt->fetchColumn();

    $response['success'] = true;
    $response['message'] = 'Comentario agregado exitosamente.';
    $response['comentario_id'] = $result['id'];
    $response['autor_nombre'] = $autor_nombre;
    $response['fecha_creacion'] = date('d/m/Y H:i', strtotime($result['fecha_creacion']));
} catch (PDOException $e) {
    $response['message'] = 'Error al agregar el comentario: ' . $e->getMessage();
}

echo json_encode($response);
?>