<?php
session_start();
require_once "../../database/conexionDB.php";

if (!isset($_SESSION['usuario_id'])) {
  echo json_encode(['success' => false, 'message' => 'No estás autenticado']);
  exit();
}

$user_id = $_SESSION['usuario_id'];

try {
  $db = conexionDB::getConexion();
  $query = "SELECT a.id AS solicitud_id, u.id AS usuario_id, u.nombre_usuario
            FROM amistades a
            INNER JOIN usuarios u ON u.id = a.usuario_id_1
            WHERE a.usuario_id_2 = :id AND a.estado = 'pendiente'";
  $stmt = $db->prepare($query);
  $stmt->execute([':id' => $user_id]);
  $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode($solicitudes);
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'message' => 'Error al obtener solicitudes']);
}
?>