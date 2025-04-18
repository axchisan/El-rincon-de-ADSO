<?php
require_once "../database/conexionDB.php";

class Registro {
    private $db;

    public function __construct() {
        $this->db = conexionDB::getConexion();
    }

    public function registrarUsuario($nombre, $documento, $edad, $correo, $ficha, $contrasena) {
        if (empty($nombre) || empty($documento) || empty($edad) || empty($correo) || empty($ficha) || empty($contrasena)) {
            return ["success" => false, "message" => "Todos los campos son obligatorios"];
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return ["success" => false, "message" => "Correo electrónico inválido"];
        }

        if (!is_numeric($edad) || $edad < 0) {
            return ["success" => false, "message" => "La edad debe ser un número mayor o igual a 0"];
        }

        $contrasenaHash = password_hash($contrasena, PASSWORD_DEFAULT);

        try {
            $queryCheck = "SELECT id FROM usuarios WHERE numero_documento = :documento OR correo = :correo";
            $stmtCheck = $this->db->prepare($queryCheck);
            $stmtCheck->execute([
                ':documento' => $documento,
                ':correo' => $correo
            ]);

            if ($stmtCheck->rowCount() > 0) {
                return ["success" => false, "message" => "El documento o correo ya está registrado"];
            }

            $query = "INSERT INTO usuarios (nombre_completo, numero_documento, edad, correo, numero_ficha, contrasena, rol) 
                      VALUES (:nombre, :documento, :edad, :correo, :ficha, :contrasena, 'user')";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':nombre' => $nombre,
                ':documento' => $documento,
                ':edad' => $edad,
                ':correo' => $correo,
                ':ficha' => $ficha,
                ':contrasena' => $contrasenaHash
            ]);

            return ["success" => true, "message" => "Usuario registrado correctamente"];
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Error al registrar: " . $e->getMessage()];
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? "");
    $documento = trim($_POST["documento"] ?? "");
    $edad = trim($_POST["edad"] ?? "");
    $correo = trim($_POST["correo"] ?? "");
    $ficha = trim($_POST["ficha"] ?? "");
    $contrasena = trim($_POST["contrasena"] ?? "");

    $registro = new Registro();
    $resultado = $registro->registrarUsuario($nombre, $documento, $edad, $correo, $ficha, $contrasena);

    header("Content-Type: application/json");
    echo json_encode($resultado);
    exit;
}
?>