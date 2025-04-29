<?php
session_start();
require_once "../../database/conexionDB.php";

session_regenerate_id(true);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_usuario = trim($_POST['usuario'] ?? '');
    $contrasena = trim($_POST['clave'] ?? '');

    if (empty($input_usuario) || empty($contrasena)) {
        $error = "Por favor, completa todos los campos.";
    } else {
        // Validación de la estructura de la contraseña
        $regex = '/^(?=(?:.*\d){3,})(?=.*[A-Z])(?=.*[!@#$%^&*()_+{}\[\]:;<>,.?~\\/-]).{6,}$/';
        if (!preg_match($regex, $contrasena)) {
            $error = "La contraseña debe tener al menos una letra mayúscula, tres números y un carácter especial. Mínimo 6 caracteres.";
        } else {
            try {
                $db = conexionDB::getConexion();

                $sql = "SELECT id, nombre_usuario, correo, contrasena, rol FROM usuarios WHERE nombre_usuario = :input OR correo = :input";
                $stmt = $db->prepare($sql);
                $stmt->execute([':input' => $input_usuario]);
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($usuario && password_verify($contrasena, $usuario['contrasena'])) {
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];
                    $_SESSION['rol'] = $usuario['rol'];

                    // Redirigir según el rol
                    if ($usuario['rol'] === "user") {
                        header("Location: ../../frontend/inicio/index.php");
                    } else if ($usuario['rol'] === "admin") {
                        header("Location: ../../frontend/inicio/panel-admin.php");
                    }
                    exit();
                } else {
                    $error = "Usuario, correo o contraseña incorrectos.";
                }
            } catch (PDOException $e) {
                $error = "Error al iniciar sesión: " . $e->getMessage();
            }
        }
    }

    $_SESSION['error'] = $error;
    header("Location: ../../frontend/login/login.php");
    exit();
}
?>
