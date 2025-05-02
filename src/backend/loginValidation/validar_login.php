<?php
session_start();
require_once "../../database/conexionDB.php";

session_regenerate_id(true);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $input_usuario = trim($_POST['usuario'] ?? '');
    $contrasena = trim($_POST['clave'] ?? '');
    $errors = [];

    // Validate inputs
    if (empty($input_usuario)) {
        $errors[] = "El campo usuario o correo es obligatorio.";
        $_SESSION['field_error'] = 'usuario';
    }
    if (empty($contrasena)) {
        $errors[] = "El campo contraseña es obligatorio.";
        $_SESSION['field_error'] = 'clave';
    }

    if (empty($errors)) {
        try {
            $db = conexionDB::getConexion();

            $sql = "SELECT id, nombre_usuario, correo, contrasena, rol FROM usuarios WHERE nombre_usuario = :input OR correo = :input";
            $stmt = $db->prepare($sql);
            $stmt->execute([':input' => $input_usuario]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificar si el usuario existe y la contraseña es correcta
            if ($usuario) {
                if (password_verify($contrasena, $usuario['contrasena'])) {
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];
                    $_SESSION['rol'] = $usuario['rol'];

                    // Clear any previous errors
                    unset($_SESSION['errors']);
                    unset($_SESSION['field_error']);

                    // Redirigir según el rol
                    if ($usuario['rol'] === "user") {
                        header("Location: ../../frontend/inicio/index.php");
                    } else if ($usuario['rol'] === "admin") {
                        header("Location: ../../frontend/inicio/panel-admin.php");
                    }
                    exit();
                } else {
                    $errors[] = "Contraseña incorrecta.";
                    $_SESSION['field_error'] = 'clave';
                }
            } else {
                $errors[] = "Usuario o correo no encontrado.";
                $_SESSION['field_error'] = 'usuario';
            }
        } catch (PDOException $e) {
            $errors[] = "Error al iniciar sesión: " . $e->getMessage();
        }
    }

    $_SESSION['errors'] = $errors;
    header("Location: ../../frontend/login/login.php");
    exit();
}