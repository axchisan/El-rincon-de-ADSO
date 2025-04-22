<?php
session_start();
require_once "../../database/conexionDB.php";

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../inicio/index.php");
    exit();
}

// Verificar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../frontend/panel/panel-usuario.php");
    exit();
}

try {
    $db = conexionDB::getConexion();
    $user_id = $_SESSION['usuario_id'];

    // Obtener los datos del formulario
    $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
    $correo = filter_input(INPUT_POST, 'correo', FILTER_SANITIZE_EMAIL);
    $telefono = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING);
    $profesion = filter_input(INPUT_POST, 'profesion', FILTER_SANITIZE_STRING);
    $bio = filter_input(INPUT_POST, 'bio', FILTER_SANITIZE_STRING);

    // Validar los datos requeridos
    if (empty($nombre) || empty($correo)) {
        $_SESSION['error_message'] = "El nombre y el correo son obligatorios.";
        header("Location: ../../frontend/panel/panel-usuario.php");
        exit();
    }

    // Manejo de la imagen de perfil
    $imagen_path = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['imagen']['tmp_name'];
        $file_name = $_FILES['imagen']['name'];
        $file_size = $_FILES['imagen']['size'];
        $file_type = $_FILES['imagen']['type'];

        // Validar tipo de archivo (solo imágenes)
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file_type, $allowed_types)) {
            $_SESSION['error_message'] = "Solo se permiten imágenes en formato JPEG, PNG o GIF.";
            header("Location: ../../frontend/panel/panel-usuario.php");
            exit();
        }

        // Validar tamaño del archivo (máximo 5MB)
        $max_size = 5 * 1024 * 1024; // 5MB en bytes
        if ($file_size > $max_size) {
            $_SESSION['error_message'] = "La imagen no debe superar los 5MB.";
            header("Location: ../../frontend/panel/panel-usuario.php");
            exit();
        }

        // Crear directorio uploads si no existe
        $upload_dir = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Generar un nombre único para la imagen
        $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $new_file_name = uniqid('profile_', true) . '.' . $file_extension;
        $destination = $upload_dir . $new_file_name;

        // Mover el archivo al directorio uploads
        if (move_uploaded_file($file_tmp, $destination)) {
            $imagen_path = 'uploads/' . $new_file_name;
            error_log("Imagen guardada en: " . $destination); // Depuración
        } else {
            $_SESSION['error_message'] = "Error al subir la imagen. Intenta de nuevo.";
            error_log("Error al mover la imagen: " . $file_tmp . " a " . $destination); // Depuración
            header("Location: ../../frontend/panel/panel-usuario.php");
            exit();
        }
    }

    // Preparar la consulta de actualización
    $query = "UPDATE usuarios SET nombre_usuario = :nombre, correo = :correo, telefono = :telefono, profesion = :profesion, bio = :bio";
    $params = [
        ':nombre' => $nombre,
        ':correo' => $correo,
        ':telefono' => $telefono ?: null,
        ':profesion' => $profesion ?: null,
        ':bio' => $bio ?: null,
        ':id' => $user_id
    ];

    // Si se subió una imagen, incluirla en la consulta
    if ($imagen_path) {
        $query .= ", imagen = :imagen";
        $params[':imagen'] = $imagen_path;
    }

    $query .= " WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    error_log("Consulta ejecutada. Imagen path: " . ($imagen_path ?? 'No se subió imagen')); // Depuración
    
    // Mostrar ventana emergente y redirigir
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="refresh" content="0;url=../../frontend/panel/panel-usuario.php">
        <title>Redirigiendo...</title>
        <script>
            alert("Datos guardados correctamente.");
            window.location.href = "../../frontend/panel/panel-usuario.php";
        </script>
    </head>
    <body>
        <p>Redirigiendo...</p>
    </body>
    </html>';
    exit();
    
} catch (PDOException $e) {
    // Manejo de errores
    $_SESSION['error_message'] = "Error al actualizar los datos: " . $e->getMessage();
    header("Location: ../../frontend/panel/panel-usuario.php");
    exit();
}
?>