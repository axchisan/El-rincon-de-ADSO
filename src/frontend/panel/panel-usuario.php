<?php
session_start();
// Evitar caché de la página
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once "../../database/conexionDB.php";

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login/login.php");
    exit();
}

try {
    $db = conexionDB::getConexion();
    $user_id = $_SESSION['usuario_id'];

    // Obtener datos del usuario
    $query = "SELECT nombre_usuario, correo, telefono, profesion, bio, ultima_conexion, imagen FROM usuarios WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $user_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        session_destroy();
        header("Location: ../inicio/index.php");
        exit();
    }

    $nombre_usuario = htmlspecialchars($usuario['nombre_usuario']);
    $ultima_conexion = $usuario['ultima_conexion']
        ? date('d \d\e F, Y', strtotime($usuario['ultima_conexion']))
        : 'Sin registro';
    $imagen_perfil = $usuario['imagen'] ? "../../backend/perfil/" . $usuario['imagen'] . "?v=" . time() : './img/default-avatar.png';

    // Contar notificaciones no leídas
    $query = "SELECT COUNT(*) FROM notificaciones WHERE usuario_id = :user_id AND leida = FALSE";
    $stmt = $db->prepare($query);
    $stmt->execute([':user_id' => $user_id]);
    $unread_count = $stmt->fetchColumn();
} catch (PDOException $e) {
    session_destroy();
    header("Location: ../inicio/index.php");
    exit();
}

