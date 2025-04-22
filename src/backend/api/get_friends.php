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
  $query = "SELECT u.id, u.nombre_usuario, u.ultima_conexion
            FROM usuarios u
            INNER JOIN amistades a ON (u.id = a.usuario_id_2 OR u.id = a.usuario_id_1)
            WHERE (a.usuario_id_1 = :id OR a.usuario_id_2 = :id) AND a.estado = 'aceptada' AND u.id != :id";
  $stmt = $db->prepare($query);
  $stmt->execute([':id' => $user_id]);
  $amigos = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode($amigos);
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'message' => 'Error al obtener amigos']);
}
?>