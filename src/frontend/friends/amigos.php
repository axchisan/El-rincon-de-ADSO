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

  // Buscar usuarios por nombre_usuario
  $search_results = [];
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_user'])) {
    $search_term = trim($_POST['search_term']);
    if (!empty($search_term)) {
      $query = "SELECT id, nombre_usuario, correo, imagen 
                FROM usuarios 
                WHERE nombre_usuario ILIKE :search_term 
                AND id != :current_user_id 
                AND id NOT IN (
                  SELECT amigo_id 
                  FROM amistades 
                  WHERE usuario_id = :current_user_id AND estado = 'pending'
                )
                AND id NOT IN (
                  SELECT usuario_id 
                  FROM amistades 
                  WHERE amigo_id = :current_user_id AND estado = 'pending'
                )
                AND id NOT IN (
                  SELECT amigo_id 
                  FROM amistades 
                  WHERE usuario_id = :current_user_id AND estado = 'accepted'
                )
                AND id NOT IN (
                  SELECT usuario_id 
                  FROM amistades 
                  WHERE amigo_id = :current_user_id AND estado = 'accepted'
                )";
      $stmt = $db->prepare($query);
      $stmt->execute([
        ':search_term' => "%$search_term%",
        ':current_user_id' => $user_id
      ]);
      $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
  }

  // Enviar solicitud de amistad
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_request'])) {
    $receiver_id = $_POST['receiver_id'];
    
    // Insertar la solicitud en la tabla amistades
    $query = "INSERT INTO amistades (usuario_id, amigo_id, estado) 
              VALUES (:sender_id, :receiver_id, 'pending')";
    $stmt = $db->prepare($query);
    $stmt->execute([
      ':sender_id' => $user_id,
      ':receiver_id' => $receiver_id
    ]);

    // Obtener el nombre del usuario que envía la solicitud
    $query = "SELECT nombre_usuario FROM usuarios WHERE id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':user_id' => $user_id]);
    $sender = $stmt->fetch(PDO::FETCH_ASSOC);
    $sender_name = $sender['nombre_usuario'];

    // Generar una notificación para el receptor
    $query = "INSERT INTO notificaciones (usuario_id, tipo, relacionado_id, mensaje) 
              VALUES (:receiver_id, 'friend_request', :sender_id, :message)";
    $stmt = $db->prepare($query);
    $stmt->execute([
      ':receiver_id' => $receiver_id,
      ':sender_id' => $user_id,
      ':message' => "Tienes una nueva solicitud de amistad de $sender_name."
    ]);

    // Recargar la página para evitar reenvíos del formulario
    header("Location: amigos.php");
    exit();
  }

  // Aceptar solicitud de amistad
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept_request'])) {
    $sender_id = $_POST['sender_id'];
    
    // Actualizar el estado a 'accepted'
    $query = "UPDATE amistades 
              SET estado = 'accepted', fecha_creacion = CURRENT_TIMESTAMP 
              WHERE usuario_id = :sender_id AND amigo_id = :receiver_id AND estado = 'pending'";
    $stmt = $db->prepare($query);
    $stmt->execute([
      ':sender_id' => $sender_id,
      ':receiver_id' => $user_id
    ]);

    // Generar notificación para el usuario que envió la solicitud
    $query = "SELECT nombre_usuario FROM usuarios WHERE id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->execute([':user_id' => $user_id]);
    $receiver = $stmt->fetch(PDO::FETCH_ASSOC);
    $receiver_name = $receiver['nombre_usuario'];

    $query = "INSERT INTO notificaciones (usuario_id, tipo, relacionado_id, mensaje) 
              VALUES (:sender_id, 'friend_request_accepted', :receiver_id, :message)";
    $stmt = $db->prepare($query);
    $stmt->execute([
      ':sender_id' => $sender_id,
      ':receiver_id' => $user_id,
      ':message' => "$receiver_name ha aceptado tu solicitud de amistad."
    ]);

    header("Location: amigos.php");
    exit();
  }

  // Rechazar solicitud de amistad
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_request'])) {
    $sender_id = $_POST['sender_id'];
    
    // Actualizar el estado a 'rejected'
    $query = "UPDATE amistades 
              SET estado = 'rejected', fecha_creacion = CURRENT_TIMESTAMP 
              WHERE usuario_id = :sender_id AND amigo_id = :receiver_id AND estado = 'pending'";
    $stmt = $db->prepare($query);
    $stmt->execute([
      ':sender_id' => $sender_id,
      ':receiver_id' => $user_id
    ]);

    header("Location: amigos.php");
    exit();
  }

  // Eliminar amigo
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_friend'])) {
    $friend_id = $_POST['friend_id'];
    
    // Eliminar la relación de amistad (considera ambas direcciones)
    $query = "DELETE FROM amistades 
              WHERE (usuario_id = :user_id AND amigo_id = :friend_id AND estado = 'accepted') 
                 OR (usuario_id = :friend_id AND amigo_id = :user_id AND estado = 'accepted')";
    $stmt = $db->prepare($query);
    $stmt->execute([
      ':user_id' => $user_id,
      ':friend_id' => $friend_id
    ]);

    header("Location: amigos.php");
    exit();
  }

  // Obtener solicitudes recibidas
  $query = "SELECT a.id, a.usuario_id AS sender_id, u.nombre_usuario, u.correo, u.imagen 
            FROM amistades a 
            JOIN usuarios u ON a.usuario_id = u.id 
            WHERE a.amigo_id = :user_id AND a.estado = 'pending'";
  $stmt = $db->prepare($query);
  $stmt->execute([':user_id' => $user_id]);
  $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Obtener lista de amigos con su última conexión
  $query = "SELECT u.id, u.nombre_usuario, u.correo, u.ultima_conexion, u.imagen 
            FROM amistades a 
            JOIN usuarios u ON (u.id = a.usuario_id OR u.id = a.amigo_id) 
            WHERE ((a.usuario_id = :user_id AND u.id = a.amigo_id) OR (a.amigo_id = :user_id AND u.id = a.usuario_id)) 
            AND a.estado = 'accepted'";
  $stmt = $db->prepare($query);
  $stmt->execute([':user_id' => $user_id]);
  $friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
  <title>Amigos - El Rincón de ADSO</title>
  <link rel="icon" type="image/png" href="../inicio/img/icono.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
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
        <li class="navbar__menu-item navbar__menu-item--active"><a href="../friends/amigos.php">Amigos</a></li>
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
        <li class="navbar__mobile-item navbar__menu-item--active"><a href="../friends/amigos.php">Amigos</a></li>
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
  <section class="friends-section">
    <div class="container">
      <div class="friends-header">
        <h1 class="friends-header__title">Tus Amigos</h1>
        <p class="friends-header__description">Busca y conecta con otros usuarios para compartir recursos y conocimientos.</p>
      </div>

      <!-- Formulario de búsqueda -->
      <div class="friend-search">
        <form class="friend-search__form" method="POST">
          <div class="friend-search__input-wrapper">
            <i class="fas fa-search"></i>
            <input type="text" name="search_term" class="friend-search__input" placeholder="Buscar por nombre de usuario..." required>
          </div>
          <button type="submit" name="search_user" class="friend-search__button">Buscar</button>
        </form>

        <!-- Resultados de búsqueda -->
        <?php if (!empty($search_results)): ?>
          <div class="search-results">
            <h3 class="friends-list__title">Resultados de búsqueda</h3>
            <?php foreach ($search_results as $result): ?>
              <div class="friend-card">
                <div class="friend-card__avatar">
                  <img src="<?php echo $result['imagen'] ? '../backend/perfil/' . htmlspecialchars($result['imagen']) . '?v=' . time() : 'https://i.pravatar.cc/150?img=' . $result['id']; ?>" alt="Avatar">
                </div>
                <div class="friend-card__info">
                  <h3 class="friend-card__name"><?php echo htmlspecialchars($result['nombre_usuario']); ?></h3>
                  <p class="friend-card__email"><?php echo htmlspecialchars($result['correo']); ?></p>
                </div>
                <div class="friend-card__actions">
                  <form method="POST">
                    <input type="hidden" name="receiver_id" value="<?php echo $result['id']; ?>">
                    <button type="submit" name="send_request" class="friend-card__action friend-card__action--accept">
                      Enviar Solicitud
                    </button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php elseif (isset($_POST['search_user']) && empty($search_results)): ?>
          <p class="friends-list__empty">No se encontraron usuarios con ese nombre.</p>
        <?php endif; ?>
      </div>

      <!-- Lista de solicitudes recibidas -->
      <div class="requests-list">
        <h2 class="requests-list__title">Solicitudes de Amistad</h2>
        <?php if (empty($requests)): ?>
          <p class="requests-list__empty">No tienes solicitudes de amistad pendientes.</p>
        <?php else: ?>
          <?php foreach ($requests as $request): ?>
            <div class="request-card">
              <div class="request-card__avatar">
                <img src="<?php echo $request['imagen'] ? '../backend/perfil/' . htmlspecialchars($request['imagen']) . '?v=' . time() : 'https://i.pravatar.cc/150?img=' . $request['sender_id']; ?>" alt="Avatar">
              </div>
              <div class="request-card__info">
                <h3 class="request-card__name"><?php echo htmlspecialchars($request['nombre_usuario']); ?></h3>
                <p class="request-card__email"><?php echo htmlspecialchars($request['correo']); ?></p>
              </div>
              <div class="request-card__actions">
                <form method="POST">
                  <input type="hidden" name="sender_id" value="<?php echo $request['sender_id']; ?>">
                  <button type="submit" name="accept_request" class="request-card__action request-card__action--accept">Aceptar</button>
                </form>
                <form method="POST">
                  <input type="hidden" name="sender_id" value="<?php echo $request['sender_id']; ?>">
                  <button type="submit" name="reject_request" class="request-card__action request-card__action--reject">Rechazar</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Lista de amigos -->
      <div class="friends-list">
        <h2 class="friends-list__title">Mis Amigos</h2>
        <?php if (empty($friends)): ?>
          <p class="friends-list__empty">Aún no tienes amigos. ¡Busca usuarios y envía solicitudes!</p>
        <?php else: ?>
          <?php foreach ($friends as $friend): ?>
            <?php $status = getOnlineStatus($friend['ultima_conexion']); ?>
            <div class="friend-card">
              <div class="friend-card__avatar">
                <img src="<?php echo $friend['imagen'] ? '../backend/perfil/' . htmlspecialchars($friend['imagen']) . '?v=' . time() : 'https://i.pravatar.cc/150?img=' . $friend['id']; ?>" alt="Avatar">
              </div>
              <div class="friend-card__info">
                <h3 class="friend-card__name"><?php echo htmlspecialchars($friend['nombre_usuario']); ?></h3>
                <p class="friend-card__email"><?php echo htmlspecialchars($friend['correo']); ?></p>
                <p class="friend-card__status <?php echo $status['is_online'] ? 'friend-card__status--online' : 'friend-card__status--offline'; ?>">
                  <?php echo $status['status_text']; ?>
                </p>
              </div>
              <div class="friend-card__actions">
                <a href="../mensajes/mensajes.php?friend_id=<?php echo $friend['id']; ?>" class="friend-card__action friend-card__action--chat">
                  Chatear
                </a>
                <a href="../perfil/perfil.php?user_id=<?php echo $friend['id']; ?>" class="friend-card__action friend-card__action--profile">
                  Ver perfil
                </a>
                <form method="POST">
                  <input type="hidden" name="friend_id" value="<?php echo $friend['id']; ?>">
                  <button type="submit" name="remove_friend" class="friend-card__action friend-card__action--remove">Eliminar Amigo</button>
                </form>
              </div>
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