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

  // Verificar que se haya pasado un friend_id y que sea un amigo válido
  if (!isset($_GET['friend_id']) || !is_numeric($_GET['friend_id'])) {
    error_log("Friend ID no proporcionado o inválido, redirigiendo a amigos.php");
    header("Location: ../friends/amigos.php");
    exit();
  }

  $friend_id = (int)$_GET['friend_id'];

  // Verificar que el usuario es un amigo
  $query = "SELECT COUNT(*) 
            FROM amistades 
            WHERE ((usuario_id = :user_id AND amigo_id = :friend_id) 
                   OR (usuario_id = :friend_id AND amigo_id = :user_id)) 
            AND estado = 'accepted'";
  $stmt = $db->prepare($query);
  $stmt->execute([
    ':user_id' => $user_id,
    ':friend_id' => $friend_id
  ]);
  $is_friend = $stmt->fetchColumn();

  if ($is_friend == 0) {
    error_log("El usuario no es un amigo, redirigiendo a amigos.php");
    header("Location: ../friends/amigos.php");
    exit();
  }

  // Obtener datos del amigo, incluyendo la imagen
  $query = "SELECT nombre_usuario, correo, imagen FROM usuarios WHERE id = :friend_id";
  $stmt = $db->prepare($query);
  $stmt->execute([':friend_id' => $friend_id]);
  $friend = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$friend) {
    error_log("Amigo no encontrado, redirigiendo a amigos.php");
    header("Location: ../friends/amigos.php");
    exit();
  }

  $friend_name = htmlspecialchars($friend['nombre_usuario']);
  $friend_email = htmlspecialchars($friend['correo']);
  $friend_image = $friend['imagen'] ? "../../backend/perfil/" . htmlspecialchars($friend['imagen']) . "?v=" . time() : "https://i.pravatar.cc/150?img=$friend_id";

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
  <title>Chat con <?php echo $friend_name; ?> - El Rincón de ADSO</title>
  <link rel="icon" type="image/png" href="../inicio/img/icono.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
  <link rel="stylesheet" href="../friends/css/style.css">
  <link rel="stylesheet" href="./css/style.css">
  <style>
    .message__actions {
      display: none;
      margin-top: 5px;
    }
    .message:hover .message__actions {
      display: flex;
      gap: 10px;
    }
    .message__action-btn {
      background: none;
      border: none;
      color: #007bff;
      cursor: pointer;
      font-size: 14px;
      padding: 0;
    }
    .message__action-btn:hover {
      text-decoration: underline;
    }
    .message__edit-form {
      display: none;
      margin-top: 10px;
    }
    .message__edit-input {
      width: 100%;
      padding: 5px;
      border: 1px solid #ccc;
      border-radius: 4px;
      margin-bottom: 5px;
    }
    .message__edit-submit {
      background-color: #007bff;
      color: white;
      border: none;
      padding: 5px 10px;
      border-radius: 4px;
      cursor: pointer;
    }
    .message__edit-submit:hover {
      background-color: #0056b3;
    }
    .message__edit-cancel {
      background: none;
      border: none;
      color: #dc3545;
      cursor: pointer;
      margin-left: 10px;
    }
    .message__edit-cancel:hover {
      text-decoration: underline;
    }
  </style>
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
  <section class="chat-section">
    <div class="container">
      <div class="chat-header">
        <div class="chat-header__avatar">
          <img src="<?php echo $friend_image; ?>" alt="Avatar">
        </div>
        <div class="chat-header__info">
          <h1 class="chat-header__title">Chat con <?php echo $friend_name; ?></h1>
          <p class="chat-header__email"><?php echo $friend_email; ?></p>
        </div>
        <div class="chat-header__actions">
          <a href="../friends/amigos.php" class="chat-header__back">
            <i class="fas fa-arrow-left"></i> Volver a Amigos
          </a>
        </div>
      </div>

      <!-- Área de mensajes -->
      <div class="chat-messages" id="chat-messages">
        <p class="chat-messages__empty">Cargando mensajes...</p>
      </div>

      <!-- Formulario para enviar mensajes -->
      <div class="chat-input">
        <div class="chat-input__form">
          <div class="chat-input__wrapper">
            <input type="text" id="message-input" class="chat-input__field" placeholder="Escribe un mensaje..." required>
          </div>
          <button id="send-message-btn" class="chat-input__button">
            <i class="fas fa-paper-plane"></i> Enviar
          </button>
        </div>
      </div>
    </div>
  </section>

  <!-- Script para el menú móvil y el chat dinámico -->
  <script>
    // Variables globales
    const userId = <?php echo $user_id; ?>;
    const friendId = <?php echo $friend_id; ?>;
    let lastMessageId = 0; // Para rastrear el último mensaje cargado

    // Elementos del DOM
    const chatMessages = document.getElementById('chat-messages');
    const messageInput = document.getElementById('message-input');
    const sendMessageBtn = document.getElementById('send-message-btn');

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

    // Función para cargar mensajes
    async function loadMessages() {
      try {
        const response = await fetch(`../../backend/api/mensajes.php?friend_id=${friendId}`);
        const result = await response.json();

        if (result.status !== 'success') {
          chatMessages.innerHTML = `<p class="chat-messages__empty">Error al cargar mensajes: ${result.message}</p>`;
          return;
        }

        const messages = result.data;
        if (messages.length === 0) {
          chatMessages.innerHTML = '<p class="chat-messages__empty">Aún no hay mensajes. ¡Inicia la conversación!</p>';
          return;
        }

        // Solo actualizar si hay nuevos mensajes
        const newMessages = messages.filter(msg => msg.id > lastMessageId);
        if (newMessages.length === 0) {
          return; // No hay nuevos mensajes
        }

        // Si es la primera carga, limpiar el contenedor
        if (lastMessageId === 0) {
          chatMessages.innerHTML = '';
        }

        // Agregar nuevos mensajes
        newMessages.forEach(message => {
          const messageDiv = document.createElement('div');
          messageDiv.className = `message ${message.remitente_id == userId ? 'message--sent' : 'message--received'}`;
          messageDiv.dataset.messageId = message.id; // Añadir ID del mensaje para referencia
          messageDiv.innerHTML = `
            <div class="message__content">
              <p>${message.contenido}</p>
              <span class="message__time">${formatDate(message.fecha_envio)}</span>
            </div>
            ${message.remitente_id == userId ? `
              <div class="message__actions">
                <button class="message__action-btn message__edit-btn">Editar</button>
                <button class="message__action-btn message__delete-btn">Borrar</button>
              </div>
              <div class="message__edit-form">
                <input type="text" class="message__edit-input" value="${message.contenido}" />
                <button class="message__edit-submit">Guardar</button>
                <button class="message__edit-cancel">Cancelar</button>
              </div>
            ` : ''}
          `;
          chatMessages.appendChild(messageDiv);

          // Actualizar el último ID de mensaje
          if (message.id > lastMessageId) {
            lastMessageId = message.id;
          }
        });

        // Añadir eventos a los botones de editar y borrar
        addMessageEventListeners();

        // Desplazar al final del chat
        chatMessages.scrollTop = chatMessages.scrollHeight;
      } catch (error) {
        console.error('Error al cargar mensajes:', error);
        chatMessages.innerHTML = '<p class="chat-messages__empty">Error al cargar mensajes.</p>';
      }
    }

    // Función para añadir eventos a los botones de editar y borrar
    function addMessageEventListeners() {
      // Botones de editar
      document.querySelectorAll('.message__edit-btn').forEach(button => {
        button.addEventListener('click', (e) => {
          const messageDiv = e.target.closest('.message');
          const editForm = messageDiv.querySelector('.message__edit-form');
          const messageContent = messageDiv.querySelector('.message__content p');
          editForm.style.display = 'block';
          messageContent.style.display = 'none';
          messageDiv.querySelector('.message__actions').style.display = 'none';
        });
      });

      // Botones de cancelar edición
      document.querySelectorAll('.message__edit-cancel').forEach(button => {
        button.addEventListener('click', (e) => {
          const messageDiv = e.target.closest('.message');
          const editForm = messageDiv.querySelector('.message__edit-form');
          const messageContent = messageDiv.querySelector('.message__content p');
          editForm.style.display = 'none';
          messageContent.style.display = 'block';
          messageDiv.querySelector('.message__actions').style.display = 'flex';
        });
      });

      // Formularios de edición
      document.querySelectorAll('.message__edit-submit').forEach(button => {
        button.addEventListener('click', async (e) => {
          const messageDiv = e.target.closest('.message');
          const messageId = messageDiv.dataset.messageId;
          const newContent = messageDiv.querySelector('.message__edit-input').value.trim();

          if (!newContent) {
            alert('El mensaje no puede estar vacío.');
            return;
          }

          try {
            const response = await fetch('../../backend/api/mensajes.php', {
              method: 'PUT',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                message_id: messageId,
                content: newContent
              })
            });

            const result = await response.json();

            if (result.status === 'success') {
              await loadMessages(); // Recargar los mensajes para reflejar los cambios
            } else {
              alert('Error al editar el mensaje: ' + result.message);
            }
          } catch (error) {
            console.error('Error al editar mensaje:', error);
            alert('Error al editar el mensaje.');
          }
        });
      });

      // Botones de borrar
      document.querySelectorAll('.message__delete-btn').forEach(button => {
        button.addEventListener('click', async (e) => {
          if (!confirm('¿Estás seguro de que quieres borrar este mensaje?')) {
            return;
          }

          const messageDiv = e.target.closest('.message');
          const messageId = messageDiv.dataset.messageId;

          try {
            const response = await fetch('../../backend/api/mensajes.php', {
              method: 'DELETE',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                message_id: messageId
              })
            });

            const result = await response.json();

            if (result.status === 'success') {
              await loadMessages(); // Recargar los mensajes para reflejar los cambios
            } else {
              alert('Error al borrar el mensaje: ' + result.message);
            }
          } catch (error) {
            console.error('Error al borrar mensaje:', error);
            alert('Error al borrar el mensaje.');
          }
        });
      });
    }

    // Función para enviar un mensaje
    async function sendMessage() {
      const content = messageInput.value.trim();
      if (!content) {
        return; // No enviar mensajes vacíos
      }

      try {
        const response = await fetch('../../backend/api/mensajes.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            friend_id: friendId,
            content: content
          })
        });

        const result = await response.json();

        if (result.status === 'success') {
          messageInput.value = ''; // Limpiar el campo de entrada
          await loadMessages(); // Recargar los mensajes para mostrar el nuevo mensaje
        } else {
          alert('Error al enviar el mensaje: ' + result.message);
        }
      } catch (error) {
        console.error('Error al enviar mensaje:', error);
        alert('Error al enviar el mensaje.');
      }
    }

    // Cargar mensajes inicialmente y cada 5 segundos
    loadMessages();
    setInterval(loadMessages, 5000);

    // Evento para enviar mensaje al hacer clic en el botón
    sendMessageBtn.addEventListener('click', sendMessage);

    // Evento para enviar mensaje al presionar Enter
    messageInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        sendMessage();
      }
    });

    // Script para el menú móvil
    document.getElementById('mobile-menu-button').addEventListener('click', function() {
      const mobileMenu = document.getElementById('mobile-menu');
      mobileMenu.classList.toggle('hidden');
    });
  </script>
</body>
</html>