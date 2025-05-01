<?php
session_start();
require_once "../../database/conexionDB.php";

// Verificar si se proporcionó un ID de recurso
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../repositorio/repositorio.php");
    exit();
}

$documento_id = intval($_GET['id']);
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;

if (!$usuario_id) {
    header("Location: ../login/login.php");
    exit();
}

try {
    $db = conexionDB::getConexion();
    
    // Obtener información del recurso
    $query = "
        SELECT d.*, u.nombre_usuario AS autor_nombre,
               COALESCE(ARRAY_AGG(dc.categoria_id) FILTER (WHERE dc.categoria_id IS NOT NULL), '{}') AS categorias,
               COALESCE(ARRAY_AGG(e.nombre) FILTER (WHERE e.nombre IS NOT NULL), '{}') AS etiquetas
        FROM documentos d
        JOIN usuarios u ON d.autor_id = u.id
        LEFT JOIN documento_categorias dc ON d.id = dc.documento_id
        LEFT JOIN documento_etiqueta de ON d.id = de.documento_id
        LEFT JOIN etiquetas e ON de.etiqueta_id = e.id
        WHERE d.id = :documento_id
        GROUP BY d.id, u.nombre_usuario
    ";
    
    $stmt = $db->prepare($query);
    $stmt->execute([':documento_id' => $documento_id]);
    $documento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$documento) {
        header("Location: ../repositorio/repositorio.php");
        exit();
    }
    
    // Verificar que el usuario sea el autor del recurso
    if ($documento['autor_id'] != $usuario_id) {
        header("Location: ../repositorio/repositorio.php");
        exit();
    }
    
    // Procesar categorías y etiquetas
    $documento['categorias'] = $documento['categorias'] === '{}'
        ? []
        : array_map('intval', explode(',', trim($documento['categorias'], '{}')));
    
    $documento['etiquetas'] = $documento['etiquetas'] === '{}'
        ? []
        : array_map('trim', explode(',', trim($documento['etiquetas'], '{}')));
    
    // Obtener todas las categorías disponibles
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
    die("Error al cargar el recurso: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Recurso - El Rincón de ADSO</title>
    <link rel="icon" type="image/png" href="../inicio/img/icono.png">
    <link rel="stylesheet" href="../repositorio/css/repositorio.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .edit-form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .edit-form h2 {
            margin-top: 0;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        .form-group .tag {
            display: inline-block;
            background-color: #e0e0e0;
            padding: 5px 10px;
            margin: 5px;
            border-radius: 15px;
            cursor: pointer;
        }
        .form-group .tag.selected {
            background-color: #007bff;
            color: #fff;
        }
        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        .form-actions button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .form-actions button[type="submit"] {
            background-color: #007bff;
            color: #fff;
        }
        .form-actions button[type="button"] {
            background-color: #6c757d;
            color: #fff;
        }
        .preview-image {
            max-width: 100px;
            margin-top: 10px;
            display: none;
        }
        .current-file {
            margin-top: 5px;
            color: #555;
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
                <li class="navbar__menu-item navbar__menu-item--active"><a href="../repositorio/repositorio.php">Repositorio</a></li>
                <li class="navbar__menu-item"><a href="../inicio/index.php#buscar">Búsquedas</a></li>
                <li class="navbar__menu-item"><a href="../inicio/index.php#nosotros">Nosotros</a></li>
                <li class="navbar__menu-item"><a href="../inicio/index.php#recientes">Recientes</a></li>
                <li class="navbar__menu-item"><a href="../inicio/index.php#comunidad">Comunidad</a></li>
                <li class="navbar__profile">
                    <i class="fas fa-user-circle navbar__profile-icon"></i>
                    <div class="navbar__profile-menu">
                        <a href="../panel/panel-usuario.php">Ver Perfil</a>
                        <form action="../../backend/logout.php" method="POST">
                            <button type="submit">Cerrar Sesión</button>
                        </form>
                    </div>
                </li>
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
                <li class="navbar__mobile-item"><a href="../panel/panel-usuario.php">Ver Perfil</a></li>
                <li class="navbar__mobile-item">
                    <form action="../../backend/logout.php" method="POST">
                        <button type="submit" class="navbar__menu-item--button">Cerrar Sesión</button>
                    </form>
                </li>
            </ul>
        </div>
    </nav>

    <main class="container">
        <div class="edit-form">
            <h2>Editar Recurso</h2>
            <form id="edit-resource-form" enctype="multipart/form-data">
                <input type="hidden" id="resource-id" name="resource_id" value="<?php echo htmlspecialchars($documento['id']); ?>">
                
                <div class="form-group">
                    <label for="resource-title">Título:</label>
                    <input type="text" id="resource-title" name="title" value="<?php echo htmlspecialchars($documento['titulo']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="resource-description">Descripción:</label>
                    <textarea id="resource-description" name="description"><?php echo htmlspecialchars($documento['descripcion'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="resource-author">Autor:</label>
                    <input type="text" id="resource-author" name="author" value="<?php echo htmlspecialchars($documento['autor']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="resource-type">Tipo:</label>
                    <select id="resource-type" name="type" required>
                        <option value="documento" <?php echo $documento['tipo'] === 'documento' ? 'selected' : ''; ?>>Documento</option>
                        <option value="libro" <?php echo $documento['tipo'] === 'libro' ? 'selected' : ''; ?>>Libro</option>
                        <option value="video" <?php echo $documento['tipo'] === 'video' ? 'selected' : ''; ?>>Video</option>
                    </select>
                </div>
                
                <div class="form-group" id="video-url-group" style="display: <?php echo $documento['tipo'] === 'video' ? 'block' : 'none'; ?>;">
                    <label for="resource-video-url">URL del Video (si aplica):</label>
                    <input type="url" id="resource-video-url" name="video_url" value="<?php echo htmlspecialchars($documento['url_archivo'] ?? ''); ?>">
                    <?php if (!empty($documento['url_archivo'])): ?>
                    <p class="current-file">URL actual: <?php echo htmlspecialchars($documento['url_archivo']); ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="form-group" id="video-duration-group" style="display: <?php echo $documento['tipo'] === 'video' ? 'block' : 'none'; ?>;">
                    <label for="resource-video-duration">Duración del Video (HH:MM:SS, si aplica):</label>
                    <input type="text" id="resource-video-duration" name="video_duration" value="<?php echo htmlspecialchars($documento['duracion'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="resource-publication-date">Fecha de Publicación:</label>
                    <input type="date" id="resource-publication-date" name="publication_date" value="<?php echo htmlspecialchars($documento['fecha_publicacion']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="resource-relevance">Relevancia:</label>
                    <select id="resource-relevance" name="relevance" required>
                        <option value="Low" <?php echo $documento['relevancia'] === 'Low' ? 'selected' : ''; ?>>Baja</option>
                        <option value="Medium" <?php echo $documento['relevancia'] === 'Medium' ? 'selected' : ''; ?>>Media</option>
                        <option value="High" <?php echo $documento['relevancia'] === 'High' ? 'selected' : ''; ?>>Alta</option>
                        <option value="Critical" <?php echo $documento['relevancia'] === 'Critical' ? 'selected' : ''; ?>>Crítica</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="resource-visibility">Visibilidad:</label>
                    <select id="resource-visibility" name="visibility" required>
                        <option value="Public" <?php echo $documento['visibilidad'] === 'Public' ? 'selected' : ''; ?>>Público</option>
                        <option value="Private" <?php echo $documento['visibilidad'] === 'Private' ? 'selected' : ''; ?>>Privado</option>
                        <option value="Group" <?php echo $documento['visibilidad'] === 'Group' ? 'selected' : ''; ?>>Grupo</option>
                    </select>
                </div>
                
                <div class="form-group" id="group-select-group" style="display: <?php echo $documento['visibilidad'] === 'Group' ? 'block' : 'none'; ?>;">
                    <label for="resource-group">Grupo (si aplica):</label>
                    <select id="resource-group" name="group_id">
                        <option value="">Ninguno</option>
                        <?php foreach ($grupos as $grupo): ?>
                        <option value="<?php echo $grupo['id']; ?>" <?php echo $documento['grupo_id'] == $grupo['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($grupo['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="resource-language">Idioma:</label>
                    <input type="text" id="resource-language" name="language" value="<?php echo htmlspecialchars($documento['idioma']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="resource-license">Licencia:</label>
                    <input type="text" id="resource-license" name="license" value="<?php echo htmlspecialchars($documento['licencia']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="resource-status">Estado:</label>
                    <select id="resource-status" name="status" required>
                        <option value="Draft" <?php echo $documento['estado'] === 'Draft' ? 'selected' : ''; ?>>Borrador</option>
                        <option value="Pending Review" <?php echo $documento['estado'] === 'Pending Review' ? 'selected' : ''; ?>>Pendiente de Revisión</option>
                        <option value="Published" <?php echo $documento['estado'] === 'Published' ? 'selected' : ''; ?>>Publicado</option>
                    </select>
                </div>
                
                <div class="form-group" id="file-upload-group" style="display: <?php echo $documento['tipo'] !== 'video' ? 'block' : 'none'; ?>;">
                    <label for="resource-file">Archivo (dejar en blanco para mantener el actual):</label>
                    <input type="file" id="resource-file" name="file">
                    <?php if (!empty($documento['url_archivo']) && $documento['tipo'] !== 'video'): ?>
                    <p class="current-file">Archivo actual: <?php echo htmlspecialchars(basename($documento['url_archivo'])); ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="resource-image">Imagen de Portada (dejar en blanco para mantener la actual):</label>
                    <input type="file" id="resource-image" name="image" accept="image/*">
                    <?php if (!empty($documento['portada'])): ?>
                    <p class="current-file">Portada actual: <?php echo htmlspecialchars(basename($documento['portada'])); ?></p>
                    <img src="<?php echo htmlspecialchars($documento['portada']); ?>" alt="Portada actual" class="preview-image" style="display: block;">
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label>Categorías:</label>
                    <div id="category-tags">
                        <?php foreach ($categorias_disponibles as $categoria): ?>
                        <span class="tag <?php echo in_array($categoria['id'], $documento['categorias']) ? 'selected' : ''; ?>" data-id="<?php echo $categoria['id']; ?>">
                            <?php echo htmlspecialchars($categoria['nombre']); ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="selected-categories" name="categories" value="<?php echo htmlspecialchars(json_encode($documento['categorias'])); ?>">
                </div>
                
                <div class="form-group">
                    <label for="tag-input">Etiquetas (separadas por comas):</label>
                    <input type="text" id="tag-input" placeholder="Escribe etiquetas y presiona Enter">
                    <div id="custom-tags">
                        <?php foreach ($documento['etiquetas'] as $etiqueta): ?>
                        <span class="tag"><?php echo htmlspecialchars($etiqueta); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="selected-tags" name="tags" value="<?php echo htmlspecialchars(json_encode($documento['etiquetas'])); ?>">
                </div>
                
                <div class="form-actions">
                    <button type="submit">Actualizar Recurso</button>
                    <button type="button" onclick="history.back()">Cancelar</button>
                </div>
            </form>
        </div>
    </main>

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

            // Elementos del formulario
            const resourceType = document.getElementById('resource-type');
            const videoUrlGroup = document.getElementById('video-url-group');
            const videoDurationGroup = document.getElementById('video-duration-group');
            const fileUploadGroup = document.getElementById('file-upload-group');
            const visibilitySelect = document.getElementById('resource-visibility');
            const groupSelectGroup = document.getElementById('group-select-group');
            const categoryTags = document.getElementById('category-tags');
            const selectedCategoriesInput = document.getElementById('selected-categories');
            const tagInput = document.getElementById('tag-input');
            const customTagsContainer = document.getElementById('custom-tags');
            const selectedTagsInput = document.getElementById('selected-tags');
            
            let selectedCategories = <?php echo json_encode($documento['categorias']); ?>;
            let customTags = <?php echo json_encode($documento['etiquetas']); ?>;
            
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
            
            // Manejar etiquetas personalizadas
            tagInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' || e.key === ',') {
                    e.preventDefault();
                    const tagName = this.value.trim();
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
                        this.value = '';
                        selectedTagsInput.value = JSON.stringify(customTags);
                    }
                }
            });

            // Inicializar etiquetas existentes
            customTags.forEach(tagName => {
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
            });

            // Enviar formulario
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
                        window.location.href = `ver_${formData.get('type')}.php?id=${formData.get('resource_id')}`;
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ha ocurrido un error al actualizar el recurso.');
                });
            });
        });
    </script>
</body>
</html>