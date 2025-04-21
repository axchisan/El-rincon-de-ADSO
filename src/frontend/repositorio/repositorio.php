<?php
session_start();
require_once "../../database/conexionDB.php";

// Verificar si el usuario está logueado
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
$nombre_usuario = '';
if ($usuario_id) {
    try {
        $db = conexionDB::getConexion();
        $query = "SELECT nombre_usuario FROM usuarios WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $usuario_id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($usuario) {
            $nombre_usuario = htmlspecialchars($usuario['nombre_usuario']);
        } else {
            session_destroy();
            header("Location: ../inicio/index.php");
            exit();
        }
    } catch (PDOException $e) {
        session_destroy();
        header("Location: ../inicio/index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repositorio - El Rincón de ADSO</title>
    <link rel="icon" type="image/png" href="../inicio/img/icono.png">
    <link rel="stylesheet" href="./css/repositorio.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
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
            <!-- Navegación para escritorio -->
            <ul class="navbar__menu">
                <li class="navbar__menu-item"><a href="../inicio/index.php">Inicio</a></li>
                <li class="navbar__menu-item navbar__menu-item--active"><a href="../repositorio/repositorio.php">Repositorio</a></li>
                <li class="navbar__menu-item"><a href="../inicio/index.php#buscar">Búsquedas</a></li>
                <li class="navbar__menu-item"><a href="../inicio/index.php#nosotros">Nosotros</a></li>
                <li class="navbar__menu-item"><a href="../inicio/index.php#recientes">Recientes</a></li>
                <li class="navbar__menu-item"><a href="../inicio/index.php#comunidad">Comunidad</a></li>
                <?php if (!$usuario_id): ?>
                    <li class="navbar__menu-item"><a href="../register/registro.php">Registro</a></li>
                <?php endif; ?>
                <?php if ($usuario_id): ?>
                    <!-- Si hay sesión activa, mostrar el icono de perfil -->
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
                    <li class="navbar__menu-item navbar__menu-item--button"><a href="../login/login.php">Iniciar sesión</a></li>
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
                <?php if (!$usuario_id): ?>
                    <li class="navbar__mobile-item"><a href="../register/registro.php">Registro</a></li>
                <?php endif; ?>
                <?php if ($usuario_id): ?>
                    <li class="navbar__mobile-item"><a href="../panel/panel-usuario.php">Ver Perfil</a></li>
                    <li class="navbar__mobile-item">
                        <form action="../../backend/logout.php" method="POST">
                            <button type="submit" class="navbar__menu-item--button">Cerrar Sesión</button>
                        </form>
                    </li>
                <?php else: ?>
                    <li class="navbar__menu-item navbar__menu-item--button"><a href="../login/login.php">Iniciar sesión</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Cabecera del Repositorio -->
    <section class="hero">
        <div class="hero__container">
            <div class="hero__image">
                <img src="../inicio/img/repositorio.jpg" alt="Laptop con libros">
            </div>
            <div class="hero__content">
                <h1 class="hero__title">Repositorio Digital</h1>
                <p class="hero__description">
                    Explora nuestra colección de recursos educativos orientados a <strong>desarrollo web</strong>,
                    <strong>programación</strong>, <strong>bases de datos</strong> y más.
                </p>
                <p class="hero__description">
                    Aprende sobre <code>HTML</code>, <code>JavaScript</code>, <code>PHP</code> y crea tu futuro digital.
                </p>
            </div>
        </div>
        <div class="hero__boton-volver">
            <button class="btn-volver" onclick="history.back()">← Volver</button>
        </div>
    </section>

    <!-- Buscador y Filtros -->
    <section class="search-section">
        <div class="container">
            <div class="search-container">
                <div class="search-box">
                    <input type="text" id="search-input" placeholder="Buscar por título, autor, tema...">
                    <button class="search-button" id="search-button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>

                <div class="filter-container">
                    <div class="filter-group">
                        <label for="filter-type">Tipo de recurso</label>
                        <select id="filter-type">
                            <option value="">Todos</option>
                            <option value="libro">Libros</option>
                            <option value="video">Videos</option>
                            <option value="documento">Documentos</option>
                            <option value="imagen">Imágenes</option>
                            <option value="otro">Otros</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="filter-category">Categoría</label>
                        <select id="filter-category">
                            <option value="">Todas</option>
                            <!-- Se llenará dinámicamente -->
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="filter-relevance">Ordenar por</label>
                        <select id="filter-relevance">
                            <option value="">Relevancia</option>
                            <option value="Low">Baja</option>
                            <option value="Medium">Media</option>
                            <option value="High">Alta</option>
                            <option value="Critical">Crítica</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="filter-language">Idioma</label>
                        <select id="filter-language">
                            <option value="">Todos</option>
                            <option value="es">Español</option>
                            <option value="en">Inglés</option>
                            <option value="fr">Francés</option>
                            <option value="de">Alemán</option>
                        </select>
                    </div>

                    <button class="filter-button" id="filter-toggle">
                        <i class="fas fa-sliders-h"></i> Filtros avanzados
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Resultados - Libros -->
    <section id="booksSection" class="resources-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Libros Disponibles</h2>
                <p class="section-description">Explora nuestra colección de libros digitales</p>
            </div>
            <div id="books-grid" class="resources-grid">
                <p>Cargando libros...</p>
            </div>
            <div class="pagination" id="books-pagination">
                <a href="#" class="pagination__link pagination__link--active">1</a>
                <a href="#" class="pagination__link">2</a>
                <a href="#" class="pagination__link">3</a>
                <span class="pagination__dots">...</span>
                <a href="#" class="pagination__link">10</a>
                <a href="#" class="pagination__link pagination__link--next">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Resultados - Videos -->
    <section id="videosSection" class="resources-section resources-section--alt">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Videos Educativos</h2>
                <p class="section-description">Tutoriales y cursos en formato video</p>
            </div>
            <div id="videos-grid" class="resources-grid">
                <p>Cargando videos...</p>
            </div>
            <div class="pagination" id="videos-pagination">
                <a href="#" class="pagination__link pagination__link--active">1</a>
                <a href="#" class="pagination__link">2</a>
                <a href="#" class="pagination__link">3</a>
                <span class="pagination__dots">...</span>
                <a href="#" class="pagination__link">8</a>
                <a href="#" class="pagination__link pagination__link--next">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Resultados - Documentos -->
    <section id="documentsSection" class="resources-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Documentos y Artículos</h2>
                <p class="section-description">Guías, tutoriales y artículos técnicos</p>
            </div>
            <div id="documents-grid" class="resources-grid resources-grid--documents">
                <p>Cargando documentos...</p>
            </div>
            <div class="pagination" id="documents-pagination">
                <a href="#" class="pagination__link pagination__link--active">1</a>
                <a href="#" class="pagination__link">2</a>
                <a href="#" class="pagination__link">3</a>
                <span class="pagination__dots">...</span>
                <a href="#" class="pagination__link">5</a>
                <a href="#" class="pagination__link pagination__link--next">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Banner de registro -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-container">
                <div class="cta-content">
                    <h2 class="cta-title">¿Quieres acceder a todos los recursos?</h2>
                    <p class="cta-description">Regístrate o inicia sesión para descargar libros, ver videos y acceder a todos los documentos de nuestro repositorio.</p>
                    <div class="cta-buttons">
                        <a href="../register/registro.php" class="btn btn--primary btn--lg">Crear cuenta</a>
                        <a href="../login/login.php" class="btn btn--outline btn--lg">Iniciar sesión</a>
                    </div>
                </div>
                <div class="cta-image">
                    <img src="https://cdn-icons-png.flaticon.com/512/10616/10616326.png" alt="Acceso a recursos" loading="lazy">
                </div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }

            // Mostrar/ocultar menú de perfil
            document.querySelectorAll('.navbar__profile-icon').forEach(icon => {
                icon.addEventListener('click', function() {
                    const menu = this.nextElementSibling;
                    menu.classList.toggle('active');
                });
            });

            const filterToggle = document.getElementById('filter-toggle');
            const filterContainer = document.querySelector('.filter-container');
            filterToggle.addEventListener('click', function() {
                filterContainer.classList.toggle('active');
            });

            const searchInput = document.getElementById('search-input');
            const searchButton = document.getElementById('search-button');
            const filterCategory = document.getElementById('filter-category');
            const filterType = document.getElementById('filter-type');
            const filterRelevance = document.getElementById('filter-relevance');
            const filterLanguage = document.getElementById('filter-language');

            // Cargar categorías dinámicamente
            fetch('../../backend/gestionRecursos/get_categories.php')
                .then(response => response.json())
                .then(data => {
                    data.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category.id;
                        option.textContent = category.nombre;
                        filterCategory.appendChild(option);
                    });
                })
                .catch(error => console.error('Error al cargar categorías:', error));

            // Cargar recursos al inicio
            loadResources();

            // Buscar al hacer clic en el botón o presionar Enter
            searchButton.addEventListener('click', loadResources);
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    loadResources();
                }
            });

            // Aplicar filtros al cambiar cualquier select
            filterType.addEventListener('change', loadResources);
            filterCategory.addEventListener('change', loadResources);
            filterRelevance.addEventListener('change', loadResources);
            filterLanguage.addEventListener('change', loadResources);

            function loadResources() {
                const search = searchInput.value;
                const category = filterCategory.value;
                const type = filterType.value;
                const relevance = filterRelevance.value;
                const language = filterLanguage.value;

                const params = new URLSearchParams();
                if (search) params.append('search', search);
                if (category) params.append('category', category);
                if (type) params.append('type', type);
                if (relevance) params.append('relevance', relevance);
                if (language) params.append('language', language);

                fetch(`../../backend/gestionRecursos/search_resources.php?${params.toString()}`)
                    .then(response => response.json())
                    .then(data => {
                        const booksGrid = document.getElementById('books-grid');
                        const videosGrid = document.getElementById('videos-grid');
                        const documentsGrid = document.getElementById('documents-grid');

                        booksGrid.innerHTML = '';
                        videosGrid.innerHTML = '';
                        documentsGrid.innerHTML = '';

                        if (data.error) {
                            booksGrid.innerHTML = `<p>${data.error}</p>`;
                            videosGrid.innerHTML = `<p>${data.error}</p>`;
                            documentsGrid.innerHTML = `<p>${data.error}</p>`;
                            return;
                        }

                        // Filtrar y mostrar recursos por tipo
                        const books = data.filter(resource => resource.tipo === 'libro');
                        const videos = data.filter(resource => resource.tipo === 'video');
                        const documents = data.filter(resource => resource.tipo === 'documento');
                        const images = data.filter(resource => resource.tipo === 'imagen');

                        // Mostrar libros
                        if (books.length === 0) {
                            booksGrid.innerHTML = '<p>No se encontraron libros.</p>';
                        } else {
                            books.forEach(resource => {
                                const categorias = Array.isArray(resource.categorias) && resource.categorias.length > 0 ? resource.categorias : ['Sin categoría'];
                                const etiquetas = Array.isArray(resource.etiquetas) && resource.etiquetas.length > 0 ? resource.etiquetas : [];
                                const resourceCard = document.createElement('div');
                                resourceCard.className = 'resource-card book-card';
                                resourceCard.innerHTML = `
                                    <div class="resource-card__image-container">
                                        <img src="${resource.portada}" alt="${resource.titulo}" class="resource-card__image" loading="lazy">
                                        <div class="resource-card__format">${resource.tipo.toUpperCase()}</div>
                                    </div>
                                    <div class="resource-card__content">
                                        <div class="resource-card__category">${categorias.join(', ')}</div>
                                        <h3 class="resource-card__title">${resource.titulo}</h3>
                                        <p class="resource-card__author">Por ${resource.autor}</p>
                                        <div class="resource-card__meta">
                                            <span><i class="fas fa-calendar-alt"></i> ${new Date(resource.fecha_publicacion).toLocaleDateString()}</span>
                                            <span><i class="fas fa-eye"></i> ${resource.visibilidad}</span>
                                        </div>
                                        <div class="resource-card__tags">
                                            ${etiquetas.length > 0 ? etiquetas.map(tag => `<span class="tag">${tag}</span>`).join('') : '<span class="tag">Sin etiquetas</span>'}
                                        </div>
                                        <div class="resource-card__actions">
                                            <a href="#" class="btn btn--primary view-resource" data-id="${resource.id}">
                                                <i class="fas fa-book-reader"></i> Leer ahora
                                            </a>
                                            ${<?php echo json_encode($usuario_id); ?> ? `
                                                <a href="#" class="btn btn--outline ${resource.es_favorito ? 'remove-favorite' : 'add-favorite'}" data-id="${resource.id}">
                                                    <i class="fas fa-heart${resource.es_favorito ? '-broken' : ''}"></i>
                                                    ${resource.es_favorito ? 'Quitar favorito' : 'Añadir a favoritos'}
                                                </a>
                                                <a href="#" class="btn btn--outline save-resource" data-id="${resource.id}">
                                                    <i class="fas fa-bookmark"></i> Guardar para después
                                                </a>
                                            ` : `
                                                <a href="../inicio/index.php" class="btn btn--outline">
                                                    <i class="fas fa-heart"></i> Inicia sesión para añadir a favoritos
                                                </a>
                                            `}
                                        </div>
                                    </div>
                                `;
                                booksGrid.appendChild(resourceCard);
                            });
                        }

                        // Mostrar videos
                        if (videos.length === 0) {
                            videosGrid.innerHTML = '<p>No se encontraron videos.</p>';
                        } else {
                            videos.forEach(resource => {
                                const categorias = Array.isArray(resource.categorias) && resource.categorias.length > 0 ? resource.categorias : ['Sin categoría'];
                                const etiquetas = Array.isArray(resource.etiquetas) && resource.etiquetas.length > 0 ? resource.etiquetas : [];
                                const resourceCard = document.createElement('div');
                                resourceCard.className = 'resource-card video-card';
                                resourceCard.innerHTML = `
                                    <div class="resource-card__image-container">
                                        <img src="${resource.portada}" alt="${resource.titulo}" class="resource-card__image" loading="lazy">
                                        <div class="resource-card__duration"><i class="fas fa-clock"></i> ${resource.duracion}</div>
                                        <div class="resource-card__play-button"><i class="fas fa-play"></i></div>
                                    </div>
                                    <div class="resource-card__content">
                                        <div class="resource-card__category">${categorias.join(', ')}</div>
                                        <h3 class="resource-card__title">${resource.titulo}</h3>
                                        <p class="resource-card__author">Por ${resource.autor}</p>
                                        <div class="resource-card__meta">
                                            <span><i class="fas fa-calendar-alt"></i> ${new Date(resource.fecha_publicacion).toLocaleDateString()}</span>
                                            <span><i class="fas fa-eye"></i> ${resource.visibilidad}</span>
                                        </div>
                                        <div class="resource-card__tags">
                                            ${etiquetas.length > 0 ? etiquetas.map(tag => `<span class="tag">${tag}</span>`).join('') : '<span class="tag">Sin etiquetas</span>'}
                                        </div>
                                        <div class="resource-card__actions">
                                            <a href="#" class="btn btn--primary view-resource" data-id="${resource.id}">
                                                <i class="fas fa-play-circle"></i> Ver video
                                            </a>
                                            ${<?php echo json_encode($usuario_id); ?> ? `
                                                <a href="#" class="btn btn--outline ${resource.es_favorito ? 'remove-favorite' : 'add-favorite'}" data-id="${resource.id}">
                                                    <i class="fas fa-heart${resource.es_favorito ? '-broken' : ''}"></i>
                                                    ${resource.es_favorito ? 'Quitar favorito' : 'Añadir a favoritos'}
                                                </a>
                                                <a href="#" class="btn btn--outline save-resource" data-id="${resource.id}">
                                                    <i class="fas fa-bookmark"></i> Guardar para después
                                                </a>
                                            ` : `
                                                <a href="../inicio/index.php" class="btn btn--outline">
                                                    <i class="fas fa-heart"></i> Inicia sesión para añadir a favoritos
                                                </a>
                                            `}
                                        </div>
                                    </div>
                                `;
                                videosGrid.appendChild(resourceCard);
                            });
                        }

                        // Mostrar documentos e imágenes (los tratamos como documentos)
                        const allDocuments = [...documents, ...images];
                        if (allDocuments.length === 0) {
                            documentsGrid.innerHTML = '<p>No se encontraron documentos.</p>';
                        } else {
                            allDocuments.forEach(resource => {
                                const categorias = Array.isArray(resource.categorias) && resource.categorias.length > 0 ? resource.categorias : ['Sin categoría'];
                                const etiquetas = Array.isArray(resource.etiquetas) && resource.etiquetas.length > 0 ? resource.etiquetas : [];
                                // Determinar el ícono y color según el tipo
                                let iconClass = 'fas fa-file-alt';
                                let iconColor = '#9b59b6';
                                if (resource.tipo === 'documento') {
                                    iconClass = 'fas fa-file-pdf';
                                    iconColor = '#e74c3c';
                                } else if (resource.tipo === 'imagen') {
                                    iconClass = 'fas fa-file-image';
                                    iconColor = '#f39c12';
                                }

                                const resourceCard = document.createElement('div');
                                resourceCard.className = 'resource-card document-card';
                                resourceCard.innerHTML = `
                                    <div class="document-card__icon" style="background-color: ${iconColor};">
                                        <i class="${iconClass}"></i>
                                    </div>
                                    <div class="resource-card__content">
                                        <div class="resource-card__category">${categorias.join(', ')}</div>
                                        <h3 class="resource-card__title">${resource.titulo}</h3>
                                        <p class="resource-card__author">Por ${resource.autor}</p>
                                        <div class="resource-card__meta">
                                            <span><i class="fas fa-calendar-alt"></i> ${new Date(resource.fecha_publicacion).toLocaleDateString()}</span>
                                            <span><i class="fas fa-eye"></i> ${resource.visibilidad}</span>
                                        </div>
                                        <div class="resource-card__tags">
                                            ${etiquetas.length > 0 ? etiquetas.map(tag => `<span class="tag">${tag}</span>`).join('') : '<span class="tag">Sin etiquetas</span>'}
                                        </div>
                                        <div class="resource-card__actions">
                                            <a href="#" class="btn btn--primary view-resource" data-id="${resource.id}">
                                                <i class="fas fa-eye"></i> Ver documento
                                            </a>
                                            ${<?php echo json_encode($usuario_id); ?> ? `
                                                <a href="#" class="btn btn--outline ${resource.es_favorito ? 'remove-favorite' : 'add-favorite'}" data-id="${resource.id}">
                                                    <i class="fas fa-heart${resource.es_favorito ? '-broken' : ''}"></i>
                                                    ${resource.es_favorito ? 'Quitar favorito' : 'Añadir a favoritos'}
                                                </a>
                                                <a href="#" class="btn btn--outline save-resource" data-id="${resource.id}">
                                                    <i class="fas fa-bookmark"></i> Guardar para después
                                                </a>
                                            ` : `
                                                <a href="../inicio/index.php" class="btn btn--outline">
                                                    <i class="fas fa-heart"></i> Inicia sesión para añadir a favoritos
                                                </a>
                                            `}
                                        </div>
                                    </div>
                                `;
                                documentsGrid.appendChild(resourceCard);
                            });
                        }

                        // Agregar eventos para "Leer ahora", "Ver video" o "Ver documento"
                        [booksGrid, videosGrid, documentsGrid].forEach(grid => {
                            grid.querySelectorAll('.view-resource').forEach(button => {
                                button.addEventListener('click', (e) => {
                                    e.preventDefault();
                                    const documentoId = button.getAttribute('data-id');
                                    <?php if ($usuario_id): ?>
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
                                                alert('Vista registrada. Aquí iría la lógica para ver el recurso.');
                                            } else {
                                                alert(data.message);
                                            }
                                        })
                                        .catch(error => console.error('Error al registrar vista:', error));
                                    <?php else: ?>
                                        alert('Inicia sesión para registrar tu actividad.');
                                    <?php endif; ?>
                                });
                            });

                            // Agregar eventos para "Añadir a favoritos" o "Quitar favorito"
                            grid.querySelectorAll('.add-favorite, .remove-favorite').forEach(button => {
                                button.addEventListener('click', (e) => {
                                    e.preventDefault();
                                    const documentoId = button.getAttribute('data-id');
                                    const action = button.classList.contains('add-favorite') ? 'add' : 'remove';
                                    const endpoint = action === 'add' ? '../../backend/gestionRecursos/add_to_favorites.php' : '../../backend/gestionRecursos/remove_from_favorites.php';

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
                                            alert(data.message);
                                            loadResources(); // Recargar recursos para actualizar el estado de favoritos
                                        } else {
                                            alert(data.message);
                                        }
                                    })
                                    .catch(error => console.error(`Error al ${action === 'add' ? 'añadir a' : 'quitar de'} favoritos:`, error));
                                });
                            });

                            // Agregar eventos para "Guardar para después"
                            grid.querySelectorAll('.save-resource').forEach(button => {
                                button.addEventListener('click', (e) => {
                                    e.preventDefault();
                                    const documentoId = button.getAttribute('data-id');
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
                                    .catch(error => console.error('Error al guardar recurso:', error));
                                });
                            });
                        });
                    })
                    .catch(error => {
                        console.error('Error al cargar recursos:', error);
                        document.getElementById('books-grid').innerHTML = '<p>Error al cargar los libros.</p>';
                        document.getElementById('videos-grid').innerHTML = '<p>Error al cargar los videos.</p>';
                        document.getElementById('documents-grid').innerHTML = '<p>Error al cargar los documentos.</p>';
                    });
            }
        });
    </script>
</body>

</html>