<?php
session_start();
require_once "../../database/conexionDB.php";

// Verificar si se proporcionó un ID de documento y tipo
if (!isset($_GET['id']) || empty($_GET['id']) || !isset($_GET['tipo']) || empty($_GET['tipo'])) {
    header("Location: ../repositorio/repositorio.php");
    exit();
}

$documento_id = intval($_GET['id']);
$tipo = $_GET['tipo'];
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;

try {
    $db = conexionDB::getConexion();
    
    // Obtener información del documento
    $query = "
        SELECT d.*, u.nombre_usuario AS autor_nombre
        FROM documentos d
        JOIN usuarios u ON d.autor_id = u.id
        WHERE d.id = :documento_id
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute([':documento_id' => $documento_id]);
    $documento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$documento) {
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
    
} catch (PDOException $e) {
    die("Error al cargar el documento: " . $e->getMessage());
}

// Determinar la página de retorno según el tipo
$return_page = "ver_documento.php";
if ($tipo === 'libro') {
    $return_page = "ver_libro.php";
} elseif ($tipo === 'video') {
    $return_page = "ver_video.php";
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
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: auto;
        }
        
        .file-viewer {
            width: 100%;
            background-color: #1a1a1a;
            min-height: 100vh;
        }
        
        .file-viewer__toolbar {
            position: sticky;
            top: 0;
            left: 0;
            right: 0;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 0.75rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
            transition: opacity 0.3s;
        }
        
        .file-viewer__title {
            font-size: 1.1rem;
            font-weight: 500;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 60%;
        }
        
        .file-viewer__actions {
            display: flex;
            gap: 1rem;
        }
        
        .file-viewer__btn {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        
        .file-viewer__btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .file-viewer__content {
            width: 100%;
            height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #1a1a1a;
        }
        
        .file-viewer__iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        
        .file-viewer__image {
            max-width: 95%;
            max-height: 95%;
            object-fit: contain;
        }

        .file-viewer__no-preview {
            color: white;
            text-align: center;
            padding: 2rem;
        }
        
        .resource-comments {
            background-color: #fff;
            padding: 1rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        @media (max-width: 768px) {
            .file-viewer__title {
                max-width: 40%;
            }
            .file-viewer__content {
                height: 60vh;
            }
        }
    </style>
</head>
<body>
    <div class="file-viewer">
        <div class="file-viewer__toolbar">
            <h1 class="file-viewer__title"><?php echo htmlspecialchars($documento['titulo']); ?></h1>
            <div class="file-viewer__actions">
                <?php if (!empty($documento['url_archivo']) && pathinfo($documento['url_archivo'], PATHINFO_EXTENSION) === 'pdf'): ?>
                <a href="<?php echo htmlspecialchars($documento['url_archivo']); ?>" class="file-viewer__btn" download>
                    <i class="fas fa-download"></i> Descargar
                </a>
                <?php endif; ?>
                <a href="<?php echo $return_page; ?>?id=<?php echo $documento_id; ?>" class="file-viewer__btn">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
        
        <div class="file-viewer__content">
            <?php 
            $extension = pathinfo($documento['url_archivo'], PATHINFO_EXTENSION);
            $isPdf = $extension === 'pdf';
            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']);
            
            if ($isPdf): 
            ?>
                <iframe src="<?php echo htmlspecialchars($documento['url_archivo']); ?>" class="file-viewer__iframe" allowfullscreen></iframe>
            <?php elseif ($isImage): ?>
                <img src="<?php echo htmlspecialchars($documento['url_archivo']); ?>" alt="<?php echo htmlspecialchars($documento['titulo']); ?>" class="file-viewer__image">
            <?php else: ?>
                <div class="file-viewer__no-preview">
                    <i class="fas fa-exclamation-circle" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <p>Este tipo de archivo no se puede previsualizar directamente.</p>
                    <a href="<?php echo htmlspecialchars($documento['url_archivo']); ?>" class="file-viewer__btn" download style="display: inline-flex; margin-top: 1rem;">
                        <i class="fas fa-download"></i> Descargar Archivo
                    </a>
                </div>
            <?php endif; ?>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
                            // Añadir el comentario al DOM sin recargar la página
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

                            // Limpiar el formulario
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