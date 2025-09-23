<?php

ini_set('session.gc_maxlifetime', 3600);
session_set_cookie_params(3600, '/');


session_start();
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
            <span id="notification-badge" class="notification-badge" style="display: none;"></span>
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
            <span id="mobile-notification-badge" class="notification-badge" style="display: none;"></span>
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
      <div id="mark-all-container" class="text-right mb-4" style="display: none;">
        <button id="mark-all-as-read" class="friend-card__action friend-card__action--accept">
          Marcar todas como leídas
        </button>
      </div>

      <!-- Lista de notificaciones -->
      <div class="notifications-list" id="notifications-list">
        <p class="friends-list__empty">Cargando notificaciones...</p>
      </div>
    </div>
  </section>

  <!-- Script para el menú móvil y las notificaciones en tiempo real -->
  <script>
    // Elementos del DOM
    const notificationsList = document.getElementById('notifications-list');
    const notificationBadge = document.getElementById('notification-badge');
    const mobileNotificationBadge = document.getElementById('mobile-notification-badge');
    const markAllContainer = document.getElementById('mark-all-container');
    const markAllButton = document.getElementById('mark-all-as-read');
    let lastNotificationId = 0; // Para rastrear la última notificación cargada

    // Función para formatear fechas
    function formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    }

    // Función para cargar notificaciones
    async function loadNotifications() {
      try {
        const response = await fetch('../../backend/api/notificaciones.php');
        const result = await response.json();

        if (result.status !== 'success') {
          notificationsList.innerHTML = `<p class="friends-list__empty">Error al cargar notificaciones: ${result.message}</p>`;
          return;
        }

        const { notifications, unread_count } = result.data;

        // Actualizar el badge de notificaciones no leídas
        if (unread_count > 0) {
          notificationBadge.textContent = unread_count;
          notificationBadge.style.display = 'inline';
          mobileNotificationBadge.textContent = unread_count;
          mobileNotificationBadge.style.display = 'inline';
          markAllContainer.style.display = 'block';
        } else {
          notificationBadge.style.display = 'none';
          mobileNotificationBadge.style.display = 'none';
          markAllContainer.style.display = 'none';
        }

        // Si no hay notificaciones
        if (notifications.length === 0) {
          notificationsList.innerHTML = '<p class="friends-list__empty">No tienes notificaciones por el momento.</p>';
          return;
        }

        // Filtrar solo las notificaciones nuevas
        const newNotifications = notifications.filter(notif => notif.id > lastNotificationId);
        if (newNotifications.length === 0 && lastNotificationId !== 0) {
          return; // No hay nuevas notificaciones
        }

        // Si es la primera carga, limpiar el contenedor
        if (lastNotificationId === 0) {
          notificationsList.innerHTML = '';
        }

        // Agregar nuevas notificaciones
        newNotifications.forEach(notification => {
          const notificationDiv = document.createElement('div');
          notificationDiv.className = `notification-card ${notification.leida ? 'notification-read' : 'notification-unread'}`;
          notificationDiv.dataset.notificationId = notification.id;
          notificationDiv.innerHTML = `
            <div class="notification-card__info">
              <p class="notification-card__message">
                <i class="fas ${notification.tipo === 'friend_request' ? 'fa-user-plus' : 'fa-check-circle'}"></i>
                ${notification.mensaje}
              </p>
              <p class="notification-card__date">
                ${formatDate(notification.fecha_creacion)}
              </p>
            </div>
            ${!notification.leida ? `
              <div class="notification-card__actions">
                <button class="notification-card__action mark-as-read-btn">Marcar como leída</button>
              </div>
            ` : ''}
          `;
          notificationsList.prepend(notificationDiv); // Agregar al inicio

          // Actualizar el último ID de notificación
          if (notification.id > lastNotificationId) {
            lastNotificationId = notification.id;
          }
        });

        // Añadir eventos a los botones de "Marcar como leída"
        addMarkAsReadListeners();
      } catch (error) {
        console.error('Error al cargar notificaciones:', error);
        notificationsList.innerHTML = '<p class="friends-list__empty">Error al cargar notificaciones.</p>';
      }
    }

    // Función para añadir eventos a los botones de "Marcar como leída"
    function addMarkAsReadListeners() {
      document.querySelectorAll('.mark-as-read-btn').forEach(button => {
        button.addEventListener('click', async (e) => {
          const notificationDiv = e.target.closest('.notification-card');
          const notificationId = notificationDiv.dataset.notificationId;

          try {
            const response = await fetch('../../backend/api/notificaciones.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                action: 'mark_as_read',
                notification_id: notificationId
              })
            });

            const result = await response.json();

            if (result.status === 'success') {
              lastNotificationId = 0; // Resetear para recargar todas las notificaciones
              await loadNotifications();
            } else {
              alert('Error al marcar como leída: ' + result.message);
            }
          } catch (error) {
            console.error('Error al marcar como leída:', error);
            alert('Error al marcar como leída.');
          }
        });
      });
    }

    // Función para marcar todas las notificaciones como leídas
    async function markAllAsRead() {
      try {
        const response = await fetch('../../backend/api/notificaciones.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            action: 'mark_all_as_read'
          })
        });

        const result = await response.json();

        if (result.status === 'success') {
          lastNotificationId = 0; // Resetear para recargar todas las notificaciones
          await loadNotifications();
        } else {
          alert('Error al marcar todas como leídas: ' + result.message);
        }
      } catch (error) {
        console.error('Error al marcar todas como leídas:', error);
        alert('Error al marcar todas como leídas.');
      }
    }

    // Cargar notificaciones inicialmente y cada 5 segundos
    loadNotifications();
    setInterval(loadNotifications, 5000);

    // Evento para marcar todas las notificaciones como leídas
    markAllButton.addEventListener('click', markAllAsRead);

    // Script para el menú móvil
    document.getElementById('mobile-menu-button').addEventListener('click', function() {
      const mobileMenu = document.getElementById('mobile-menu');
      mobileMenu.classList.toggle('hidden');
    });
  </script>
</body>
</html>