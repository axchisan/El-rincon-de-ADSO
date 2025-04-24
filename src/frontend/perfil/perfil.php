<?php
session_start();
ini_set('session.gc_maxlifetime', 3600);
session_set_cookie_params(3600, '/');
require_once "../../database/conexionDB.php";

if (!isset($_SESSION['usuario_id'])) {
  error_log("Sesión no encontrada, redirigiendo a index.php");
  header("Location: ../inicio/index.php");
  exit();
}

try {
  $db = conexionDB::getConexion();
  $user_id = $_SESSION['usuario_id'];

  // Obtener datos del usuario actual
  $query = "SELECT nombre_usuario, correo FROM usuarios WHERE id = :id";
  $stmt = $db->prepare($query);
  $stmt->execute([':id' => $user_id]);
  $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$usuario) {
    error_log("Usuario no encontrado, redirigiendo a index.php");
    header("Location: ../inicio/index.php");
    exit();
  }

  $nombre_usuario = htmlspecialchars($usuario['nombre_usuario']);
  $correo = htmlspecialchars($usuario['correo']);

  // Contar notificaciones no leídas
  $query = "SELECT COUNT(*) FROM notificaciones WHERE usuario_id = :user_id AND leida = FALSE";
  $stmt = $db->prepare($query);
  $stmt->execute([':user_id' => $user_id]);
  $unread_count = $stmt->fetchColumn();

  // Verificar que se haya pasado un user_id y que sea un amigo válido
  if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
    error_log("User ID no proporcionado o inválido, redirigiendo a amigos.php");
    header("Location: ../friends/amigos.php");
    exit();
  }

  $profile_user_id = (int)$_GET['user_id'];

  // Verificar que el usuario es un amigo
  $query = "SELECT COUNT(*) 
            FROM amistades 
            WHERE ((usuario_id = :user_id AND amigo_id = :friend_id) 
                   OR (usuario_id = :friend_id AND amigo_id = :user_id)) 
            AND estado = 'accepted'";
  $stmt = $db->prepare($query);
  $stmt->execute([
    ':user_id' => $user_id,
    ':friend_id' => $profile_user_id
  ]);
  $is_friend = $stmt->fetchColumn();

  if ($is_friend == 0) {
    error_log("El usuario no es un amigo, redirigiendo a amigos.php");
    header("Location: ../friends/amigos.php");
    exit();
  }

  // Obtener datos del usuario del perfil
  $query = "SELECT nombre_usuario, correo, telefono, profesion, bio, imagen, ultima_conexion 
            FROM usuarios 
            WHERE id = :user_id";
  $stmt = $db->prepare($query);
  $stmt->execute([':user_id' => $profile_user_id]);
  $profile_user = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$profile_user) {
    error_log("Usuario del perfil no encontrado, redirigiendo a amigos.php");
    header("Location: ../friends/amigos.php");
    exit();
  }

  $profile_name = htmlspecialchars($profile_user['nombre_usuario']);
  $profile_email = htmlspecialchars($profile_user['correo']);
  $profile_phone = !empty($profile_user['telefono']) ? htmlspecialchars($profile_user['telefono']) : 'No especificado';
  $profile_profession = !empty($profile_user['profesion']) ? htmlspecialchars($profile_user['profesion']) : 'No especificado';
  $profile_bio = !empty($profile_user['bio']) ? htmlspecialchars($profile_user['bio']) : 'Este usuario aún no ha añadido una biografía.';
  $profile_image = $profile_user['imagen'] ? "../backend/perfil/" . htmlspecialchars($profile_user['imagen']) . "?v=" . time() : "https://i.pravatar.cc/150?img=$profile_user_id";
  $last_connection = $profile_user['ultima_conexion'];

  // Función para determinar si un usuario está en línea y formatear la última conexión
  function getOnlineStatus($lastConnection) {
    if (is_null($lastConnection)) {
      return ['is_online' => false, 'status_text' => 'Última conexión: Desconocida'];
    }

    $lastConnectionTime = new DateTime($lastConnection);
    $currentTime = new DateTime();
    $interval = $currentTime->diff($lastConnectionTime);
    $minutes = $interval->days * 24 * 60 + $interval->h * 60 + $interval->i;

    if ($minutes <= 5) {
      return ['is_online' => true, 'status_text' => 'En línea'];
    } else {
      $formattedTime = $lastConnectionTime->format('d/m/Y H:i');
      return ['is_online' => false, 'status_text' => "Última conexión: $formattedTime"];
    }
  }

  $status = getOnlineStatus($last_connection);

} catch (PDOException $e) {
  error_log("Error de base de datos: " . $e->getMessage());
  header("Location: ../inicio/index.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Perfil de <?php echo $profile_name; ?> - El Rincón de ADSO</title>
  <link rel="icon" type="image/png" href="../inicio/img/icono.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
  <link rel="stylesheet" href="../friends/css/style.css">
  <link rel="stylesheet" href="./css/style.css">
</head>
<body>
  <!-- Navegación -->
  <nav class="navbar">
    <div class="container navbar__container">
      <a href="../inicio/index.php" class="navbar__logo">
        <i class="fas fa-book-open"></i>
        El Rincón de ADSO
      </a>
      <ul class="navbar__menu">
        <li class="navbar__menu-item"><a href="../inicio/index.php">Inicio</a></li>
        <li class="navbar__menu-item"><a href="../repositorio/repositorio.php">Repositorio</a></li>
        <li class="navbar__menu-item"><a href="../panel/panel-usuario.php">Panel</a></li>
        <li class="navbar__menu-item"><a href="../friends/amigos.php">Amigos</a></li>
        <li class="navbar__menu-item">
          <a href="../notificaciones/notificaciones.php">
            Notificaciones
            <?php if ($unread_count > 0): ?>
              <span class="notification-badge"><?php echo $unread_count; ?></span>
            <?php endif; ?>
          </a>
        </li>
        <li class="navbar__menu-item navbar__menu-item--button"><a href="../../backend/logout.php">Cerrar sesión</a></li>
      </ul>
      <button id="mobile-menu-button" class="navbar__toggle">
        <i class="fas fa-bars"></i>
      </button>
    </div>
    <div id="mobile-menu" class="navbar__mobile container hidden">
      <ul>
        <li class="navbar__mobile-item"><a href="../inicio/index.php">Inicio</a></li>
        <li class="navbar__mobile-item"><a href="../repositorio/repositorio.php">Repositorio</a></li>
        <li class="navbar__mobile-item"><a href="../panel/panel-usuario.php">Panel</a></li>
        <li class="navbar__mobile-item"><a href="../friends/amigos.php">Amigos</a></li>
        <li class="navbar__mobile-item">
          <a href="../notificaciones/notificaciones.php">
            Notificaciones
            <?php if ($unread_count > 0): ?>
              <span class="notification-badge"><?php echo $unread_count; ?></span>
            <?php endif; ?>
          </a>
        </li>
        <li class="navbar__mobile-item"><a href="../../backend/logout.php">Cerrar sesión</a></li>
      </ul>
    </div>
  </nav>

  <!-- Sección principal -->
  <section class="profile-section">
    <div class="container">
      <div class="profile-container">
        <h1 class="profile-container__title">Perfil de <?php echo $profile_name; ?></h1>
        <p class="profile-container__subtitle">Información personal</p>
        <div class="profile-content">
          <!-- Sección de la foto de perfil -->
          <div class="profile-image-section">
            <div class="profile-image-wrapper">
              <img src="<?php echo $profile_image; ?>" alt="Foto de perfil de <?php echo $profile_name; ?>" class="profile-image">
            </div>
            <p class="profile-image-label"><?php echo $status['status_text']; ?></p>
          </div>

          <!-- Sección de información -->
          <div class="profile-info-section">
            <div class="profile-info-row">
              <div class="profile-info-field">
                <label>Nombre</label>
                <p><?php echo $profile_name; ?></p>
              </div>
              <div class="profile-info-field">
                <label>Correo electrónico</label>
                <p><?php echo $profile_email; ?></p>
              </div>
            </div>
            <div class="profile-info-row">
              <div class="profile-info-field">
                <label>Teléfono</label>
                <p><?php echo $profile_phone; ?></p>
              </div>
              <div class="profile-info-field">
                <label>Profesión</label>
                <p><?php echo $profile_profession; ?></p>
              </div>
            </div>
            <div class="profile-info-row">
              <div class="profile-info-field profile-info-field--full">
                <label>Biografía</label>
                <p><?php echo $profile_bio; ?></p>
              </div>
            </div>
          </div>
        </div>
        <div class="profile-actions">
          <a href="../friends/amigos.php" class="profile-actions__button profile-actions__button--back">Volver a Amigos</a>
        </div>
      </div>
    </div>
  </section>

  <!-- Script para el menú móvil -->
  <script>
    document.getElementById('mobile-menu-button').addEventListener('click', function() {
      const mobileMenu = document.getElementById('mobile-menu');
      mobileMenu.classList.toggle('hidden');
    });
  </script>
</body>
</html>