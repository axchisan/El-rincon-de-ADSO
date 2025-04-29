<?php
session_start();

// Evitar caché para que la imagen actualizada se muestre correctamente
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once "../../database/conexionDB.php";

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../frontend/login/login.php");
    exit();
}

try {
    $db = conexionDB::getConexion();
    $user_id = $_SESSION['usuario_id'];

    // Verificar qué acción se está realizando
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'change_password') {
        // Cambio de contraseña
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Obtener la contraseña actual del usuario desde la base de datos
        $query = "SELECT contrasena FROM usuarios WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['error_message'] = "Usuario no encontrado.";
            header("Location: ../../frontend/panel/panel-usuario.php");
            exit();
        }

        // Verificar si la contraseña actual es correcta
        if (!password_verify($current_password, $user['contrasena'])) {
            $_SESSION['error_message'] = "La contraseña actual es incorrecta.";
            header("Location: ../../frontend/panel/panel-usuario.php");
            exit();
        }

        // Verificar si las nuevas contraseñas coinciden
        if ($new_password !== $confirm_password) {
            $_SESSION['error_message'] = "Las nuevas contraseñas no coinciden.";
            header("Location: ../../frontend/panel/panel-usuario.php");
            exit();
        }

        // Validar los requisitos de la nueva contraseña
        if (!preg_match('/[A-Z]/', $new_password)) {
            $_SESSION['error_message'] = "La nueva contraseña debe contener al menos una letra mayúscula.";
            header("Location: ../../frontend/panel/panel-usuario.php");
            exit();
        }
        if (preg_match_all('/\d/', $new_password) < 3) {
            $_SESSION['error_message'] = "La nueva contraseña debe contener al menos 3 números.";
            header("Location: ../../frontend/panel/panel-usuario.php");
            exit();
        }
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $new_password)) {
            $_SESSION['error_message'] = "La nueva contraseña debe contener al menos un carácter especial.";
            header("Location: ../../frontend/panel/panel-usuario.php");
            exit();
        }

        // Cifrar la nueva contraseña
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Actualizar la contraseña en la base de datos
        $query = "UPDATE usuarios SET contrasena = :contrasena WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':contrasena' => $hashed_password,
            ':id' => $user_id
        ]);

        // Mostrar mensaje de éxito y redirigir
        echo "<script>alert('Contraseña actualizada correctamente'); window.location.href='../../frontend/panel/panel-usuario.php';</script>";
        exit();
    } else {
        // Actualización de datos personales
        $nombre = $_POST['nombre'] ?? '';
        $correo = $_POST['correo'] ?? '';
        $telefono = $_POST['telefono'] ?? '';
        $profesion = $_POST['profesion'] ?? '';
        $bio = $_POST['bio'] ?? '';
        $imagen = $_FILES['imagen'] ?? null;

        // Validar correo
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_message'] = "El correo electrónico no es válido.";
            header("Location: ../../frontend/panel/panel-usuario.php");
            exit();
        }

        // Manejo de la imagen
        $imagen_nombre = null;
        if ($imagen && $imagen['error'] === UPLOAD_ERR_OK) {
            $imagen_tmp = $imagen['tmp_name'];
            $imagen_nombre = uniqid() . '-' . basename($imagen['name']);
            $imagen_ruta = "uploads/" . $imagen_nombre;

            $extension = strtolower(pathinfo($imagen_nombre, PATHINFO_EXTENSION));
            $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($extension, $extensiones_permitidas)) {
                $_SESSION['error_message'] = "El formato de la imagen no es válido. Usa JPG, PNG o GIF.";
                header("Location: ../../frontend/panel/panel-usuario.php");
                exit();
            }

            if ($imagen['size'] > 5 * 1024 * 1024) { // 5MB
                $_SESSION['error_message'] = "La imagen es demasiado grande. El tamaño máximo es 5MB.";
                header("Location: ../../frontend/panel/panel-usuario.php");
                exit();
            }

            if (!move_uploaded_file($imagen_tmp, $imagen_ruta)) {
                $_SESSION['error_message'] = "Error al subir la imagen.";
                header("Location: ../../frontend/panel/panel-usuario.php");
                exit();
            }

            // Eliminar la imagen anterior si existe
            $query = "SELECT imagen FROM usuarios WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->execute([':id' => $user_id]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario['imagen'] && file_exists("uploads/" . $usuario['imagen'])) {
                unlink("uploads/" . $usuario['imagen']);
            }
        }

        // Actualizar datos en la base de datos
        $query = "UPDATE usuarios SET nombre_usuario = :nombre, correo = :correo, telefono = :telefono, profesion = :profesion, bio = :bio";
        $params = [
            ':nombre' => $nombre,
            ':correo' => $correo,
            ':telefono' => $telefono ?: null,
            ':profesion' => $profesion ?: null,
            ':bio' => $bio ?: null,
            ':id' => $user_id
        ];

        if ($imagen_nombre) {
            $query .= ", imagen = :imagen";
            $params[':imagen'] = $imagen_nombre;
        }

        $query .= " WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute($params);

        // Mostrar mensaje de éxito y redirigir
        echo "<script>alert('Datos guardados correctamente'); window.location.href='../../frontend/panel/panel-usuario.php';</script>";
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error al actualizar los datos: " . $e->getMessage();
    header("Location: ../../frontend/panel/panel-usuario.php");
    exit();
}
?>