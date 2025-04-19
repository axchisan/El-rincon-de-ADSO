<?php
session_start();
//Conexion a la base de datos
require_once "../database/conexionDB.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre_usuario = $_POST['nombre_usuario'];
    $contrasena = $_POST['contrasena'];

    // Consulta para verificar las credenciales y obtener el rol
    $sql = "SELECT nombre_usuario, rol FROM usuarios WHERE nombre_usuario = ? AND contrasena = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $nombre_usuario, $contrasena);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $rol = $row['rol'];

        // Verificar si el rol es "user"
        if ($rol === "user") {
            // Credenciales correctas y rol es "user", iniciar sesión y redirigir
            $_SESSION['nombre_usuario'] = $nombre_usuario;
            $_SESSION['rol'] = $rol;
            header("Location: ../panel/panel-usuario.php");
            exit();
        } else {
            $error = "Acceso denegado: No tienes el rol de usuario.";
        }
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }

    $stmt->close();
}

$conn->close();
?>