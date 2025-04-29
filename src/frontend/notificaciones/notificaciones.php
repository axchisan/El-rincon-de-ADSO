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

  // Marcar notificación como leída
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_as_read'])) {
    $notification_id = $_POST['notification_id'];
    $query = "UPDATE notificaciones SET leida = TRUE WHERE id = :notification_id AND usuario_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->execute([
      ':notification_id' => $notification_id,
      ':user_id' => $user_id
    ]);
    header("Location: notificaciones.php");
    exit();
  }

  // Marcar todas las notificaciones como leídas
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all_as_read'])) {
    $query = "UPDATE notificaciones SET leida = TRUE WHERE usuario_id = :user_id AND leida = FALSE";
    $stmt = $db->prepare($query);
    $stmt->execute([':user_id' => $user_id]);
    header("Location: notificaciones.php");
    exit();
  }

  // Obtener todas las notificaciones del usuario
  $query = "SELECT n.id, n.tipo, n.relacionado_id, n.mensaje, n.leida, n.fecha_creacion, u.nombre_usuario AS relacionado_nombre 
            FROM notificaciones n 
            LEFT JOIN usuarios u ON n.relacionado_id = u.id 
            WHERE n.usuario_id = :user_id 
            ORDER BY n.fecha_creacion DESC";
  $stmt = $db->prepare($query);
  $stmt->execute([':user_id' => $user_id]);
  $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
  <title>Notificaciones - El Rincón de ADSO</title>
  <link rel="icon" type="image/png" href="../inicio/img/icono.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
  <link rel="stylesheet" href="../notificaciones/css/style.css">
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
        <li class="navbar__menu-item navbar__menu-item--active">
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
        <li class="navbar__mobile-item navbar__mobile-item--active">
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
  <section class="friends-section">
    <div class="container">
      <div class="friends-header">
        <h1 class="friends-header__title">Notificaciones</h1>
        <p class="friends-header__description">Aquí puedes ver tus notificaciones recientes.</p>
      </div>

      <!-- Botón para marcar todas como leídas -->
      <?php if (!empty($notifications) && $unread_count > 0): ?>
        <div class="text-right mb-4">
          <form method="POST" class="inline">
            <button type="submit" name="mark_all_as_read" class="friend-card__action friend-card__action--accept">
              Marcar todas como leídas
            </button>
          </form>
        </div>
      <?php endif; ?>

      <!-- Lista de notificaciones -->
      <div class="notifications-list">
        <?php if (empty($notifications)): ?>
          <p class="friends-list__empty">No tienes notificaciones por el momento.</p>
        <?php else: ?>
          <?php foreach ($notifications as $notification): ?>
            <div class="notification-card <?php echo $notification['leida'] ? 'notification-read' : 'notification-unread'; ?>">
              <div class="notification-card__info">
                <p class="notification-card__message">
                  <i class="fas <?php echo $notification['tipo'] === 'friend_request' ? 'fa-user-plus' : 'fa-check-circle'; ?>"></i>
                  <?php echo htmlspecialchars($notification['mensaje']); ?>
                </p>
                <p class="notification-card__date">
                  <?php echo date('d/m/Y H:i', strtotime($notification['fecha_creacion'])); ?>
                </p>
              </div>
              <?php if (!$notification['leida']): ?>
                <div class="notification-card__actions">
                  <form method="POST">
                    <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                    <button type="submit" name="mark_as_read" class="notification-card__action">Marcar como leída</button>
                  </form>
                </div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
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