try {
    $query = "UPDATE usuarios SET ultima_conexion = NOW() WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->execute([':id' => $user_id]);
} catch (PDOException $e) {
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Usuario - El Rincón de ADSO</title>
    <link rel="icon" type="image/png" href="../inicio/img/icono.png">
    <link rel="stylesheet" href="./css/styles-panel.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <nav class="navbar">
        <div class="container navbar__container">
            <a href="../inicio/index.php" class="navbar__logo">
                <i class="fas fa-book-open"></i>
                El Rincón de ADSO
            </a>
            <ul class="navbar__menu">
                <li class="navbar__menu-item"><a href="../inicio/index.php">Inicio</a></li>
                <li class="navbar__menu-item"><a href="../repositorio/repositorio.php">Repositorio</a></li>
                <li class="navbar__menu-item navbar__menu-item--active"><a href="panel-usuario.php">Panel</a></li>
                <li class="navbar__menu-item"><a href="../friends/amigos.php">Amigos</a></li>
                <li class="navbar__menu-item"><a href="../notificaciones/notificaciones.php">
                        Notificaciones
                        <?php if ($unread_count > 0): ?>
                            <span class="notification-badge"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </a></li>
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
                <li class="navbar__mobile-item navbar__mobile-item--active"><a href="panel-usuario.php">Panel</a></li>
                <li class="navbar__mobile-item"><a href="../friends/amigos.php">Amigos</a></li>
                <li class="navbar__mobile-item"><a href="../notificaciones/notificaciones.php">
                        Notificaciones
                        <?php if ($unread_count > 0): ?>
                            <span class="notification-badge"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </a></li>
                <li class="navbar__mobile-item"><a href="../../backend/logout.php">Cerrar sesión</a></li>
            </ul>
        </div>
    </nav>

    <header class="panel-header">
        <div class="panel-header__bg"></div>
        <div class="container">
            <div class="user-welcome">
                <div class="user-avatar">
                    <div class="avatar-decoration"></div>
                    <img src="<?php echo $imagen_perfil; ?>" alt="Avatar de usuario" class="avatar-img">
                    <div class="user-status online" title="En línea"></div>
                </div>
                <div class="user-info">
                    <span class="user-greeting">¡Bienvenido de nuevo!</span>
                    <h1><?php echo $nombre_usuario; ?></h1>
                    <p><i class="fas fa-clock"></i> Última conexión: <?php echo $ultima_conexion; ?></p>
                    <div class="user-badges-preview">
                        <span class="user-badge" title="Colaborador Activo"><i class="fas fa-award"></i></span>
                        <span class="user-badge" title="Experto en Python"><i class="fab fa-python"></i></span>
                        <span class="user-badge" title="Mentor"><i class="fas fa-user-graduate"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="main-tabs">
        <div class="container">
            <div class="main-tabs__wrapper">
                <button class="main-tab active" data-tab="repositorio">
                    <div class="main-tab__icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="main-tab__content">
                        <span class="main-tab__title">Repositorio</span>
                        <span class="main-tab__description">Tus recursos y aportes</span>
                    </div>
                </button>
                <button class="main-tab" data-tab="perfil">
                    <div class="main-tab__icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="main-tab__content">
                        <span class="main-tab__title">Mi Perfil</span>
                        <span class="main-tab__description">Configuración personal</span>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <main class="main-content">
        <div class="container">
            <section id="repositorio" class="tab-section active">
                <div class="sub-tabs">
                    <button class="sub-tab active" data-subtab="mis-favoritos">Mis Favoritos</button>
                    <button class="sub-tab" data-subtab="recientes">Vistos Recientemente</button>
                    <button class="sub-tab" data-subtab="guardados">Guardados</button>
                    <button class="sub-tab" data-subtab="mis-aportes">Mis Aportes</button>
                </div>

                <div class="sub-content">
                    <div id="mis-favoritos" class="sub-panel active">
                        <div class="panel-header-secondary">
                            <h2>Mis Recursos Favoritos</h2>
                            <p>Aquí encontrarás todos los recursos que has marcado como favoritos</p>
                        </div>
                        <div class="resources-grid" id="favorites-grid">
                            <p>Cargando recursos favoritos...</p>
                        </div>
                    </div>

                    <div id="recientes" class="sub-panel">
                        <div class="panel-header-secondary">
                            <h2>Vistos Recientemente</h2>
                            <p>Recursos que has consultado en los últimos días</p>
                        </div>
                        <div class="resources-grid" id="recently-viewed-grid">
                            <p>Cargando recursos vistos recientemente...</p>
                        </div>
                    </div>

                    <div id="guardados" class="sub-panel">
                        <div class="panel-header-secondary">
                            <h2>Recursos Guardados</h2>
                            <p>Recursos que has guardado para consultar más tarde</p>
                        </div>
                        <div class="resources-grid" id="saved-grid">
                            <p>Cargando recursos guardados...</p>
                        </div>
                    </div>

                    <div id="mis-aportes" class="sub-panel">
                        <div class="panel-header-secondary">
                            <h2>Mis Aportes al Repositorio</h2>
                            <p>Recursos que has compartido con la comunidad</p>
                        </div>
                        <div class="action-bar">
                            <button class="btn btn--primary" id="open-upload-modal"><i class="fas fa-plus"></i> Nuevo aporte</button>
                        </div>
                        <div id="upload-modal" class="modal">
                            <div class="modal-content">
                                <span class="close" id="close-upload-modal">×</span>
                                <h3 id="modal-title">Subir Nuevo Recurso</h3>
                                <form id="upload-resource-form" enctype="multipart/form-data">
                                    <input type="hidden" id="resource-id" name="resource_id">
                                    <div class="form-group">
                                        <label for="resource-title">Título *</label>
                                        <input type="text" id="resource-title" name="title" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="resource-description">Descripción</label>
                                        <textarea id="resource-description" name="description" rows="3"></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="resource-author">Autor *</label>
                                        <input type="text" id="resource-author" name="author" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="resource-image">Imagen del Recurso (Portada) *</label>
                                        <input type="file" id="resource-image" name="image" accept="image/*">
                                        <p id="current-image" style="display: none;"></p>
                                    </div>
                                    <div class="form-group">
                                        <label for="resource-type">Tipo de Recurso *</label>
                                        <select id="resource-type" name="type" required>
                                            <option value="">Selecciona un tipo</option>
                                            <option value="libro">Libro</option>
                                            <option value="video">Video</option>
                                            <option value="documento">Documento</option>
                                            <option value="imagen">Imagen</option>
                                            <option value="otro">Otro</option>
                                        </select>
                                    </div>
                                    <div class="form-group" id="video-url-group" style="display: none;">
                                        <label for="resource-video-url">URL del Video (YouTube) *</label>
                                        <input type="url" id="resource-video-url" name="video_url" placeholder="https://www.youtube.com/watch?v=...">
                                        <div id="video-preview"></div>
                                    </div>
                                    <div class="form-group" id="video-duration-group" style="display: none;">
                                        <label for="resource-video-duration">Duración del Video (HH:MM:SS)</label>
                                        <input type="text" id="resource-video-duration" name="video_duration" placeholder="00:00:00">
                                    </div>
                                    <div class="form-group" id="file-upload-group">
                                        <label for="resource-file">Archivo (PDF, DOCX, Imagen, etc.)</label>
                                        <input type="file" id="resource-file" name="file" accept=".pdf,.doc,.docx,.ppt,.pptx,image/*">
                                        <p id="current-file" style="display: none;"></p>
                                    </div>
                                    <div class="form-group">
                                        <label>Categorías * (selecciona al menos una)</label>
                                        <div id="category-tags" class="category-tags"></div>
                                        <input type="hidden" id="selected-categories" name="categories">
                                    </div>
                                    <div class="form-group">
                                        <label for="tag-input">Etiquetas Personalizadas (opcional):</label>
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <input type="text" id="tag-input" placeholder="Escribe una etiqueta y presiona Enter, coma o Añadir">
                                            <button type="button" id="add-tag-button">Añadir</button>
                                        </div>
                                        <div id="custom-tags" style="margin-top: 10px;"></div>
                                        <input type="hidden" id="selected-tags" name="tags">
                                    </div>
                                    <div class="form-group">
                                        <label for="resource-publication-date">Fecha de Publicación</label>
                                        <input type="date" id="resource-publication-date" name="publication_date">
                                    </div>
                                    <div class="form-group">
                                        <label for="resource-relevance">Relevancia *</label>
                                        <select id="resource-relevance" name="relevance" required>
                                            <option value="Low">Baja</option>
                                            <option value="Medium" selected>Media</option>
                                            <option value="High">Alta</option>
                                            <option value="Critical">Crítica</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="resource-visibility">Visibilidad *</label>
                                        <select id="resource-visibility" name="visibility" required>
                                            <option value="Public">Pública</option>
                                            <option value="Private">Privada</option>
                                            <option value="Group">Solo para un grupo</option>
                                        </select>
                                    </div>
                                    <div class="form-group" id="group-select-group" style="display: none;">
                                        <label for="resource-group">Seleccionar Grupo *</label>
                                        <select id="resource-group" name="group_id"></select>
                                    </div>
                                    <div class="form-group">
                                        <label for="resource-language">Idioma *</label>
                                        <select id="resource-language" name="language" required>
                                            <option value="es">Español</option>
                                            <option value="en">Inglés</option>
                                            <option value="fr">Francés</option>
                                            <option value="de">Alemán</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="resource-license">Licencia *</label>
                                        <select id="resource-license" name="license" required>
                                            <option value="CC BY-SA">Creative Commons BY-SA</option>
                                            <option value="CC BY-NC">Creative Commons BY-NC</option>
                                            <option value="Public Domain">Dominio Público</option>
                                            <option value="All Rights Reserved">Todos los Derechos Reservados</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="resource-status">Estado *</label>
                                        <select id="resource-status" name="status" required>
                                            <option value="Published">Publicado</option>
                                            <option value="Draft">Borrador</option>
                                            <option value="Pending Review">Enviar para Revisión</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <h4>Vista Previa</h4>
                                        <div id="resource-preview" class="preview-card">
                                            <img id="preview-image" src="" alt="Portada">
                                            <div>
                                                <h3 id="preview-title"></h3>
                                                <p id="preview-author"></p>
                                                <p id="preview-description"></p>
                                                <p id="preview-meta"></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <button type="button" id="submit-resource" class="btn btn--primary">Subir Recurso</button>
                                        <div class="progress-bar" id="progress-bar">
                                            <div class="progress-bar-fill" id="progress-bar-fill"></div>
                                        </div>
                                    </div>
                                    <div id="error-message" class="error-message"></div>
                                    <div id="success-message" class="success-message"></div>
                                </form>
                            </div>
                        </div>
                        <div id="resources-grid" class="resources-grid">
                            <p>Cargando tus aportes...</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="perfil" class="tab-section">
                <div class="sub-tabs">
                    <button class="sub-tab active" data-subtab="datos-personales">Datos Personales</button>
                    <button class="sub-tab" data-subtab="seguridad">Seguridad</button>
                    <button class="sub-tab" data-subtab="notificaciones">Notificaciones</button>
                </div>
                <div class="sub-content">
                    <div id="datos-personales" class="sub-panel active">
                        <div class="panel-header-secondary">
                            <h2>Datos Personales</h2>
                            <p>Gestiona tu información personal</p>
                        </div>
                        <?php if (isset($_SESSION['success_message'])): ?>
                            <div class="success-message" style="display: block; margin-bottom: 20px; color: green;">
                                <?php echo htmlspecialchars($_SESSION['success_message']); ?>
                            </div>
                            <?php unset($_SESSION['success_message']); ?>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['error_message'])): ?>
                            <div class="error-message" style="display: block; margin-bottom: 20px; color: red;">
                                <?php echo htmlspecialchars($_SESSION['error_message']); ?>
                            </div>
                            <?php unset($_SESSION['error_message']); ?>
                        <?php endif; ?>
                        <div class="profile-container">
                            <div class="profile-sidebar">
                                <div class="profile-avatar">
                                    <img id="profile-image-preview" src="<?php echo $imagen_perfil; ?>" alt="Tu avatar">
                                    <button type="button" class="change-avatar-btn" onclick="document.getElementById('profile-image').click();"><i class="fas fa-camera"></i></button>
                                </div>
                                <div class="profile-badges">
                                    <h4>Insignias</h4>
                                    <div class="badges-container">
                                        <div class="badge" title="Colaborador Activo">
                                            <i class="fas fa-award"></i>
                                        </div>
                                        <div class="badge" title="Experto en Python">
                                            <i class="fab fa-python"></i>
                                        </div>
                                        <div class="badge" title="Mentor">
                                            <i class="fas fa-user-graduate"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="profile-main">
                                <form class="profile-form" action="../../backend/perfil/update.php" method="POST" enctype="multipart/form-data">
                                    <input type="file" id="profile-image" name="imagen" accept="image/*" style="display: none;" onchange="previewProfileImage(event)">
                                    <div class="form-group">
                                        <label for="nombre">Nombre</label>
                                        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre_usuario']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="email">Correo electrónico</label>
                                        <input type="email" id="email" name="correo" value="<?php echo htmlspecialchars($usuario['correo']); ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="telefono">Teléfono</label>
                                        <input type="tel" id="telefono" name="telefono" value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="profesion">Profesión</label>
                                        <input type="text" id="profesion" name="profesion" value="<?php echo htmlspecialchars($usuario['profesion'] ?? ''); ?>">
                                    </div>
                                    <div class="form-group">
                                        <label for="bio">Biografía</label>
                                        <textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($usuario['bio'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn--primary">Guardar cambios</button>
                                        <button type="reset" class="btn btn--outline">Cancelar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div id="seguridad" class="sub-panel">
                        <div class="panel-header-secondary">
                            <h2>Seguridad</h2>
                            <p>Gestiona la seguridad de tu cuenta</p>
                        </div>
                        <div class="security-container">
                            <div class="form-section">
                                <h3>Cambiar contraseña</h3>
                                <form class="security-form" action="../../backend/perfil/update.php" method="POST" onsubmit="return validatePasswordForm()">
                                    <input type="hidden" name="action" value="change_password">
                                    <div class="form-group">
                                        <label for="current-password">Contraseña actual</label>
                                        <input type="password" id="current-password" name="current_password" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="new-password">Nueva contraseña</label>
                                        <input type="password" id="new-password" name="new_password" required>
                                        <div class="password-requirements">
                                            La contraseña debe tener al menos: 1 mayúscula, 3 números y 1 carácter especial (ej. @, #, $).
                                        </div>
                                        <div class="password-requirements-checklist" id="password-requirements-checklist">
                                            <div id="uppercase-check"><span class="icon">✖</span> Mayúscula: Falta</div>
                                            <div id="numbers-check"><span class="icon">✖</span> 3 números: Falta</div>
                                            <div id="special-char-check"><span class="icon">✖</span> Carácter especial: Falta</div>
                                        </div>
                                        <div class="password-strength">
                                            <div class="strength-meter">
                                                <div class="strength-meter-fill" id="strength-meter-fill" style="width: 0%;"></div>
                                            </div>
                                            <span id="strength-text">Seguridad: Débil</span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="confirm-password">Confirmar nueva contraseña</label>
                                        <input type="password" id="confirm-password" name="confirm_password" required>
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn--primary">Cambiar contraseña</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div id="notificaciones" class="sub-panel">
                        <div class="panel-header-secondary">
                            <h2>Notificaciones</h2>
                            <p>Configura cómo quieres recibir notificaciones</p>
                        </div>
                        <div class="notifications-container">
                            <form class="notifications-form">
                                <div class="form-section">
                                    <h3>Notificaciones por correo electrónico</h3>
                                    <div class="notification-option">
                                        <div>
                                            <h4>Nuevos recursos</h4>
                                            <p>Recibe notificaciones cuando se añadan nuevos recursos en categorías de tu interés</p>
                                        </div>
                                        <label class="switch">
                                            <input type="checkbox" checked>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                    <div class="notification-option">
                                        <div>
                                            <h4>Comentarios en tus aportes</h4>
                                            <p>Recibe notificaciones cuando alguien comente en tus aportes</p>
                                        </div>
                                        <label class="switch">
                                            <input type="checkbox" checked>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                    <div class="notification-option">
                                        <div>
                                            <h4>Mensajes directos</h4>
                                            <p>Recibe notificaciones cuando alguien te envíe un mensaje directo</p>
                                        </div>
                                        <label class="switch">
                                            <input type="checkbox" checked>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                    <div class="notification-option">
                                        <div>
                                            <h4>Eventos</h4>
                                            <p>Recibe notificaciones sobre próximos eventos y webinars</p>
                                        </div>
                                        <label class="switch">
                                            <input type="checkbox">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-section">
                                    <h3>Notificaciones en la plataforma</h3>
                                    <div class="notification-option">
                                        <div>
                                            <h4>Mostrar notificaciones en tiempo real</h4>
                                            <p>Recibe notificaciones mientras estás usando la plataforma</p>
                                        </div>
                                        <label class="switch">
                                            <input type="checkbox" checked>
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                    <div class="notification-option">
                                        <div>
                                            <h4>Sonidos de notificación</h4>
                                            <p>Reproducir sonidos cuando recibas notificaciones</p>
                                        </div>
                                        <label class="switch">
                                            <input type="checkbox">
                                            <span class="slider"></span>
                                        </label>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn--primary">Guardar configuración</button>
                                    <button type="reset" class="btn btn--outline">Restaurar valores predeterminados</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <a href="../notificaciones/notificaciones.php">
        <button class="floating-notifications-btn">
            <i class="fas fa-bell"></i>
            <?php if ($unread_count > 0): ?>
                <span class="notification-dot"><?php echo $unread_count; ?></span>
            <?php endif; ?>
        </button>
    </a>

    <script>
        function previewProfileImage(event) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('profile-image-preview');
                const headerAvatar = document.querySelector('.avatar-img');
                output.src = reader.result;
                headerAvatar.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        function validatePasswordForm() {
            const newPassword = document.getElementById('new-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;

            if (newPassword !== confirmPassword) {
                alert('Las nuevas contraseñas no coinciden. Por favor, verifica.');
                return false;
            }

            if (!/[A-Z]/.test(newPassword)) {
                alert('La nueva contraseña debe contener al menos 1 mayúscula.');
                return false;
            }
            if ((newPassword.match(/\d/g) || []).length < 3) {
                alert('La nueva contraseña debe contener al menos 3 números.');
                return false;
            }
            if (!/[!@#$%^&*(),.?":{}|<>]/.test(newPassword)) {
                alert('La nueva contraseña debe contener al menos 1 carácter especial.');
                return false;
            }

            return true;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const newPasswordInput = document.getElementById('new-password');
            const strengthMeterFill = document.getElementById('strength-meter-fill');
            const strengthText = document.getElementById('strength-text');
            const uppercaseCheck = document.getElementById('uppercase-check');
            const numbersCheck = document.getElementById('numbers-check');
            const specialCharCheck = document.getElementById('special-char-check');

            if (newPasswordInput) {
                newPasswordInput.addEventListener('input', function() {
                    const password = this.value;
                    let strength = 0;

                    const hasUppercase = /[A-Z]/.test(password);
                    if (hasUppercase) {
                        uppercaseCheck.innerHTML = '<span class="icon fulfilled">✔</span> Mayúscula: Cumple';
                        uppercaseCheck.classList.remove('not-fulfilled');
                        uppercaseCheck.classList.add('fulfilled');
                        strength += 33;
                    } else {
                        uppercaseCheck.innerHTML = '<span class="icon not-fulfilled">✖</span> Mayúscula: Falta';
                        uppercaseCheck.classList.remove('fulfilled');
                        uppercaseCheck.classList.add('not-fulfilled');
                    }

                    const numberCount = (password.match(/\d/g) || []).length;
                    if (numberCount >= 3) {
                        numbersCheck.innerHTML = '<span class="icon fulfilled">✔</span> 3 números: Cumple';
                        numbersCheck.classList.remove('not-fulfilled');
                        numbersCheck.classList.add('fulfilled');
                        strength += 33;
                    } else {
                        numbersCheck.innerHTML = `<span class="icon not-fulfilled">✖</span> 3 números: Falta (${numberCount}/3)`;
                        numbersCheck.classList.remove('fulfilled');
                        numbersCheck.classList.add('not-fulfilled');
                    }

                    const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);
                    if (hasSpecialChar) {
                        specialCharCheck.innerHTML = '<span class="icon fulfilled">✔</span> Carácter especial: Cumple';
                        specialCharCheck.classList.remove('not-fulfilled');
                        specialCharCheck.classList.add('fulfilled');
                        strength += 34;
                    } else {
                        specialCharCheck.innerHTML = '<span class="icon not-fulfilled">✖</span> Carácter especial: Falta';
                        specialCharCheck.classList.remove('fulfilled');
                        specialCharCheck.classList.add('not-fulfilled');
                    }

                    strengthMeterFill.style.width = strength + '%';
                    if (strength < 50) {
                        strengthText.textContent = 'Seguridad: Débil';
                        strengthMeterFill.style.backgroundColor = '#ff4d4d';
                    } else if (strength < 90) {
                        strengthText.textContent = 'Seguridad: Media';
                        strengthMeterFill.style.backgroundColor = '#ffd700';
                    } else {
                        strengthText.textContent = 'Seguridad: Fuerte';
                        strengthMeterFill.style.backgroundColor = '#4CAF50';
                    }
                });
            }

            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }

            const mainTabs = document.querySelectorAll('.main-tab');
            const tabSections = document.querySelectorAll('.tab-section');
            mainTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    mainTabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    tabSections.forEach(section => section.classList.remove('active'));
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                });
            });

            const subTabs = document.querySelectorAll('.sub-tab');
            subTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const parentSection = this.closest('.tab-section');
                    parentSection.querySelectorAll('.sub-tab').forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    parentSection.querySelectorAll('.sub-panel').forEach(panel => panel.classList.remove('active'));
                    const subTabId = this.getAttribute('data-subtab');
                    parentSection.querySelector('#' + subTabId).classList.add('active');

                    if (subTabId === 'mis-favoritos') {
                        loadUserFavorites();
                    } else if (subTabId === 'recientes') {
                        loadRecentlyViewed();
                    } else if (subTabId === 'guardados') {
                        loadSavedResources();
                    } else if (subTabId === 'mis-aportes') {
                        loadUserResources();
                    }
                });
            });

            loadUserFavorites();

            const modal = document.getElementById('upload-modal');
            const modalTitle = document.getElementById('modal-title');
            const openModalBtn = document.getElementById('open-upload-modal');
            const closeModalBtn = document.getElementById('close-upload-modal');
            const uploadForm = document.getElementById('upload-resource-form');
            const errorMessage = document.getElementById('error-message');
            const successMessage = document.getElementById('success-message');
            const resourceType = document.getElementById('resource-type');
            const videoUrlGroup = document.getElementById('video-url-group');
            const videoDurationGroup = document.getElementById('video-duration-group');
            const fileUploadGroup = document.getElementById('file-upload-group');
            const videoUrlInput = document.getElementById('resource-video-url');
            const videoPreview = document.getElementById('video-preview');
            const visibilitySelect = document.getElementById('resource-visibility');
            const groupSelectGroup = document.getElementById('group-select-group');
            const resourceImage = document.getElementById('resource-image');
            const previewImage = document.getElementById('preview-image');
            const previewTitle = document.getElementById('preview-title');
            const previewAuthor = document.getElementById('preview-author');
            const previewDescription = document.getElementById('preview-description');
            const previewMeta = document.getElementById('preview-meta');
            const submitButton = document.getElementById('submit-resource');
            const progressBar = document.getElementById('progress-bar');
            const progressBarFill = document.getElementById('progress-bar-fill');
            const categoryTags = document.getElementById('category-tags');
            const customTagsContainer = document.getElementById('custom-tags');
            const selectedCategoriesInput = document.getElementById('selected-categories');
            const selectedTagsInput = document.getElementById('selected-tags');

            const tagInput = document.getElementById('tag-input');
            const addTagButton = document.getElementById('add-tag-button');

            if (!tagInput || !customTagsContainer || !selectedTagsInput || !addTagButton) {
                console.error('Error: No se encontraron los elementos de etiquetas (tag-input, custom-tags, selected-tags, add-tag-button).');
            }

            tagInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' || e.key === ',') {
                    e.preventDefault();
                    const tagName = this.value.trim();
                    if (tagName) {
                        addTagToContainer(tagName);
                        this.value = '';
                    }
                }
            });

            addTagButton.addEventListener('click', function() {
                const tagName = tagInput.value.trim();
                if (tagName) {
                    addTagToContainer(tagName);
                    tagInput.value = '';
                }
            });

            let selectedCategories = [];
            let customTags = [];
            let isEditing = false;

            openModalBtn.addEventListener('click', function() {
                isEditing = false;
                modalTitle.textContent = 'Subir Nuevo Recurso';
                submitButton.textContent = 'Subir Recurso';
                uploadForm.reset();
                modal.style.display = 'flex';
                loadCategories();
                loadGroups();
                updatePreview();
                videoUrlGroup.style.display = 'none';
                videoDurationGroup.style.display = 'none';
                fileUploadGroup.style.display = 'block';
                groupSelectGroup.style.display = 'none';
                selectedCategories = [];
                customTags = [];
                categoryTags.innerHTML = '';
                customTagsContainer.innerHTML = '';
                selectedCategoriesInput.value = '';
                selectedTagsInput.value = '';
                document.getElementById('current-image').style.display = 'none';
                document.getElementById('current-file').style.display = 'none';
            });

            closeModalBtn.addEventListener('click', function() {
                modal.style.display = 'none';
                uploadForm.reset();
                errorMessage.style.display = 'none';
                successMessage.style.display = 'none';
                videoUrlGroup.style.display = 'none';
                videoDurationGroup.style.display = 'none';
                fileUploadGroup.style.display = 'block';
                groupSelectGroup.style.display = 'none';
                selectedCategories = [];
                customTags = [];
                categoryTags.innerHTML = '';
                customTagsContainer.innerHTML = '';
                selectedCategoriesInput.value = '';
                selectedTagsInput.value = '';
                document.getElementById('current-image').style.display = 'none';
                document.getElementById('current-file').style.display = 'none';
            });

            window.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                    uploadForm.reset();
                    errorMessage.style.display = 'none';
                    successMessage.style.display = 'none';
                    videoUrlGroup.style.display = 'none';
                    videoDurationGroup.style.display = 'none';
                    fileUploadGroup.style.display = 'block';
                    groupSelectGroup.style.display = 'none';
                    selectedCategories = [];
                    customTags = [];
                    categoryTags.innerHTML = '';
                    customTagsContainer.innerHTML = '';
                    selectedCategoriesInput.value = '';
                    selectedTagsInput.value = '';
                    document.getElementById('current-image').style.display = 'none';
                    document.getElementById('current-file').style.display = 'none';
                }
            });

            resourceType.addEventListener('change', function() {
                const type = this.value;
                if (type === 'video') {
                    videoUrlGroup.style.display = 'block';
                    videoDurationGroup.style.display = 'block';
                    fileUploadGroup.style.display = 'none';
                    document.getElementById('resource-file').removeAttribute('required');
                } else {
                    videoUrlGroup.style.display = 'none';
                    videoDurationGroup.style.display = 'none';
                    fileUploadGroup.style.display = 'block';
                    if (!isEditing) {
                        document.getElementById('resource-file').setAttribute('required', '');
                    }
                }
                updatePreview();
            });

            videoUrlInput.addEventListener('input', function() {
                const url = this.value;
                const youtubeRegex = /^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/(watch\?v=)?([a-zA-Z0-9_-]{11})/;
                if (youtubeRegex.test(url)) {
                    const videoId = url.match(youtubeRegex)[5];
                    videoPreview.innerHTML = `<iframe width="100%" height="200" src="https://www.youtube.com/embed/${videoId}" frameborder="0" allowfullscreen></iframe>`;
                    errorMessage.style.display = 'none';
                } else {
                    videoPreview.innerHTML = '';
                    errorMessage.textContent = 'Por favor, ingresa una URL válida de YouTube.';
                    errorMessage.style.display = 'block';
                }
                updatePreview();
            });

            visibilitySelect.addEventListener('change', function() {
                if (this.value === 'Group') {
                    groupSelectGroup.style.display = 'block';
                    document.getElementById('resource-group').setAttribute('required', '');
                } else {
                    groupSelectGroup.style.display = 'none';
                    document.getElementById('resource-group').removeAttribute('required');
                }
            });

            resourceImage.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });

            document.getElementById('resource-title').addEventListener('input', updatePreview);
            document.getElementById('resource-author').addEventListener('input', updatePreview);
            document.getElementById('resource-description').addEventListener('input', updatePreview);
            document.getElementById('resource-publication-date').addEventListener('input', updatePreview);

            function updatePreview() {
                previewTitle.textContent = document.getElementById('resource-title').value || 'Título del Recurso';
                previewAuthor.textContent = 'Por ' + (document.getElementById('resource-author').value || 'Autor');
                previewDescription.textContent = document.getElementById('resource-description').value || 'Descripción del recurso.';
                const pubDate = document.getElementById('resource-publication-date').value;
                previewMeta.textContent = pubDate ? `Publicado el: ${pubDate}` : '';
                if (resourceType.value === 'video' && videoUrlInput.value) {
                    const match = videoUrlInput.value.match(/(?:v=)([a-zA-Z0-9_-]{11})/);
                    if (match && match[1]) {
                        previewImage.src = `https://img.youtube.com/vi/${match[1]}/maxresdefault.jpg`;
                    }
                }
            }

            function loadCategories() {
                return fetch('../../backend/gestionRecursos/get_categories.php')
                    .then(response => response.json())
                    .then(categories => {
                        categoryTags.innerHTML = '';
                        if (categories && Array.isArray(categories)) {
                            categories.forEach(category => {
                                const tag = document.createElement('span');
                                tag.className = 'tag';
                                tag.textContent = category.nombre;
                                tag.dataset.id = category.id;
                                tag.addEventListener('click', function() {
                                    const id = parseInt(this.dataset.id);
                                    if (selectedCategories.includes(id)) {
                                        selectedCategories = selectedCategories.filter(catId => catId !== id);
                                        this.classList.remove('selected');
                                    } else {
                                        selectedCategories.push(id);
                                        this.classList.add('selected');
                                    }
                                    selectedCategoriesInput.value = JSON.stringify(selectedCategories);
                                    console.log('Categorías después de clic:', selectedCategories);
                                });
                                categoryTags.appendChild(tag);
                            });
                        } else {
                            console.error('No se recibieron categorías válidas:', categories);
                        }
                    });
            }

            function addTagToContainer(tagName) {
                try {
                    tagName = tagName.trim();
                    if (!tagName) {
                        console.warn('Intento de agregar una etiqueta vacía.');
                        return;
                    }
                    if (customTags.includes(tagName)) {
                        console.warn(`La etiqueta "${tagName}" ya existe.`);
                        return;
                    }

                    if (tagName.length > 50) {
                        alert('Las etiquetas no pueden tener más de 50 caracteres.');
                        return;
                    }
                    if (!/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s-]+$/.test(tagName)) {
                        alert('Las etiquetas solo pueden contener letras, números, espacios o guiones.');
                        return;
                    }
                    customTags.push(tagName);
                    const tagElement = document.createElement('span');
                    tagElement.className = 'tag';
                    tagElement.textContent = tagName;
                    tagElement.addEventListener('click', function() {
                        const index = customTags.indexOf(tagName);
                        if (index !== -1) {
                            customTags.splice(index, 1);
                            tagElement.remove();
                            selectedTagsInput.value = JSON.stringify(customTags);
                        }
                    });
                    customTagsContainer.appendChild(tagElement);
                    selectedTagsInput.value = JSON.stringify(customTags);
                    console.log(`Etiqueta "${tagName}" añadida. customTags:`, customTags);
                } catch (error) {
                    console.error('Error al agregar la etiqueta:', error);
                    alert('Error al agregar la etiqueta. Revisa la consola para más detalles.');
                }
            }

            function loadGroups() {
                fetch('../../backend/gestionRecursos/get_user_groups.php')
                    .then(response => response.json())
                    .then(data => {
                        const groupSelect = document.getElementById('resource-group');
                        groupSelect.innerHTML = '<option value="">Selecciona un grupo</option>';
                        data.forEach(group => {
                            const option = document.createElement('option');
                            option.value = group.id;
                            option.textContent = group.nombre;
                            groupSelect.appendChild(option);
                        });
                    })
                    .catch(error => {
                        console.error('Error al cargar grupos:', error);
                        errorMessage.textContent = 'Error al cargar los grupos. Intenta de nuevo.';
                        errorMessage.style.display = 'block';
                    });
            }

            submitButton.addEventListener('click', function() {
                if (selectedCategories.length === 0) {
                    errorMessage.textContent = 'Por favor, selecciona al menos una categoría.';
                    errorMessage.style.display = 'block';
                    return;
                }

                const confirmation = confirm(`¿Estás seguro de que deseas ${isEditing ? 'actualizar' : 'subir'} este recurso?`);
                if (!confirmation) return;

                const formData = new FormData(uploadForm);
                formData.append('categories', JSON.stringify(selectedCategories));
                formData.append('tags', JSON.stringify(customTags));

                progressBar.style.display = 'block';
                progressBarFill.style.width = '0%';

                const url = isEditing ? '../../backend/gestionRecursos/update_resource.php' : '../../backend/gestionRecursos/upload_resource.php';

                fetch(url, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    progressBar.style.display = 'none';
                    if (data.success) {
                        successMessage.textContent = isEditing ? 'Recurso actualizado exitosamente.' : 'Recurso subido exitosamente.';
                        successMessage.style.display = 'block';
                        errorMessage.style.display = 'none';
                        uploadForm.reset();
                        videoUrlGroup.style.display = 'none';
                        videoDurationGroup.style.display = 'none';
                        fileUploadGroup.style.display = 'block';
                        groupSelectGroup.style.display = 'none';
                        selectedCategories = [];
                        customTags = [];
                        categoryTags.innerHTML = '';
                        customTagsContainer.innerHTML = '';
                        selectedCategoriesInput.value = '';
                        selectedTagsInput.value = '';
                        document.getElementById('current-image').style.display = 'none';
                        document.getElementById('current-file').style.display = 'none';
                        setTimeout(() => {
                            modal.style.display = 'none';
                            successMessage.style.display = 'none';
                            loadUserResources();
                        }, 2000);
                    } else {
                        errorMessage.textContent = data.message;
                        errorMessage.style.display = 'block';
                        successMessage.style.display = 'none';
                    }
                })
                .catch(error => {
                    progressBar.style.display = 'none';
                    errorMessage.textContent = 'Error al procesar el recurso. Intenta de nuevo.';
                    errorMessage.style.display = 'block';
                    successMessage.style.display = 'none';
                });
            });

            function loadUserFavorites() {
    fetch('../../backend/gestionRecursos/get_user_favorites.php')
        .then(response => response.json())
        .then(data => {
            const resourcesGrid = document.getElementById('favorites-grid');
            resourcesGrid.innerHTML = '';
            if (data.length === 0) {
                resourcesGrid.innerHTML = '<p>No tienes recursos favoritos aún.</p>';
            } else {
                data.forEach(resource => {
                    const resourceCard = document.createElement('div');
                    resourceCard.className = 'resource-card';
                    let viewUrl;
                    if (resource.tipo === 'video') {
                        viewUrl = `../repositorio/ver_video.php?id=${resource.id}`;
                    } else if (resource.tipo === 'libro') {
                        viewUrl = `../repositorio/ver_libro.php?id=${resource.id}`;
                    } else {
                        viewUrl = `../repositorio/ver_documento.php?id=${resource.id}`;
                    }

                    const etiquetasHTML = resource.etiquetas && resource.etiquetas.length > 0 
                        ? resource.etiquetas.map(tag => `<span class="tag">${tag}</span>`).join('')
                        : '';

                    resourceCard.innerHTML = `
                        <div class="resource-card__image-container">
                            <img src="${resource.portada}" alt="${resource.titulo}" class="resource-card__image" loading="lazy">
                            <div class="resource-card__format">${resource.tipo.toUpperCase()}</div>
                            ${resource.tipo === 'video' && resource.duracion ? `<div class="resource-card__duration"><i class="fas fa-clock"></i> ${resource.duracion}</div>` : ''}
                            ${resource.tipo === 'video' ? `<div class="resource-card__play-button"><i class="fas fa-play"></i></div>` : ''}
                        </div>
                        <div class="resource-card__content">
                            <div class="resource-card__category">${resource.categorias.join(', ')}</div>
                            <h3 class="resource-card__title">${resource.titulo}</h3>
                            <p class="resource-card__author">Por ${resource.autor}</p>
                            <div class="resource-card__meta">
                                <span><i class="fas fa-calendar-alt"></i> ${new Date(resource.fecha_publicacion).toLocaleDateString()}</span>
                                <span><i class="fas fa-star"></i> Favorito desde: ${new Date(resource.fecha_agregado).toLocaleDateString()}</span>
                            </div>
                            <div class="resource-card__tags">
                                ${etiquetasHTML}
                            </div>
                            <div class="resource-card__actions">
                                <a href="${viewUrl}" class="btn btn--primary view-resource" data-id="${resource.id}">
                                    <i class="fas fa-${resource.tipo === 'video' ? 'play-circle' : 'book-reader'}"></i>
                                    ${resource.tipo === 'video' ? 'Ver video' : 'Leer ahora'}
                                </a>
                                <a href="#" class="btn btn--outline remove-favorite" data-id="${resource.id}"><i class="fas fa-heart-broken"></i> Quitar favorito</a>
                            </div>
                        </div>
                    `;
                    resourcesGrid.appendChild(resourceCard);
                });

                resourcesGrid.querySelectorAll('.view-resource').forEach(button => {
                    button.addEventListener('click', (e) => {
                        const documentoId = button.getAttribute('data-id');
                        fetch('../../backend/gestionRecursos/add_to_recently_viewed.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `documento_id=${documentoId}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                console.log(data.message);
                            } else {
                                alert(data.message);
                            }
                        })
                        .catch(error => console.error('Error al registrar vista:', error));
                    });
                });

                resourcesGrid.querySelectorAll('.remove-favorite').forEach(button => {
                    button.addEventListener('click', (e) => {
                        e.preventDefault();
                        const documentoId = button.getAttribute('data-id');
                        if (confirm('¿Estás seguro de que deseas quitar este recurso de tus favoritos?')) {
                            fetch('../../backend/gestionRecursos/remove_from_favorites.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: `documento_id=${documentoId}`
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert(data.message);
                                    loadUserFavorites();
                                } else {
                                    alert(data.message);
                                }
                            })
                            .catch(error => console.error('Error al quitar favorito:', error));
                        }
                    });
                });
            }
        })
        .catch(error => {
            console.error('Error al cargar favoritos:', error);
            document.getElementById('favorites-grid').innerHTML = '<p>Error al cargar los recursos favoritos.</p>';
        });
}

