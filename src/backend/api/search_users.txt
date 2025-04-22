<?php
session_start();
require_once "../../database/conexionDB.php";

if (!isset($_SESSION['usuario_id'])) {
  echo json_encode(['success' => false, 'message' => 'No estás autenticado']);
  exit();
}

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$user_id = $_SESSION['usuario_id'];

try {
  $db = conexionDB::getConexion();
  $query = "SELECT id, nombre_usuario 
            FROM usuarios 
            WHERE nombre_usuario LIKE :username AND id != :id 
            LIMIT 10";
  $stmt = $db->prepare($query);
  $stmt->execute([':username' => "%$username%", ':id' => $user_id]);
  $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode($users);
} catch (PDOException $e) {
  echo json_encode(['success' => false, 'message' => 'Error al buscar usuarios']);
}
?>