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
  <title>Foro - El Rincón de ADSO</title>
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
        <li class="navbar__menu-item navbar__menu-item--active"><a href="../foro/foro.php">Foro</a></li>
        <li class="navbar__menu-item"><a href="../eventos/eventos.php">Eventos</a></li>
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
        <li class="navbar__mobile-item navbar__mobile-item--active"><a href="../foro/foro.php">Foro</a></li>
        <li class="navbar__mobile-item"><a href="../eventos/eventos.php">Eventos</a></li>
        <li class="navbar__mobile-item"><a href="../../backend/logout.php">Cerrar sesión</a></li>
      </ul>
    </div>
  </nav>

  <!-- Sección principal -->
  <section class="friends-section">
    <div class="container">
      <div class="friends-header">
        <h1 class="friends-header__title">Foro</h1>
        <p class="friends-header__description">Participa en discusiones con la comunidad.</p>
      </div>

      <!-- Formulario para crear un nuevo tema -->
      <div class="mb-4">
        <h2 class="text-lg font-semibold mb-2">Crear un nuevo tema</h2>
        <div class="friend-card">
          <div class="friend-card__info">
            <input type="text" id="new-topic-title" class="friend-card__input w-full mb-2" placeholder="Título del tema" required>
            <textarea id="new-topic-content" class="friend-card__input w-full mb-2" placeholder="Contenido del tema" rows="3" required></textarea>
          </div>
          <div class="friend-card__actions">
            <button id="create-topic-btn" class="friend-card__action friend-card__action--accept">Crear tema</button>
          </div>
        </div>
      </div>

      <!-- Lista de temas -->
      <div class="notifications-list" id="topics-list">
        <p class="friends-list__empty">Cargando temas...</p>
      </div>

      <!-- Detalle del tema (oculto inicialmente) -->
      <div id="topic-detail" class="hidden">
        <div class="friends-header">
          <h2 id="topic-title" class="friends-header__title"></h2>
          <button id="back-to-topics" class="friend-card__action friend-card__action--accept">Volver a la lista</button>
        </div>
        <div id="topic-messages" class="notifications-list"></div>
        <!-- Formulario para responder -->
        <div class="friend-card mt-4">
          <div class="friend-card__info">
            <textarea id="new-message-content" class="friend-card__input w-full mb-2" placeholder="Escribe tu respuesta..." rows="2" required></textarea>
          </div>
          <div class="friend-card__actions">
            <button id="post-message-btn" class="friend-card__action friend-card__action--accept">Enviar respuesta</button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Script para el menú móvil y la funcionalidad del foro -->
  <script>
    const topicsList = document.getElementById('topics-list');
    const topicDetail = document.getElementById('topic-detail');
    const topicTitle = document.getElementById('topic-title');
    const topicMessages = document.getElementById('topic-messages');
    const newTopicTitle = document.getElementById('new-topic-title');
    const newTopicContent = document.getElementById('new-topic-content');
    const createTopicBtn = document.getElementById('create-topic-btn');
    const backToTopicsBtn = document.getElementById('back-to-topics');
    const newMessageContent = document.getElementById('new-message-content');
    const postMessageBtn = document.getElementById('post-message-btn');
    let currentTopicId = null;
    let lastMessageId = 0;

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

    // Cargar lista de temas
    async function loadTopics() {
      try {
        const response = await fetch('../../backend/api/foro.php');
        const result = await response.json();

        if (result.status !== 'success') {
          topicsList.innerHTML = `<p class="friends-list__empty">Error al cargar temas: ${result.message}</p>`;
          return;
        }

        const topics = result.data.topics;

        if (topics.length === 0) {
          topicsList.innerHTML = '<p class="friends-list__empty">No hay temas en el foro. ¡Crea uno!</p>';
          return;
        }

        topicsList.innerHTML = '';
        topics.forEach(topic => {
          const topicDiv = document.createElement('div');
          topicDiv.className = 'notification-card notification-unread';
          topicDiv.dataset.topicId = topic.id;
          topicDiv.innerHTML = `
            <div class="notification-card__info">
              <p class="notification-card__message">
                <i class="fas fa-comment-alt"></i>
                ${topic.titulo} - por ${topic.autor}
              </p>
              <p class="notification-card__date">
                ${formatDate(topic.fecha_creacion)}
              </p>
            </div>
          `;
          topicDiv.addEventListener('click', () => showTopicDetail(topic.id, topic.titulo));
          topicsList.appendChild(topicDiv);
        });
      } catch (error) {
        console.error('Error al cargar temas:', error);
        topicsList.innerHTML = '<p class="friends-list__empty">Error al cargar temas.</p>';
      }
    }

    // Mostrar detalle de un tema
    async function showTopicDetail(topicId, title) {
      currentTopicId = topicId;
      lastMessageId = 0;
      topicTitle.textContent = title;
      topicsList.classList.add('hidden');
      topicDetail.classList.remove('hidden');
      await loadMessages();
    }

    // Cargar mensajes de un tema
    async function loadMessages() {
      try {
        const response = await fetch(`../../backend/api/foro.php?topic_id=${currentTopicId}`);
        const result = await response.json();

        if (result.status !== 'success') {
          topicMessages.innerHTML = `<p class="friends-list__empty">Error al cargar mensajes: ${result.message}</p>`;
          return;
        }

        const messages = result.data.messages;

        if (messages.length === 0 && lastMessageId === 0) {
          topicMessages.innerHTML = '<p class="friends-list__empty">No hay mensajes en este tema.</p>';
          return;
        }

        const newMessages = messages.filter(msg => msg.id > lastMessageId);
        if (newMessages.length === 0 && lastMessageId !== 0) {
          return;
        }

        if (lastMessageId === 0) {
          topicMessages.innerHTML = '';
        }

        newMessages.forEach(msg => {
          const msgDiv = document.createElement('div');
          msgDiv.className = 'notification-card notification-read';
          msgDiv.innerHTML = `
            <div class="notification-card__info">
              <p class="notification-card__message">
                <i class="fas fa-reply"></i>
                ${msg.contenido} - por ${msg.autor}
              </p>
              <p class="notification-card__date">
                ${formatDate(msg.fecha_creacion)}
              </p>
            </div>
          `;
          topicMessages.appendChild(msgDiv);
          if (msg.id > lastMessageId) {
            lastMessageId = msg.id;
          }
        });
      } catch (error) {
        console.error('Error al cargar mensajes:', error);
        topicMessages.innerHTML = '<p class="friends-list__empty">Error al cargar mensajes.</p>';
      }
    }

    // Crear un nuevo tema
    createTopicBtn.addEventListener('click', async () => {
      const title = newTopicTitle.value.trim();
      const content = newTopicContent.value.trim();

      if (!title || !content) {
        alert('Por favor, completa todos los campos.');
        return;
      }

      try {
        const response = await fetch('../../backend/api/foro.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            action: 'create_topic',
            title: title,
            content: content
          })
        });
        const result = await response.json();

        if (result.status === 'success') {
          newTopicTitle.value = '';
          newTopicContent.value = '';
          await loadTopics();
        } else {
          alert('Error al crear el tema: ' + result.message);
        }
      } catch (error) {
        console.error('Error al crear tema:', error);
        alert('Error al crear el tema.');
      }
    });

    // Enviar un mensaje en un tema
    postMessageBtn.addEventListener('click', async () => {
      const content = newMessageContent.value.trim();

      if (!content) {
        alert('Por favor, escribe un mensaje.');
        return;
      }

      try {
        const response = await fetch('../../backend/api/foro.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            action: 'post_message',
            topic_id: currentTopicId,
            content: content
          })
        });
        const result = await response.json();

        if (result.status === 'success') {
          newMessageContent.value = '';
          lastMessageId = 0;
          await loadMessages();
        } else {
          alert('Error al enviar el mensaje: ' + result.message);
        }
      } catch (error) {
        console.error('Error al enviar mensaje:', error);
        alert('Error al enviar el mensaje.');
      }
    });

    // Volver a la lista de temas
    backToTopicsBtn.addEventListener('click', () => {
      topicDetail.classList.add('hidden');
      topicsList.classList.remove('hidden');
      currentTopicId = null;
      lastMessageId = 0;
    });

    // Cargar temas inicialmente y actualizar mensajes en tiempo real
    loadTopics();
    setInterval(() => {
      if (currentTopicId) {
        loadMessages();
      }
    }, 5000);

    // Script para el menú móvil
    document.getElementById('mobile-menu-button').addEventListener('click', function() {
      const mobileMenu = document.getElementById('mobile-menu');
      mobileMenu.classList.toggle('hidden');
    });
  </script>
</body>
</html>