<?php
session_start();
require_once "../../database/conexionDB.php";

// Verificar si se proporcionó un ID de documento
if (!isset($_GET['id']) || empty($_GET['id'])) {
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
    
    if (!$documento || $documento['tipo'] !== 'video') {
        header("Location: ../repositorio/repositorio.php");
        exit();
    }
    
    // Verificar permisos de acceso
    if ($documento['visibilidad'] === 'Private' && $documento['autor_id'] != $usuario_id) {
        header("Location: ../repositorio/repositorio.php");
        exit();
    }
    
    if ($documento['visibilidad'] === 'Group' && $usuario_id) {
        $query = "SELECT COUNT(*) FROM usuario_grupo WHERE usuario_id = :usuario_id AND grupo_id = :grupo_id";
        $stmt = $db->prepare($query);
        $stmt->execute([':usuario_id' => $usuario_id, ':grupo_id' => $documento['grupo_id']]);
        $es_miembro = $stmt->fetchColumn();
        
        if (!$es_miembro) {
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
    
    // Obtener comentarios
    $query = "
        SELECT c.*, u.nombre_usuario
        FROM comentarios c
        JOIN usuarios u ON c.autor_id = u.id
        WHERE c.publicacion_id = :documento_id
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
    
    // Extraer ID de YouTube si es un enlace de YouTube
    $youtube_id = '';
    if (!empty($documento['url_archivo'])) {
        $youtube_regex = '/^(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:embed\/|watch\?v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
        if (preg_match($youtube_regex, $documento['url_archivo'], $matches)) {
            $youtube_id = $matches[1];
        }
    }
    
} catch (PDOException $e) {
    die("Error al cargar el video: " . $e->getMessage());
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
<script src="../lib/Bootstrap/js"></script>
    <!-- Navegación -->
    <nav class="navbar">
        <div class="container navbar__container">
            <a href="../inicio/index.php" class="navbar__logo">
                <i class="fas fa-book-open"></i>
                El Rincón de ADSO
            </a>
            <!-- Navegación para escritorio -->
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
            <!-- Botón menú móvil -->
            <button id="mobile-menu-button" class="navbar__toggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        <!-- Menú móvil desplegable -->
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
                        <span class="badge badge--primary">Video</span>
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
            </div>
            
            <div class="resource-content">
                <div class="resource-video">
                    <div class="resource-section">
                        <h2 class="resource-section__title">Video</h2>
                        
                        <?php if (!empty($youtube_id)): ?>
                        <div class="resource-video__container">
                            <iframe 
                                src="https://www.youtube.com/embed/<?php echo htmlspecialchars($youtube_id); ?>" 
                                title="<?php echo htmlspecialchars($documento['titulo']); ?>" 
                                frameborder="0" 
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                allowfullscreen
                                class="resource-video__iframe">
                            </iframe>
                        </div>
                        <?php elseif (!empty($documento['url_archivo'])): ?>
                        <div class="resource-video__container">
                            <video controls class="resource-video__player">
                                <source src="<?php echo htmlspecialchars($documento['url_archivo']); ?>" type="video/mp4">
                                Tu navegador no soporta la reproducción de videos.
                            </video>
                        </div>
                        <?php else: ?>
                        <div class="resource-video__no-video">
                            <i class="fas fa-exclamation-circle resource-video__no-video-icon"></i>
                            <p>No hay video disponible para este recurso.</p>
                        </div>
                        <?php endif; ?>
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
                            
                            <?php if (!empty($documento['duracion'])): ?>
                            <div class="resource-info__item">
                                <span class="resource-info__label">Duración:</span>
                                <span class="resource-info__value"><?php echo htmlspecialchars($documento['duracion']); ?></span>
                            </div>
                            <?php endif; ?>
                            
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
                            <p>No hay descripción disponible para este video.</p>
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
                            <p>No hay etiquetas asociadas a este video.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Reemplazar la sección de comentarios en ver_libro.php, ver_documento.php y ver_video.php con este código mejorado -->

<div class="resource-comments">
    <div class="resource-section">
        <h2 class="resource-section__title">Comentarios</h2>
        
        <?php if ($usuario_id): ?>
        <div class="comment-form">
            <form id="form-comentario" data-documento-id="<?php echo $documento_id; ?>">
                <div class="comment-form__input">
                    <textarea name="comentario" placeholder="Escribe un comentario..." required></textarea>
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
                <div class="comment">
                    <div class="comment__content">
                        <div class="comment__header">
                            <span class="comment__author"><?php echo htmlspecialchars($comentario['nombre_usuario']); ?></span>
                            <span class="comment__date"><?php echo date('d/m/Y H:i', strtotime($comentario['fecha_creacion'])); ?></span>
                        </div>
                          ?></span>
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
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer__content">
                <div class="footer__logo">
                    <i class="fas fa-book-open"></i>
                    <span>El Rincón de ADSO</span>
                </div>
                <p class="footer__copyright">&copy; <?php echo date('Y'); ?> El Rincón de ADSO. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

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
            
            // Editar recurso
            const btnEditar = document.getElementById('btn-editar');
            if (btnEditar) {
                btnEditar.addEventListener('click', function() {
                    const documentoId = this.getAttribute('data-id');
                    window.location.href = `../panel/editar-recurso.php?id=${documentoId}`;
                });
            }
            
            // Comentarios
            const formComentario = document.getElementById('form-comentario');
            if (formComentario) {
                formComentario.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const documentoId = this.getAttribute('data-documento-id');
                    const comentario = this.querySelector('textarea[name="comentario"]').value;
                    
                    if (!comentario.trim()) {
                        alert('El comentario no puede estar vacío.');
                        return;
                    }
                    
                    fetch('../../backend/gestionRecursos/add_comment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `documento_id=${documentoId}&comentario=${encodeURIComponent(comentario)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Recargar la página para mostrar el nuevo comentario
                            location.reload();
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Ha ocurrido un error al enviar el comentario.');
                    });
                });
            }
            
            // Eliminar comentario
            document.querySelectorAll('.btn-delete-comment').forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm('¿Estás seguro de que deseas eliminar este comentario?')) {
                        const comentarioId = this.getAttribute('data-id');
                        
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
                                // Recargar la página para actualizar los comentarios
                                location.reload();
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
        });
    </script>

<!-- Agregar el script de comentarios al final del archivo, justo antes de </body> -->

<script src="../lib/Bootstrap/js/script-comentarios.js"></script>
</body>
</html>

