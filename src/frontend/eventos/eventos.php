<?php
// Establecer configuraciones de la sesión ANTES de iniciarla
ini_set('session.gc_maxlifetime', 3600);
session_set_cookie_params(3600, '/');

// Iniciar la sesión
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

  // Contar notificaciones no leídas
  $query = "SELECT COUNT(*) FROM notificaciones WHERE usuario_id = :user_id AND leida = FALSE";
  $stmt = $db->prepare($query);
  $stmt->execute([':user_id' => $user_id]);
  $unread_count = $stmt->fetchColumn();

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
  <title>Eventos - El Rincón de ADSO</title>
  <link rel="icon" type="image/png" href="../inicio/img/icono.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
  <link rel="stylesheet" href="../foro/css/style.css">
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
        <li class="navbar__menu-item"><a href="../notificaciones/notificaciones.php">
          Notificaciones
          <span id="notification-badge" class="notification-badge" style="display: <?php echo $unread_count > 0 ? 'inline' : 'none'; ?>;">
            <?php echo $unread_count; ?>
          </span>
        </a></li>
        <li class="navbar__menu-item"><a href="../foro/foro.php">Foro</a></li>
        <li class="navbar__menu-item navbar__menu-item--active"><a href="../eventos/eventos.php">Eventos</a></li>
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
            <span id="mobile-notification-badge" class="notification-badge" style="display: <?php echo $unread_count > 0 ? 'inline' : 'none'; ?>;">
              <?php echo $unread_count; ?>
            </span>
          </a>
        </li>
        <li class="navbar__mobile-item"><a href="../foro/foro.php">Foro</a></li>
        <li class="navbar__mobile-item navbar__mobile-item--active"><a href="../eventos/eventos.php">Eventos</a></li>
        <li class="navbar__mobile-item"><a href="../../backend/logout.php">Cerrar sesión</a></li>
      </ul>
    </div>
  </nav>

  <!-- Sección principal -->
  <section class="friends-section">
    <div class="container">
      <div class="friends-header">
        <h1 class="friends-header__title">Eventos</h1>
        <p class="friends-header__description">Descubre y participa en eventos de la comunidad.</p>
      </div>

      <!-- Formulario para crear un nuevo evento -->
      <div class="mb-4">
        <h2 class="text-lg font-semibold mb-2">Crear un nuevo evento</h2>
        <div class="friend-card">
          <div class="friend-card__info">
            <input type="text" id="new-event-title" class="friend-card__input w-full mb-2" placeholder="Título del evento" required>
            <textarea id="new-event-description" class="friend-card__input w-full mb-2" placeholder="Descripción del evento" rows="3"></textarea>
            <input type="datetime-local" id="new-event-start" class="friend-card__input w-full mb-2" required>
            <input type="datetime-local" id="new-event-end" class="friend-card__input w-full mb-2">
            <input type="text" id="new-event-location" class="friend-card__input w-full mb-2" placeholder="Lugar del evento">
          </div>
          <div class="friend-card__actions">
            <button id="create-event-btn" class="friend-card__action friend-card__action--accept">Crear evento</button>
          </div>
        </div>
      </div>

      <!-- Lista de eventos -->
      <div class="notifications-list" id="events-list">
        <p class="friends-list__empty">Cargando eventos...</p>
      </div>
    </div>
  </section>

  <!-- Script para el menú móvil y la funcionalidad de eventos -->
  <script>
    const eventsList = document.getElementById('events-list');
    const newEventTitle = document.getElementById('new-event-title');
    const newEventDescription = document.getElementById('new-event-description');
    const newEventStart = document.getElementById('new-event-start');
    const newEventEnd = document.getElementById('new-event-end');
    const newEventLocation = document.getElementById('new-event-location');
    const createEventBtn = document.getElementById('create-event-btn');

    // Formatear fechas
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

    // Cargar lista de eventos
    async function loadEvents() {
      try {
        const response = await fetch('../../backend/api/eventos.php');
        const result = await response.json();

        if (result.status !== 'success') {
          eventsList.innerHTML = `<p class="friends-list__empty">Error al cargar eventos: ${result.message}</p>`;
          return;
        }

        const events = result.data.events;

        if (events.length === 0) {
          eventsList.innerHTML = '<p class="friends-list__empty">No hay eventos disponibles.</p>';
          return;
        }

        eventsList.innerHTML = '';
        events.forEach(event => {
          const eventDiv = document.createElement('div');
          eventDiv.className = 'notification-card notification-unread';
          eventDiv.dataset.eventId = event.id;
          const isRegistered = event.is_registered;
          eventDiv.innerHTML = `
            <div class="notification-card__info">
              <p class="notification-card__message">
                <i class="fas fa-calendar-alt"></i>
                ${event.titulo} - Organizado por ${event.organizador}
              </p>
              <p>${event.descripcion || 'Sin descripción'}</p>
              <p><strong>Inicio:</strong> ${formatDate(event.fecha_inicio)}</p>
              ${event.fecha_fin ? `<p><strong>Fin:</strong> ${formatDate(event.fecha_fin)}</p>` : ''}
              ${event.lugar ? `<p><strong>Lugar:</strong> ${event.lugar}</p>` : ''}
              <p class="notification-card__date">
                Creado: ${formatDate(event.fecha_creacion)}
              </p>
            </div>
            <div class="notification-card__actions">
              <button class="${isRegistered ? 'friend-card__action friend-card__action--reject' : 'friend-card__action friend-card__action--accept'} toggle-registration-btn">
                ${isRegistered ? 'Cancelar inscripción' : 'Inscribirse'}
              </button>
            </div>
          `;
          eventsList.appendChild(eventDiv);
        });

        // Añadir eventos a los botones de inscripción
        document.querySelectorAll('.toggle-registration-btn').forEach(btn => {
          btn.addEventListener('click', async (e) => {
            const eventDiv = e.target.closest('.notification-card');
            const eventId = eventDiv.dataset.eventId;
            const isRegistered = e.target.textContent.trim() === 'Cancelar inscripción';

            try {
              const response = await fetch('../../backend/api/eventos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                  action: isRegistered ? 'unregister' : 'register',
                  event_id: eventId
                })
              });
              const result = await response.json();

              if (result.status === 'success') {
                await loadEvents();
              } else {
                alert(`Error al ${isRegistered ? 'cancelar inscripción' : 'inscribirse'}: ${result.message}`);
              }
            } catch (error) {
              console.error(`Error al ${isRegistered ? 'cancelar inscripción' : 'inscribirse'}:`, error);
              alert(`Error al ${isRegistered ? 'cancelar inscripción' : 'inscribirse'}.`);
            }
          });
        });
      } catch (error) {
        console.error('Error al cargar eventos:', error);
        eventsList.innerHTML = '<p class="friends-list__empty">Error al cargar eventos.</p>';
      }
    }

    // Crear un nuevo evento
    createEventBtn.addEventListener('click', async () => {
      const title = newEventTitle.value.trim();
      const description = newEventDescription.value.trim();
      const start = newEventStart.value;
      const end = newEventEnd.value;
      const location = newEventLocation.value.trim();

      if (!title || !start) {
        alert('El título y la fecha de inicio son obligatorios.');
        return;
      }

      if (end && new Date(end) <= new Date(start)) {
        alert('La fecha de fin debe ser posterior a la fecha de inicio.');
        return;
      }

      try {
        const response = await fetch('../../backend/api/eventos.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            action: 'create_event',
            title: title,
            description: description || null,
            start: start,
            end: end || null,
            location: location || null
          })
        });
        const result = await response.json();

        if (result.status === 'success') {
          newEventTitle.value = '';
          newEventDescription.value = '';
          newEventStart.value = '';
          newEventEnd.value = '';
          newEventLocation.value = '';
          await loadEvents();
        } else {
          alert('Error al crear el evento: ' + result.message);
        }
      } catch (error) {
        console.error('Error al crear evento:', error);
        alert('Error al crear el evento.');
      }
    });

    // Cargar eventos inicialmente y actualizar cada 5 segundos
    loadEvents();
    setInterval(loadEvents, 5000);

    // Script para el menú móvil
    document.getElementById('mobile-menu-button').addEventListener('click', function() {
      const mobileMenu = document.getElementById('mobile-menu');
      mobileMenu.classList.toggle('hidden');
    });
  </script>
</body>
</html>