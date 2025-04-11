<?php // aca esta la logica de validacion del login
session_start();
require 'db.php';

$usuario = $_POST['usuario'];
$clave = $_POST['clave'];

// Buscar el usuario en la base de datos
$sql = "SELECT * FROM usuarios WHERE usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 1) {
    $fila = $resultado->fetch_assoc();

    if (password_verify($clave, $fila['clave'])) {
        // Login exitoso
        $_SESSION['usuario'] = $fila['usuario'];
        header("Location: home.php");
        exit();
    } else {
        echo "Contraseña incorrecta.";
    }
} else {
    echo "Usuario no encontrado.";
}
?>
