<?php
session_start();
require_once "../../database/conexionDB.php";

// Verificar si se proporcionó un ID de documento
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Redirigiendo porque no se proporcionó un ID válido.<br>";
    header("Location: ../repositorio/repositorio.php");
    exit();
}

$documento_id = intval($_GET['id']);
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;

try {
    $db = conexionDB::getConexion();
    
    // Obtener información del documento
    $query = "
        SELECT d.*, u.nombre_usuario AS autor_nombre,
               COALESCE(ARRAY_AGG(c.nombre) FILTER (WHERE c.nombre IS NOT NULL), '{}') AS categorias,
               COALESCE(ARRAY_AGG(e.nombre) FILTER (WHERE e.nombre IS NOT NULL), '{}') AS etiquetas
        FROM documentos d
        JOIN usuarios u ON d.autor_id = u.id
        LEFT JOIN documento_categorias dc ON d.id = dc.documento_id
        LEFT JOIN categorias c ON dc.categoria_id = c.id
        LEFT JOIN documento_etiqueta de ON d.id = de.documento_id
        LEFT JOIN etiquetas e ON de.etiqueta_id = e.id
        WHERE d.id = :documento_id
        GROUP BY d.id, u.nombre_usuario
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute([':documento_id' => $documento_id]);
    $documento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$documento) {
        echo "Redirigiendo porque no se encontró el documento con ID $documento_id.<br>";
        header("Location: ../repositorio/repositorio.php");
        exit();
    }
    
    // Verificar permisos de acceso (visibilidad privada)
    if ($documento['visibilidad'] === 'Private' && $documento['autor_id'] != $usuario_id) {
        echo "Redirigiendo porque el documento es privado y no eres el autor (usuario_id: $usuario_id, autor_id: {$documento['autor_id']}).<br>";
        header("Location: ../repositorio/repositorio.php");
        exit();
    }
    
    // Verificar permisos de acceso (visibilidad de grupo)
    if ($documento['visibilidad'] === 'Group' && $usuario_id) {
        $query = "SELECT COUNT(*) FROM usuario_grupo WHERE usuario_id = :usuario_id AND grupo_id = :grupo_id";
        $stmt = $db->prepare($query);
        $stmt->execute([':usuario_id' => $usuario_id, ':grupo_id' => $documento['grupo_id']]);
        $es_miembro = $stmt->fetchColumn();
        
        if (!$es_miembro) {
            echo "Redirigiendo porque el documento es de grupo y no eres miembro del grupo (usuario_id: $usuario_id, grupo_id: {$documento['grupo_id']}).<br>";
            header("Location: ../repositorio/repositorio.php");
            exit();
        }
    }
    
    // Procesar categorías y etiquetas
    $documento['categorias'] = $documento['categorias'] === '{}'
        ? []
        : array_map('trim', explode(',', trim($documento['categorias'], '{}')));
    
    $documento['etiquetas'] = $documento['etiquetas'] === '{}'
        ? []
        : array_map('trim', explode(',', trim($documento['etiquetas'], '{}')));
    
    // Registrar vista
    if ($usuario_id) {
        $query = "
            INSERT INTO recientemente_vistos (usuario_id, documento_id, fecha_vista)
            VALUES (:usuario_id, :documento_id, NOW())
            ON CONFLICT (usuario_id, documento_id) DO UPDATE
            SET fecha_vista = NOW()
        ";
        $stmt = $db->prepare($query);
        $stmt->execute([':usuario_id' => $usuario_id, ':documento_id' => $documento_id]);
    }
    
    // Obtener comentarios directamente asociados al documento
    $query = "
        SELECT c.*, u.nombre_usuario
        FROM comentarios c
        JOIN usuarios u ON c.autor_id = u.id
        WHERE c.documento_id = :documento_id
        ORDER BY c.fecha_creacion DESC
    ";
    $stmt = $db->prepare($query);
    $stmt->execute([':documento_id' => $documento_id]);
    $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Verificar si está en favoritos
    $es_favorito = false;
    if ($usuario_id) {
        $query = "SELECT COUNT(*) FROM favoritos WHERE usuario_id = :usuario_id AND documento_id = :documento_id";
        $stmt = $db->prepare($query);
        $stmt->execute([':usuario_id' => $usuario_id, ':documento_id' => $documento_id]);
        $es_favorito = $stmt->fetchColumn() > 0;
    }
    
    // Obtener todas las categorías disponibles para el formulario de edición
    $query = "SELECT id, nombre FROM categorias ORDER BY nombre";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $categorias_disponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener todos los grupos disponibles
    $query = "SELECT id, nombre FROM grupos WHERE id IN (SELECT grupo_id FROM usuario_grupo WHERE usuario_id = :usuario_id)";
    $stmt = $db->prepare($query);
    $stmt->execute([':usuario_id' => $usuario_id]);
    $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Error al cargar el documento: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($documento['titulo']); ?> - El Rincón de ADSO</title>
    <link rel="icon" type="image/png" href="../inicio/img/icono.png">
    <link rel="stylesheet" href="../repositorio/css/repositorio.css">
    <link rel="stylesheet" href="../repositorio/css/ver_recurso.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                <li class="navbar__menu-item navbar__menu-item--active"><a href="../repositorio/repositorio.php">Repositorio</a></li>
                <li class="navbar__menu-item"><a href="../inicio/index.php#buscar">Búsquedas</a></li>
                <li class="navbar__menu-item"><a href="../inicio/index.php#nosotros">Nosotros</a></li>
                <li class="navbar__menu-item"><a href="../inicio/index.php#recientes">Recientes</a></li>
                <li class="navbar__menu-item"><a href="../inicio/index.php#comunidad">Comunidad</a></li>
                <?php if ($usuario_id): ?>
                <li class="navbar__profile">
                    <i class="fas fa-user-circle navbar__profile-icon"></i>
                    <div class="navbar__profile-menu">
                        <a href="../panel/panel-usuario.php">Ver Perfil</a>
                        <form action="../../backend/logout.php" method="POST">
                            <button type="submit">Cerrar Sesión</button>
                        </form>
                    </div>
                </li>
                <?php else: ?>
                <li class="navbar__menu-item"><a href="../login/login.php">Iniciar Sesión</a></li>
                <?php endif; ?>
            </ul>
            <button id="mobile-menu-button" class="navbar__toggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        <div id="mobile-menu" class="navbar__mobile container hidden">
            <ul>
                <li class="navbar__menu-item"><a href="../inicio/index.php">Inicio</a></li>
                <li class="navbar__menu-item navbar__menu-item--active"><a href="../repositorio/repositorio.php">Repositorio</a></li>
                <li class="navbar__menu-item"><a href="../inicio/index.php#buscar">Búsquedas</a></li>
                <li class="navbar__menu-item"><a href="../inicio/index.php#nosotros">Nosotros</a></li>
                <li class="navbar__menu-item"><a href="../inicio/index.php#recientes">Recientes</a></li>
                <li class="navbar__menu-item"><a href="../inicio/index.php#comunidad">Comunidad</a></li>
                <?php if ($usuario_id): ?>
                <li class="navbar__mobile-item"><a href="../panel/panel-usuario.php">Ver Perfil</a></li>
                <li class="navbar__mobile-item">
                    <form action="../../backend/logout.php" method="POST">
                        <button type="submit" class="navbar__menu-item--button">Cerrar Sesión</button>
                    </form>
                </li>
                <?php else: ?>
                <li class="navbar__menu-item"><a href="../login/login.php">Iniciar Sesión</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <main class="resource-viewer">
        <div class="container">
            <div class="resource-header">
                <div class="resource-header__actions">
                    <button class="btn-volver" onclick="history.back()">
                        <i class="fas fa-arrow-left"></i> Volver
                    </button>
                    
                    <?php if ($usuario_id): ?>
                    <div class="resource-header__buttons">
                        <button id="btn-favorito" class="btn <?php echo $es_favorito ? 'btn--danger' : 'btn--outline'; ?>" data-id="<?php echo $documento_id; ?>">
                            <i class="fas <?php echo $es_favorito ? 'fa-heart-broken' : 'fa-heart'; ?>"></i>
                            <?php echo $es_favorito ? 'Quitar de favoritos' : 'Añadir a favoritos'; ?>
                        </button>
                        
                        <button id="btn-guardar" class="btn btn--outline" data-id="<?php echo $documento_id; ?>">
                            <i class="fas fa-bookmark"></i> Guardar para después
                        </button>
                        
                        <?php if ($documento['autor_id'] == $usuario_id): ?>
                        <button id="btn-editar" class="btn btn--primary" data-id="<?php echo $documento_id; ?>">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="resource-header__meta">
                    <div class="resource-type">
                        <span class="badge badge--primary"><?php echo htmlspecialchars(ucfirst($documento['tipo'])); ?></span>
                        <?php foreach ($documento['categorias'] as $categoria): ?>
                        <span class="badge"><?php echo htmlspecialchars($categoria); ?></span>
                        <?php endforeach; ?>
                    </div>
                    
                    <h1 class="resource-title"><?php echo htmlspecialchars($documento['titulo']); ?></h1>
                    
                    <div class="resource-author__info">
                        <span class="resource-author__name"><?php echo htmlspecialchars($documento['autor_nombre']); ?></span>
                        <span class="resource-author__date">Publicado el <?php echo date('d/m/Y', strtotime($documento['fecha_publicacion'])); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="resource-content">
                <div class="resource-document">
                    <div class="resource-section">
                        <h2 class="resource-section__title"><?php echo htmlspecialchars(ucfirst($documento['tipo'])); ?></h2>
                        <div class="resource-document__container">
                            <?php if (!empty($documento['portada'])): ?>
                            <img src="<?php echo htmlspecialchars($documento['portada']); ?>" alt="<?php echo htmlspecialchars($documento['titulo']); ?>" class="resource-document__cover">
                            <?php endif; ?>
                            
                            <?php if (!empty($documento['url_archivo'])): ?>
                                <?php
                                // Determinar el tipo de archivo según la extensión
                                $extension = strtolower(pathinfo($documento['url_archivo'], PATHINFO_EXTENSION));
                                $is_image = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                $is_pdf = $extension === 'pdf';
                                ?>
                                
                                <?php if ($is_image): ?>
                                <div class="resource-document__image-container">
                                    <img src="<?php echo htmlspecialchars($documento['url_archivo']); ?>" alt="<?php echo htmlspecialchars($documento['titulo']); ?>" class="resource-document__image">
                                </div>
                                <?php elseif ($is_pdf): ?>
                                <div class="resource-document__viewer">
                                    <embed src="<?php echo htmlspecialchars($documento['url_archivo']); ?>" type="application/pdf" width="100%" height="100%">
                                </div>
                                <?php endif; ?>
                                
                                <div class="resource-document__download">
                                    <a href="<?php echo htmlspecialchars($documento['url_archivo']); ?>" download class="btn btn--primary">
                                        <i class="fas fa-download"></i> Descargar Archivo
                                    </a>
                                </div>
                            <?php else: ?>
                            <div class="resource-document__no-file">
                                <i class="fas fa-exclamation-circle resource-document__no-file-icon"></i>
                                <p>No hay archivo disponible para este recurso.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="resource-details">
                    <div class="resource-section">
                        <h2 class="resource-section__title">Información General</h2>
                        <div class="resource-info">
                            <div class="resource-info__item">
                                <span class="resource-info__label">Autor:</span>
                                <span class="resource-info__value"><?php echo htmlspecialchars($documento['autor']); ?></span>
                            </div>
                            
                            <div class="resource-info__item">
                                <span class="resource-info__label">Fecha de Publicación:</span>
                                <span class="resource-info__value"><?php echo date('d/m/Y', strtotime($documento['fecha_publicacion'])); ?></span>
                            </div>
                            
                            <div class="resource-info__item">
                                <span class="resource-info__label">Idioma:</span>
                                <span class="resource-info__value"><?php echo htmlspecialchars($documento['idioma']); ?></span>
                            </div>
                            
                            <div class="resource-info__item">
                                <span class="resource-info__label">Licencia:</span>
                                <span class="resource-info__value"><?php echo htmlspecialchars($documento['licencia']); ?></span>
                            </div>
                            
                            <div class="resource-info__item">
                                <span class="resource-info__label">Relevancia:</span>
                                <span class="resource-info__value"><?php echo htmlspecialchars($documento['relevancia']); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="resource-section">
                        <h2 class="resource-section__title">Descripción</h2>
                        <div class="resource-description">
                            <?php if (!empty($documento['descripcion'])): ?>
                            <p><?php echo nl2br(htmlspecialchars($documento['descripcion'])); ?></p>
                            <?php else: ?>
                            <p>No hay descripción disponible para este documento.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="resource-section">
                        <h2 class="resource-section__title">Etiquetas</h2>
                        <div class="resource-tags">
                            <?php if (!empty($documento['etiquetas'])): ?>
                                <?php foreach ($documento['etiquetas'] as $etiqueta): ?>
                                <span class="tag"><?php echo htmlspecialchars($etiqueta); ?></span>
                                <?php endforeach; ?>
                            <?php else: ?>
                            <p>No hay etiquetas asociadas a este documento.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Sección de comentarios -->
                <div class="resource-comments">
                    <div class="resource-section">
                        <h2 class="resource-section__title">Comentarios</h2>
                        
                        <?php if ($usuario_id): ?>
                        <div class="comment-form">
                            <form id="form-comentario" data-documento-id="<?php echo $documento_id; ?>">
                                <div class="comment-form__input">
                                    <textarea name="contenido" placeholder="Escribe un comentario..." required></textarea>
                                    <button type="submit" class="btn btn--primary" id="btn-enviar-comentario">
                                        <i class="fas fa-paper-plane"></i> Enviar comentario
                                    </button>
                                    <div id="comentario-status" class="comment-status" style="display: none;">
                                        <div class="spinner"><i class="fas fa-spinner fa-spin"></i> Enviando...</div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <?php else: ?>
                        <div class="comment-login-prompt">
                            <p>Debes <a href="../login/login.php">iniciar sesión</a> para comentar.</p>
                        </div>
                        <?php endif; ?>
                        
                        <div class="comments-list" id="comments-container">
                            <?php if (empty($comentarios)): ?>
                            <div class="no-comments">
                                <p>No hay comentarios aún. ¡Sé el primero en comentar!</p>
                            </div>
                            <?php else: ?>
                                <?php foreach ($comentarios as $comentario): ?>
                                <div class="comment" data-id="<?php echo $comentario['id']; ?>">
                                    <div class="comment__content">
                                        <div class="comment__header">
                                            <span class="comment__author"><?php echo htmlspecialchars($comentario['nombre_usuario']); ?></span>
                                            <span class="comment__date"><?php echo date('d/m/Y H:i', strtotime($comentario['fecha_creacion'])); ?></span>
                                        </div>
                                        <div class="comment__text">
                                            <p><?php echo nl2br(htmlspecialchars($comentario['contenido'])); ?></p>
                                        </div>
                                        <?php if ($usuario_id == $comentario['autor_id']): ?>
                                        <div class="comment__actions">
                                            <button class="btn-delete-comment" data-id="<?php echo $comentario['id']; ?>">
                                                <i class="fas fa-trash-alt"></i> Eliminar
                                            </button>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal para edición -->
    <div id="edit-modal" class="modal">
        <div class="modal-content">
            <span class="close">×</span>
            <h2 id="modal-title">Editar Recurso</h2>
            <form id="edit-resource-form" enctype="multipart/form-data">
                <input type="hidden" id="resource-id" name="resource_id">
                
                <div class="form-group">
                    <label for="resource-title">Título:</label>
                    <input type="text" id="resource-title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label for="resource-description">Descripción:</label>
                    <textarea id="resource-description" name="description"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="resource-author">Autor:</label>
                    <input type="text" id="resource-author" name="author" required>
                </div>
                
                <div class="form-group">
                    <label for="resource-type">Tipo:</label>
                    <select id="resource-type" name="type" required>
                        <option value="documento">Documento</option>
                        <option value="imagen">Imagen</option>
                        <option value="libro">Libro</option>
                        <option value="video">Video</option>
                    </select>
                </div>
                
                <div class="form-group" id="video-url-group" style="display: none;">
                    <label for="resource-video-url">URL del Video (si aplica):</label>
                    <input type="url" id="resource-video-url" name="video_url">
                </div>
                
                <div class="form-group" id="video-duration-group" style="display: none;">
                    <label for="resource-video-duration">Duración del Video (HH:MM:SS, si aplica):</label>
                    <input type="text" id="resource-video-duration" name="video_duration">
                </div>
                
                <div class="form-group">
                    <label for="resource-publication-date">Fecha de Publicación:</label>
                    <input type="date" id="resource-publication-date" name="publication_date" required>
                </div>
                
                <div class="form-group">
                    <label for="resource-relevance">Relevancia:</label>
                    <select id="resource-relevance" name="relevance" required>
                        <option value="Low">Baja</option>
                        <option value="Medium">Media</option>
                        <option value="High">Alta</option>
                        <option value="Critical">Crítica</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="resource-visibility">Visibilidad:</label>
                    <select id="resource-visibility" name="visibility" required>
                        <option value="Public">Público</option>
                        <option value="Private">Privado</option>
                        <option value="Group">Grupo</option>
                    </select>
                </div>
                
                <div class="form-group" id="group-select-group" style="display: none;">
                    <label for="resource-group">Grupo (si aplica):</label>
                    <select id="resource-group" name="group_id">
                        <option value="">Ninguno</option>
                        <?php foreach ($grupos as $grupo): ?>
                        <option value="<?php echo $grupo['id']; ?>"><?php echo htmlspecialchars($grupo['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="resource-language">Idioma:</label>
                    <input type="text" id="resource-language" name="language" required>
                </div>
                
                <div class="form-group">
                    <label for="resource-license">Licencia:</label>
                    <input type="text" id="resource-license" name="license" required>
                </div>
                
                <div class="form-group">
                    <label for="resource-status">Estado:</label>
                    <select id="resource-status" name="status" required>
                        <option value="Draft">Borrador</option>
                        <option value="Pending Review">Pendiente de Revisión</option>
                        <option value="Published">Publicado</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="resource-image">Imagen de Portada (dejar en blanco para mantener la actual):</label>
                    <input type="file" id="resource-image" name="image" accept="image/*">
                    <p id="current-image" style="display: none;"></p>
                    <img id="preview-image" class="preview-image" alt="Vista previa">
                </div>
                
                <div class="form-group" id="file-upload-group">
                    <label for="resource-file">Archivo (dejar en blanco para mantener el actual):</label>
                    <input type="file" id="resource-file" name="file">
                    <p id="current-file" style="display: none;"></p>
                </div>
                
                <div class="form-group">
                    <label>Categorías:</label>
                    <div id="category-tags">
                        <?php foreach ($categorias_disponibles as $categoria): ?>
                        <span class="tag" data-id="<?php echo $categoria['id']; ?>"><?php echo htmlspecialchars($categoria['nombre']); ?></span>
                        <?php endforeach; ?>
                    </div>
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
                
                <div class="form-actions">
                    <button type="submit" id="submit-button">Actualizar Recurso</button>
                    <button type="button" id="cancel-button">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="footer__content">
                <div class="footer__logo">
                    <i class="fas fa-book-open"></i>
                    <span>El Rincón de ADSO</span>
                </div>
                <p class="footer__copyright">© <?php echo date('Y'); ?> El Rincón de ADSO. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="../lib/Bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Menú móvil
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }
            
            // Menú de perfil
            document.querySelectorAll('.navbar__profile-icon').forEach(icon => {
                icon.addEventListener('click', function() {
                    const menu = this.nextElementSibling;
                    menu.classList.toggle('active');
                });
            });
            
            // Favoritos
            const btnFavorito = document.getElementById('btn-favorito');
            if (btnFavorito) {
                btnFavorito.addEventListener('click', function() {
                    const documentoId = this.getAttribute('data-id');
                    const esFavorito = this.classList.contains('btn--danger');
                    const endpoint = esFavorito 
                        ? '../../backend/gestionRecursos/remove_from_favorites.php' 
                        : '../../backend/gestionRecursos/add_to_favorites.php';
                    
                    fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `documento_id=${documentoId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (esFavorito) {
                                btnFavorito.classList.remove('btn--danger');
                                btnFavorito.classList.add('btn--outline');
                                btnFavorito.innerHTML = '<i class="fas fa-heart"></i> Añadir a favoritos';
                            } else {
                                btnFavorito.classList.remove('btn--outline');
                                btnFavorito.classList.add('btn--danger');
                                btnFavorito.innerHTML = '<i class="fas fa-heart-broken"></i> Quitar de favoritos';
                            }
                            alert(data.message);
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Ha ocurrido un error al procesar la solicitud.');
                    });
                });
            }
            
            // Guardar para después
            const btnGuardar = document.getElementById('btn-guardar');
            if (btnGuardar) {
                btnGuardar.addEventListener('click', function() {
                    const documentoId = this.getAttribute('data-id');
                    
                    fetch('../../backend/gestionRecursos/add_to_saved.php', {
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
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Ha ocurrido un error al guardar el recurso.');
                    });
                });
            }
            
            // --- Funcionalidad del modal de edición ---
            const modal = document.getElementById('edit-modal');
            const modalTitle = document.getElementById('modal-title');
            const submitButton = document.getElementById('submit-button');
            const closeModal = document.querySelector('.modal .close');
            const cancelButton = document.getElementById('cancel-button');
            
            // Elementos del formulario
            const resourceType = document.getElementById('resource-type');
            const videoUrlGroup = document.getElementById('video-url-group');
            const videoDurationGroup = document.getElementById('video-duration-group');
            const fileUploadGroup = document.getElementById('file-upload-group');
            const visibilitySelect = document.getElementById('resource-visibility');
            const groupSelectGroup = document.getElementById('group-select-group');
            const resourceImage = document.getElementById('resource-image');
            const currentImage = document.getElementById('current-image');
            const previewImage = document.getElementById('preview-image');
            const currentFile = document.getElementById('current-file');
            const categoryTags = document.getElementById('category-tags');
            const selectedCategoriesInput = document.getElementById('selected-categories');
            const tagInput = document.getElementById('tag-input');
            const customTagsContainer = document.getElementById('custom-tags');
            const selectedTagsInput = document.getElementById('selected-tags');
            const addTagButton = document.getElementById('add-tag-button');

            let selectedCategories = [];
            let customTags = [];

            // Verificar existencia de elementos
            if (!tagInput || !customTagsContainer || !selectedTagsInput || !addTagButton) {
                console.error('Error: No se encontraron los elementos de etiquetas (tag-input, custom-tags, selected-tags, add-tag-button).');
            }
            
            // Cerrar modal
            closeModal.addEventListener('click', closeModalFunction);
            cancelButton.addEventListener('click', closeModalFunction);
            
            function closeModalFunction() {
                modal.style.display = 'none';
                document.getElementById('edit-resource-form').reset();
                previewImage.style.display = 'none';
                currentImage.style.display = 'none';
                currentFile.style.display = 'none';
                categoryTags.querySelectorAll('.tag').forEach(tag => tag.classList.remove('selected'));
                customTagsContainer.innerHTML = '';
                customTags = [];
                selectedCategories = [];
                selectedCategoriesInput.value = '';
                selectedTagsInput.value = '';
            }
            
            // Actualizar vista previa de la imagen
            resourceImage.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        previewImage.style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });
            
            // Manejar cambio de tipo de recurso
            resourceType.addEventListener('change', function() {
                if (this.value === 'video') {
                    videoUrlGroup.style.display = 'block';
                    videoDurationGroup.style.display = 'block';
                    fileUploadGroup.style.display = 'none';
                    document.getElementById('resource-file').removeAttribute('required');
                } else {
                    videoUrlGroup.style.display = 'none';
                    videoDurationGroup.style.display = 'none';
                    fileUploadGroup.style.display = 'block';
                    document.getElementById('resource-file').removeAttribute('required');
                }
            });
            
            // Manejar cambio de visibilidad
            visibilitySelect.addEventListener('change', function() {
                if (this.value === 'Group') {
                    groupSelectGroup.style.display = 'block';
                } else {
                    groupSelectGroup.style.display = 'none';
                }
            });
            
            // Manejar categorías
            categoryTags.addEventListener('click', function(e) {
                if (e.target.classList.contains('tag')) {
                    const tag = e.target;
                    const categoryId = parseInt(tag.dataset.id);
                    const index = selectedCategories.indexOf(categoryId);
                    if (index === -1) {
                        selectedCategories.push(categoryId);
                        tag.classList.add('selected');
                    } else {
                        selectedCategories.splice(index, 1);
                        tag.classList.remove('selected');
                    }
                    selectedCategoriesInput.value = JSON.stringify(selectedCategories);
                }
            });
            
            // Función para agregar una etiqueta al contenedor
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

            // Manejar etiquetas personalizadas con Enter o coma
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

            // Manejar clic en el botón "Añadir"
            addTagButton.addEventListener('click', function() {
                const tagName = tagInput.value.trim();
                if (tagName) {
                    addTagToContainer(tagName);
                    tagInput.value = '';
                }
            });
            
            // Abrir modal para edición
            const btnEditar = document.getElementById('btn-editar');
            if (btnEditar) {
                btnEditar.addEventListener('click', function() {
                    const resourceId = this.getAttribute('data-id');
                    editResource(resourceId);
                });
            }
            
            function editResource(resourceId) {
                fetch(`../../backend/gestionRecursos/get_resource.php?resource_id=${resourceId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const resource = data.resource;
                            modal.style.display = 'flex';
                            modalTitle.textContent = 'Editar Recurso';
                            submitButton.textContent = 'Actualizar Recurso';
                            
                            // Llenar los campos del formulario
                            document.getElementById('resource-id').value = resource.id;
                            document.getElementById('resource-title').value = resource.titulo;
                            document.getElementById('resource-description').value = resource.descripcion || '';
                            document.getElementById('resource-author').value = resource.autor || '';
                            document.getElementById('resource-type').value = resource.tipo;
                            document.getElementById('resource-publication-date').value = resource.fecha_publicacion || '';
                            document.getElementById('resource-relevance').value = resource.relevancia || 'Medium';
                            document.getElementById('resource-visibility').value = resource.visibilidad || 'Public';
                            document.getElementById('resource-language').value = resource.idioma || 'es';
                            document.getElementById('resource-license').value = resource.licencia || 'Public Domain';
                            document.getElementById('resource-status').value = resource.estado || 'Published';
                            
                            // Mostrar la imagen y archivo actuales
                            if (resource.portada) {
                                currentImage.textContent = `Portada actual: ${resource.portada.split('/').pop()}`;
                                currentImage.style.display = 'block';
                                previewImage.src = resource.portada;
                                previewImage.style.display = 'block';
                            }
                            if (resource.url_archivo) {
                                currentFile.textContent = `Archivo actual: ${resource.url_archivo.split('/').pop()}`;
                                currentFile.style.display = 'block';
                            }
                            
                            // Manejar el tipo de recurso (video o archivo)
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
                            
                            // Manejar visibilidad y grupo
                            if (resource.visibilidad === 'Group') {
                                groupSelectGroup.style.display = 'block';
                                document.getElementById('resource-group').value = resource.grupo_id || '';
                            } else {
                                groupSelectGroup.style.display = 'none';
                            }
                            
                            // Cargar categorías y seleccionar las del recurso
                            selectedCategories = resource.categorias.map(id => parseInt(id));
                            categoryTags.querySelectorAll('.tag').forEach(tag => {
                                if (selectedCategories.includes(parseInt(tag.dataset.id))) {
                                    tag.classList.add('selected');
                                } else {
                                    tag.classList.remove('selected');
                                }
                            });
                            selectedCategoriesInput.value = JSON.stringify(selectedCategories);
                            
                            // Cargar etiquetas
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
                                        if (index !== -1) {
                                            customTags.splice(index, 1);
                                            this.remove();
                                            selectedTagsInput.value = JSON.stringify(customTags);
                                        }
                                    });
                                    customTagsContainer.appendChild(tagElement);
                                }
                            });
                            selectedTagsInput.value = JSON.stringify(customTags);
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error al cargar recurso:', error);
                        alert('Error al cargar el recurso para edición.');
                    });
            }
            
            // Enviar formulario de edición
            const form = document.getElementById('edit-resource-form');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                
                fetch('../../backend/gestionRecursos/update_resource.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload(); // Recargar la página para reflejar los cambios
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ha ocurrido un error al actualizar el recurso.');
                });
            });
            
            // Enviar comentario
            const formComentario = document.getElementById('form-comentario');
            const comentarioStatus = document.getElementById('comentario-status');
            if (formComentario) {
                formComentario.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const documentoId = this.getAttribute('data-documento-id');
                    const contenido = this.querySelector('textarea[name="contenido"]').value.trim();

                    if (!contenido) {
                        alert('El comentario no puede estar vacío.');
                        return;
                    }

                    comentarioStatus.style.display = 'block';

                    fetch('../../backend/gestionRecursos/add_comment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `documento_id=${documentoId}&contenido=${encodeURIComponent(contenido)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        comentarioStatus.style.display = 'none';
                        if (data.success) {
                            const commentsContainer = document.getElementById('comments-container');
                            const noCommentsDiv = commentsContainer.querySelector('.no-comments');
                            if (noCommentsDiv) {
                                noCommentsDiv.remove();
                            }

                            const newComment = document.createElement('div');
                            newComment.className = 'comment';
                            newComment.setAttribute('data-id', data.comentario_id);
                            newComment.innerHTML = `
                                <div class="comment__content">
                                    <div class="comment__header">
                                        <span class="comment__author">${data.autor_nombre}</span>
                                        <span class="comment__date">${data.fecha_creacion}</span>
                                    </div>
                                    <div class="comment__text">
                                        <p>${contenido.replace(/\n/g, '<br>')}</p>
                                    </div>
                                    <div class="comment__actions">
                                        <button class="btn-delete-comment" data-id="${data.comentario_id}">
                                            <i class="fas fa-trash-alt"></i> Eliminar
                                        </button>
                                    </div>
                                </div>
                            `;
                            commentsContainer.insertBefore(newComment, commentsContainer.firstChild);

                            formComentario.querySelector('textarea').value = '';
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        comentarioStatus.style.display = 'none';
                        console.error('Error:', error);
                        alert('Ha ocurrido un error al enviar el comentario.');
                    });
                });
            }

            // Eliminar comentario
            document.getElementById('comments-container').addEventListener('click', function(e) {
                const btnDelete = e.target.closest('.btn-delete-comment');
                if (btnDelete) {
                    if (!confirm('¿Estás seguro de que deseas eliminar este comentario?')) return;

                    const comentarioId = btnDelete.getAttribute('data-id');

                    fetch('../../backend/gestionRecursos/delete_comment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `comentario_id=${comentarioId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const commentDiv = btnDelete.closest('.comment');
                            commentDiv.remove();

                            const commentsContainer = document.getElementById('comments-container');
                            if (!commentsContainer.querySelector('.comment')) {
                                commentsContainer.innerHTML = `
                                    <div class="no-comments">
                                        <p>No hay comentarios aún. ¡Sé el primero en comentar!</p>
                                    </div>
                                `;
                            }
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Ha ocurrido un error al eliminar el comentario.');
                    });
                }
            });
        });
    </script>
</body>
</html>