function loadRecentlyViewed() {
    fetch('../../backend/gestionRecursos/get_recently_viewed.php')
        .then(response => response.json())
        .then(data => {
            const resourcesGrid = document.getElementById('recently-viewed-grid');
            resourcesGrid.innerHTML = '';
            if (data.length === 0) {
                resourcesGrid.innerHTML = '<p>No has visto recursos recientemente.</p>';
            } else {
                data.forEach(resource => {
                    const resourceCard = document.createElement('div');
                    resourceCard.className = 'resource-card';
                    let viewUrl;
                    if (resource.tipo === 'video') {
                        viewUrl = `../repositorio/ver_video.php?id=${resource.id}`;
                    } else if (resource.tipo === 'libro') {
                        viewUrl = `../repositorio/ver_libro.php?id=${resource.id}`;
                    } else {
                        viewUrl = `../repositorio/ver_documento.php?id=${resource.id}`;
                    }

                    const etiquetasHTML = resource.etiquetas && resource.etiquetas.length > 0 
                        ? resource.etiquetas.map(tag => `<span class="tag">${tag}</span>`).join('')
                        : '';

                    resourceCard.innerHTML = `
                        <div class="resource-card__image-container">
                            <img src="${resource.portada}" alt="${resource.titulo}" class="resource-card__image" loading="lazy">
                            <div class="resource-card__format">${resource.tipo.toUpperCase()}</div>
                            ${resource.tipo === 'video' && resource.duracion ? `<div class="resource-card__duration"><i class="fas fa-clock"></i> ${resource.duracion}</div>` : ''}
                            ${resource.tipo === 'video' ? `<div class="resource-card__play-button"><i class="fas fa-play"></i></div>` : ''}
                        </div>
                        <div class="resource-card__content">
                            <div class="resource-card__category">${resource.categorias.join(', ')}</div>
                            <h3 class="resource-card__title">${resource.titulo}</h3>
                            <p class="resource-card__author">Por ${resource.autor}</p>
                            <div class="resource-card__meta">
                                <span><i class="fas fa-calendar-alt"></i> ${new Date(resource.fecha_publicacion).toLocaleDateString()}</span>
                                <span><i class="fas fa-eye"></i> Visto el: ${new Date(resource.fecha_vista).toLocaleDateString()}</span>
                            </div>
                            <div class="resource-card__tags">
                                ${etiquetasHTML}
                            </div>
                            <div class="resource-card__actions">
                                <a href="${viewUrl}" class="btn btn--primary view-resource" data-id="${resource.id}">
                                    <i class="fas fa-${resource.tipo === 'video' ? 'play-circle' : 'book-reader'}"></i>
                                    ${resource.tipo === 'video' ? 'Ver video' : 'Leer ahora'}
                                </a>
                                <a href="#" class="btn btn--outline add-favorite" data-id="${resource.id}"><i class="fas fa-heart"></i> Añadir a favoritos</a>
                            </div>
                        </div>
                    `;
                    resourcesGrid.appendChild(resourceCard);
                });

                resourcesGrid.querySelectorAll('.view-resource').forEach(button => {
                    button.addEventListener('click', (e) => {
                        const documentoId = button.getAttribute('data-id');
                        fetch('../../backend/gestionRecursos/add_to_recently_viewed.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `documento_id=${documentoId}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                console.log(data.message);
                            } else {
                                alert(data.message);
                            }
                        })
                        .catch(error => console.error('Error al registrar vista:', error));
                    });
                });

                resourcesGrid.querySelectorAll('.add-favorite').forEach(button => {
                    button.addEventListener('click', (e) => {
                        e.preventDefault();
                        const documentoId = button.getAttribute('data-id');
                        fetch('../../backend/gestionRecursos/add_to_favorites.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `documento_id=${documentoId}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert(data.message);
                                loadRecentlyViewed();
                            } else {
                                alert(data.message);
                            }
                        })
                        .catch(error => console.error('Error al añadir a favoritos:', error));
                    });
                });
            }
        })
        .catch(error => {
            console.error('Error al cargar recursos recientes:', error);
            document.getElementById('recently-viewed-grid').innerHTML = '<p>Error al cargar los recursos vistos recientemente.</p>';
        });
}

