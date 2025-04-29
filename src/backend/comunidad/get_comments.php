<?php
session_start();
require_once "../../database/conexionDB.php";

header('Content-Type: application/json');

$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : 0;

try {
    $db = conexionDB::getConexion();
    $query = "
        SELECT cc.id, cc.libro, cc.comentario, cc.valoracion, cc.likes, cc.fecha_creacion, 
               u.nombre_usuario, u.imagen,
               (SELECT COUNT(*) FROM likes_comentarios_comunidad lcc WHERE lcc.comentario_id = cc.id AND lcc.usuario_id = :usuario_id) as user_liked
        FROM comentarios_comunidad cc
        JOIN usuarios u ON cc.usuario_id = u.id
        ORDER BY cc.fecha_creacion DESC
        LIMIT 4
    ";
    $stmt = $db->prepare($query);
    $stmt->execute([':usuario_id' => $usuario_id]);
    $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Procesar los datos para el frontend
    $result = [];
    foreach ($comentarios as $comentario) {
        $avatar = $comentario['imagen'] ? "../../backend/perfil/" . htmlspecialchars($comentario['imagen']) : "https://i.pravatar.cc/150?img=" . $comentario['usuario_id'];

        // Obtener respuestas
        $query_replies = "
            SELECT r.id, r.respuesta, r.fecha_creacion, u.nombre_usuario, u.imagen
            FROM respuestas_comentarios_comunidad r
            JOIN usuarios u ON r.usuario_id = u.id
            WHERE r.comentario_id = :comentario_id
            ORDER BY r.fecha_creacion ASC
        ";
        $stmt_replies = $db->prepare($query_replies);
        $stmt_replies->execute([':comentario_id' => $comentario['id']]);
        $respuestas = $stmt_replies->fetchAll(PDO::FETCH_ASSOC);

        $respuestas_data = [];
        foreach ($respuestas as $respuesta) {
            $reply_avatar = $respuesta['imagen'] ? "../../backend/perfil/" . htmlspecialchars($respuesta['imagen']) : "https://i.pravatar.cc/150?img=" . $respuesta['id'];
            $respuestas_data[] = [
                'id' => $respuesta['id'],
                'respuesta' => htmlspecialchars($respuesta['respuesta']),
                'fecha_creacion' => $respuesta['fecha_creacion'],
                'nombre_usuario' => htmlspecialchars($respuesta['nombre_usuario']),
                'avatar' => $reply_avatar
            ];
        }

        $result[] = [
            'id' => $comentario['id'],
            'libro' => htmlspecialchars($comentario['libro']),
            'comentario' => htmlspecialchars($comentario['comentario']),
            'valoracion' => (int)$comentario['valoracion'],
            'likes' => (int)$comentario['likes'],
            'fecha_creacion' => $comentario['fecha_creacion'],
            'nombre_usuario' => htmlspecialchars($comentario['nombre_usuario']),
            'avatar' => $avatar,
            'user_liked' => $comentario['user_liked'] > 0,
            'respuestas' => $respuestas_data
        ];
    }

    echo json_encode($result);
} catch (PDOException $e) {
    error_log("Error al obtener comentarios de la comunidad: " . $e->getMessage());
    echo json_encode(['error' => 'Error al cargar los comentarios.']);
}
?>