function loadSavedResources() {
    fetch('../../backend/gestionRecursos/get_saved_resources.php')
        .then(response => response.json())
        .then(data => {
            const resourcesGrid = document.getElementById('saved-grid');
            resourcesGrid.innerHTML = '';
            if (data.length === 0) {
                resourcesGrid.innerHTML = '<p>No tienes recursos guardados aún.</p>';
            } else {
                data.forEach(resource => {
                    const resourceCard = document.createElement('div');
                    resourceCard.className = 'resource-card';
                    let viewUrl;
                    if (resource.tipo === 'video') {
                        viewUrl = `../repositorio/ver_video.php?id=${resource.id}`;
                    } else if (resource.tipo === 'libro') {
                        viewUrl = `../repositorio/ver_libro.php?id=${resource.id}`;
                    } else {
                        viewUrl = `../repositorio/ver_documento.php?id=${resource.id}`;
                    }

                    const etiquetasHTML = resource.etiquetas && resource.etiquetas.length > 0 
                        ? resource.etiquetas.map(tag => `<span class="tag">${tag}</span>`).join('')
                        : '';

                    resourceCard.innerHTML = `
                        <div class="resource-card__image-container">
                            <img src="${resource.portada}" alt="${resource.titulo}" class="resource-card__image" loading="lazy">
                            <div class="resource-card__format">${resource.tipo.toUpperCase()}</div>
                            ${resource.tipo === 'video' && resource.duracion ? `<div class="resource-card__duration"><i class="fas fa-clock"></i> ${resource.duracion}</div>` : ''}
                            ${resource.tipo === 'video' ? `<div class="resource-card__play-button"><i class="fas fa-play"></i></div>` : ''}
                        </div>
                        <div class="resource-card__content">
                            <div class="resource-card__category">${resource.categorias.join(', ')}</div>
                            <h3 class="resource-card__title">${resource.titulo}</h3>
                            <p class="resource-card__author">Por ${resource.autor}</p>
                            <div class="resource-card__meta">
                                <span><i class="fas fa-calendar-alt"></i> ${new Date(resource.fecha_publicacion).toLocaleDateString()}</span>
                                <span><i class="fas fa-bookmark"></i> Guardado el: ${new Date(resource.fecha_guardado).toLocaleDateString()}</span>
                            </div>
                            <div class="resource-card__tags">
                                ${etiquetasHTML}
                            </div>
                            <div class="resource-card__actions">
                                <a href="${viewUrl}" class="btn btn--primary view-resource" data-id="${resource.id}">
                                    <i class="fas fa-${resource.tipo === 'video' ? 'play-circle' : 'book-reader'}"></i>
                                    ${resource.tipo === 'video' ? 'Ver video' : 'Leer ahora'}
                                </a>
                                <a href="#" class="btn btn--outline remove-saved" data-id="${resource.id}"><i class="fas fa-bookmark"></i> Quitar de guardados</a>
                            </div>
                        </div>
                    `;
                    resourcesGrid.appendChild(resourceCard);
                });

                resourcesGrid.querySelectorAll('.view-resource').forEach(button => {
                    button.addEventListener('click', (e) => {
                        const documentoId = button.getAttribute('data-id');
                        fetch('../../backend/gestionRecursos/add_to_recently_viewed.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `documento_id=${documentoId}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                console.log(data.message);
                            } else {
                                alert(data.message);
                            }
                        })
                        .catch(error => console.error('Error al registrar vista:', error));
                    });
                });

                resourcesGrid.querySelectorAll('.remove-saved').forEach(button => {
                    button.addEventListener('click', (e) => {
                        e.preventDefault();
                        const documentoId = button.getAttribute('data-id');
                        if (confirm('¿Estás seguro de que deseas quitar este recurso de tus guardados?')) {
                            fetch('../../backend/gestionRecursos/remove_from_saved.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: `documento_id=${documentoId}`
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert(data.message);
                                    loadSavedResources();
                                } else {
                                    alert(data.message);
                                }
                            })
                            .catch(error => console.error('Error al quitar de guardados:', error));
                        }
                    });
                });
            }
        })
        .catch(error => {
            console.error('Error al cargar recursos guardados:', error);
            document.getElementById('saved-grid').innerHTML = '<p>Error al cargar los recursos guardados.</p>';
        });
}

function loadUserResources() {
    fetch('../../backend/gestionRecursos/get_user_resources.php')
        .then(response => response.json())
        .then(data => {
            const resourcesGrid = document.getElementById('resources-grid');
            resourcesGrid.innerHTML = '';
            if (data.length === 0) {
                resourcesGrid.innerHTML = '<p>No has subido recursos aún.</p>';
            } else {
                data.forEach(resource => {
                    const resourceCard = document.createElement('div');
                    resourceCard.className = 'resource-card';
                    let viewUrl;
                    if (resource.tipo === 'video') {
                        viewUrl = `../repositorio/ver_video.php?id=${resource.id}`;
                    } else if (resource.tipo === 'libro') {
                        viewUrl = `../repositorio/ver_libro.php?id=${resource.id}`;
                    } else {
                        viewUrl = `../repositorio/ver_documento.php?id=${resource.id}`;
                    }

                    const etiquetasHTML = resource.etiquetas && resource.etiquetas.length > 0 
                        ? resource.etiquetas.map(tag => `<span class="tag">${tag}</span>`).join('')
                        : '';

                    resourceCard.innerHTML = `
                        <div class="resource-card__image-container">
                            <img src="${resource.portada}" alt="${resource.titulo}" class="resource-card__image" loading="lazy">
                            <div class="resource-card__format">${resource.tipo.toUpperCase()}</div>
                            ${resource.tipo === 'video' && resource.duracion ? `<div class="resource-card__duration"><i class="fas fa-clock"></i> ${resource.duracion}</div>` : ''}
                            ${resource.tipo === 'video' ? `<div class="resource-card__play-button"><i class="fas fa-play"></i></div>` : ''}
                        </div>
                        <div class="resource-card__content">
                            <div class="resource-card__category">${resource.categorias.join(', ')}</div>
                            <h3 class="resource-card__title">${resource.titulo}</h3>
                            <p class="resource-card__author">Por ${resource.autor}</p>
                            <div class="resource-card__meta">
                                <span><i class="fas fa-calendar-alt"></i> ${new Date(resource.fecha_publicacion).toLocaleDateString()}</span>
                            </div>
                            <div class="resource-card__tags">
                                ${etiquetasHTML}
                            </div>
                            <div class="resource-card__actions">
                                <a href="${viewUrl}" class="btn btn--primary view-resource" data-id="${resource.id}">
                                    <i class="fas fa-${resource.tipo === 'video' ? 'play-circle' : 'book-reader'}"></i>
                                    ${resource.tipo === 'video' ? 'Ver video' : 'Leer ahora'}
                                </a>
                                <button class="btn btn--outline edit-resource" data-id="${resource.id}"><i class="fas fa-edit"></i> Editar</button>
                                <button class="btn btn--outline delete-resource" data-id="${resource.id}"><i class="fas fa-trash"></i> Eliminar</button>
                            </div>
                        </div>
                    `;
                    resourcesGrid.appendChild(resourceCard);
                });

                resourcesGrid.querySelectorAll('.edit-resource').forEach(button => {
                    button.addEventListener('click', (e) => {
                        const resourceId = button.getAttribute('data-id');
                        editResource(resourceId);
                    });
                });

                resourcesGrid.querySelectorAll('.delete-resource').forEach(button => {
                    button.addEventListener('click', function() {
                        const resourceId = this.dataset.id;
                        console.log('Intentando eliminar recurso con ID:', resourceId);
                        if (!resourceId || isNaN(resourceId) || parseInt(resourceId) <= 0) {
                            alert('Error: ID de recurso no válido.');
                            return;
                        }

                        if (confirm('¿Estás seguro de que deseas eliminar este recurso?')) {
                            fetch('../../backend/gestionRecursos/delete_resource.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({ documento_id: parseInt(resourceId) })
                            })
                            .then(response => response.json())
                            .then(data => {
                                console.log('Respuesta del servidor:', data);
                                if (data.success) {
                                    alert(data.message);
                                    loadUserResources();
                                } else {
                                    alert(data.message);
                                }
                            })
                            .catch(error => {
                                console.error('Error al eliminar:', error);
                                alert('Error al eliminar el recurso.');
                            });
                        }
                    });
                });
            }
        })
        .catch(error => {
            console.error('Error al cargar recursos:', error);
            document.getElementById('resources-grid').innerHTML = '<p>Error al cargar tus aportes.</p>';
        });
}

            function editResource(resourceId) {
                fetch(`../../backend/gestionRecursos/get_resource.php?resource_id=${resourceId}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('Respuesta del servidor:', data);
                        if (data.success) {
                            const resource = data.resource;
                            isEditing = true;
                            modal.style.display = 'flex';
                            modalTitle.textContent = 'Editar Recurso';
                            submitButton.textContent = 'Actualizar Recurso';

                            document.getElementById('resource-id').value = resource.id;
                            document.getElementById('resource-title').value = resource.titulo;
                            document.getElementById('resource-description').value = resource.descripcion || '';
                            document.getElementById('resource-author').value = resource.autor || '';
                            document.getElementById('resource-type').value = resource.tipo;
                            document.getElementById('resource-publication-date').value = resource.fecha_publicacion || '';
                            document.getElementById('resource-relevance').value = resource.relevancia || 'Medium';
                            document.getElementById('resource-visibility').value = resource.visibilidad || 'Public';
                            document.getElementById('resource-language').value = resource.idioma || 'es';
                            document.getElementById('resource-license').value = resource.licencia || 'CC BY-SA';
                            document.getElementById('resource-status').value = resource.estado || 'Published';

                            const currentImage = document.getElementById('current-image');
                            const currentFile = document.getElementById('current-file');
                            if (resource.portada) {
                                currentImage.textContent = `Portada actual: ${resource.portada.split('/').pop()}`;
                                currentImage.style.display = 'block';
                                previewImage.src = resource.portada;
                            }
                            if (resource.url_archivo) {
                                currentFile.textContent = `Archivo actual: ${resource.url_archivo.split('/').pop()}`;
                                currentFile.style.display = 'block';
                            }

                            if (resource.tipo === 'video') {
                                videoUrlGroup.style.display = 'block';
                                videoDurationGroup.style.display = 'block';
                                fileUploadGroup.style.display = 'none';
                                document.getElementById('resource-file').removeAttribute('required');
                                document.getElementById('resource-video-url').value = resource.url_video || '';
                                document.getElementById('resource-video-duration').value = resource.duracion || '';
                            } else {
                                videoUrlGroup.style.display = 'none';
                                videoDurationGroup.style.display = 'none';
                                fileUploadGroup.style.display = 'block';
                                document.getElementById('resource-image').removeAttribute('required');
                                document.getElementById('resource-file').removeAttribute('required');
                            }

                            if (resource.visibilidad === 'Group') {
                                groupSelectGroup.style.display = 'block';
                                document.getElementById('resource-group').value = resource.grupo_id || '';
                            } else {
                                groupSelectGroup.style.display = 'none';
                            }

                            selectedCategories = resource.categorias.map(id => parseInt(id));
                            loadCategories().then(() => {
                                categoryTags.querySelectorAll('.tag').forEach(tag => {
                                    if (selectedCategories.includes(parseInt(tag.dataset.id))) {
                                        tag.classList.add('selected');
                                    }
                                });
                                selectedCategoriesInput.value = JSON.stringify(selectedCategories);
                                console.log('Categorías seleccionadas:', selectedCategories);
                            }).catch(error => {
                                console.error('Error al cargar categorías:', error);
                            });

                            customTags = [];
                            customTagsContainer.innerHTML = '';
                            resource.etiquetas.forEach(tagName => {
                                if (tagName && !customTags.includes(tagName)) {
                                    customTags.push(tagName);
                                    const tagElement = document.createElement('span');
                                    tagElement.className = 'tag';
                                    tagElement.textContent = tagName;
                                    tagElement.addEventListener('click', function() {
                                        const index = customTags.indexOf(tagName);
                                        customTags.splice(index, 1);
                                        this.remove();
                                        selectedTagsInput.value = JSON.stringify(customTags);
                                    });
                                    customTagsContainer.appendChild(tagElement);
                                }
                            });
                            selectedTagsInput.value = JSON.stringify(customTags);

                            loadGroups();
                            updatePreview();
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error al cargar recurso:', error);
                        alert('Error al cargar el recurso para edición.');
                    });
            }
        });
    </script>

</body>

</html>