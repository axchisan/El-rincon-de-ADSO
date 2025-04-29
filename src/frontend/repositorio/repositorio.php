<?php
session_start();
require_once "../../database/conexionDB.php";

$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
$nombre_usuario = '';
$usuario_imagen = '';
$unread_count = 0; // Variable para contar notificaciones no leídas

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login/login.php");
    exit();
}

if ($usuario_id) {
    try {
        $db = conexionDB::getConexion();
        // Obtener nombre_usuario e imagen del usuario logueado
        $query = "SELECT nombre_usuario, imagen FROM usuarios WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $usuario_id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($usuario) {
            $nombre_usuario = htmlspecialchars($usuario['nombre_usuario']);
            // Construir la ruta de la imagen del usuario
            $usuario_imagen = $usuario['imagen'] ? "../../backend/perfil/" . htmlspecialchars($usuario['imagen']) . "?v=" . time() : "https://i.pravatar.cc/150?img=$usuario_id";
        }

        // Contar notificaciones no leídas
        $query = "SELECT COUNT(*) FROM notificaciones WHERE usuario_id = :user_id AND leida = FALSE";
        $stmt = $db->prepare($query);
        $stmt->execute([':user_id' => $usuario_id]);
        $unread_count = $stmt->fetchColumn();

    } catch (PDOException $e) {
        error_log("Error al obtener datos del usuario o notificaciones: " . $e->getMessage());
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
    <style>
        /* Estilo para la imagen del perfil */
        .navbar__profile-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
        }
        .navbar__profile {
            position: relative;
            display: inline-block;
        }
        .navbar__profile-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
            z-index: 1000;
        }
        .navbar__profile-menu.active {
            display: block;
        }
        .navbar__profile-menu a, .navbar__profile-menu button {
            display: block;
            padding: 10px 20px;
            color: #333;
            text-decoration: none;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }
        .navbar__profile-menu a:hover, .navbar__profile-menu button:hover {
            background-color: #f0f0f0;
        }
        /* Estilo para el círculo de notificaciones */
        .navbar__notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #e74c3c;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }
        /* Asegurar que el círculo esté oculto si no hay notificaciones */
        .navbar__notification-badge.hidden {
            display: none;
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
                <li class="navbar__menu-item"><a href="#buscar">Búsquedas</a></li>
                <li class="navbar__menu-item"><a href="../inicio/index.php#nosotros">Nosotros</a></li>
                <li class="navbar__menu-item"><a href="../inicio/index.php#recientes">Recientes</a></li>
                <li class="navbar__menu-item"><a href="../inicio/index.php#comunidad">Comunidad</a></li>
                <?php if ($usuario_id): ?>
                <li class="navbar__profile">
                    <img src="<?php echo $usuario_imagen; ?>" alt="Perfil de <?php echo $nombre_usuario; ?>" class="navbar__profile-img" id="profile-img">
                    <span class="navbar__notification-badge <?php echo $unread_count == 0 ? 'hidden' : ''; ?>">
                        <?php echo $unread_count; ?>
                    </span>
                    <div class="navbar__profile-menu" id="profile-menu">
                        <a href="../panel/panel-usuario.php">Ver Perfil</a>
                        <a href="../notificaciones/notificaciones.php">Notificaciones</a>
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
                <li class="navbar__menu-item"><a href="#buscar">Búsquedas</a></li>
                <li class="navbar__menu-item"><a href="../inicio/index.php#nosotros">Nosotros</a></li>
                <li class="navbar__menu-item"><a href="../inicio/index.php#recientes">Recientes</a></li>
                <li class="navbar__menu-item"><a href="../inicio/index.php#comunidad">Comunidad</a></li>
                <?php if ($usuario_id): ?>
                <li class="navbar__mobile-item">
                    <img src="<?php echo $usuario_imagen; ?>" alt="Perfil de <?php echo $nombre_usuario; ?>" class="navbar__profile-img" style="vertical-align: middle; margin-right: 10px;">
                    <span><?php echo $nombre_usuario; ?></span>
                    <?php if ($unread_count > 0): ?>
                        <span class="navbar__notification-badge" style="margin-left: 10px; vertical-align: middle;">
                            <?php echo $unread_count; ?>
                        </span>
                    <?php endif; ?>
                </li>
                <li class="navbar__mobile-item"><a href="../panel/panel-usuario.php">Ver Perfil</a></li>
                <li class="navbar__mobile-item"><a href="../notificaciones/notificaciones.php">Notificaciones</a></li>
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
    <section class="search-section"  id="buscar">
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
            <div class="pagination" id="books-pagination"></div>
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
            <div class="pagination" id="videos-pagination"></div>
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
            <div class="pagination" id="documents-pagination"></div>
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
            const profileImg = document.getElementById('profile-img');
            const profileMenu = document.getElementById('profile-menu');
            
            if (profileImg && profileMenu) {
                profileImg.addEventListener('click', function(event) {
                    event.stopPropagation(); // Evitar que el clic se propague y cierre el menú inmediatamente
                    profileMenu.classList.toggle('active');
                });

                // Cerrar el menú al hacer clic fuera de él
                document.addEventListener('click', function(event) {
                    if (!profileImg.contains(event.target) && !profileMenu.contains(event.target)) {
                        profileMenu.classList.remove('active');
                    }
                });
            } else {
                console.error('No se encontraron los elementos profile-img o profile-menu');
            }

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

            function loadCategories() {
                fetch('../../backend/gestionRecursos/get_categories.php')
                    .then(response => response.json())
                    .then(data => {
                        filterCategory.innerHTML = '<option value="">Todas</option>';
                        if (data.error) {
                            console.error('Error al cargar categorías:', data.error);
                            filterCategory.innerHTML += '<option value="">Error al cargar categorías</option>';
                            return;
                        }
                        data.forEach(category => {
                            const option = document.createElement('option');
                            option.value = category.id;
                            option.textContent = category.nombre;
                            filterCategory.appendChild(option);
                        });
                        loadResources();
                    })
                    .catch(error => {
                        console.error('Error al cargar categorías:', error);
                        filterCategory.innerHTML = '<option value="">Error al cargar categorías</option>';
                    });
            }
            loadCategories();

            function loadResources(pageBooks = 1, pageVideos = 1, pageDocuments = 1) {
                const search = searchInput.value.trim();
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
                params.append('limit', 6);

                const booksSection = document.getElementById('booksSection');
                const videosSection = document.getElementById('videosSection');
                const documentsSection = document.getElementById('documentsSection');
                const booksPagination = document.getElementById('books-pagination');
                const videosPagination = document.getElementById('videos-pagination');
                const documentsPagination = document.getElementById('documents-pagination');

                // Función para generar paginación
                function generatePagination(totalItems, currentPage, section, callback) {
                    const totalPages = Math.ceil(totalItems / 6);
                    const paginationContainer = document.getElementById(`${section}-pagination`);
                    paginationContainer.innerHTML = '';

                    if (totalPages <= 1) return;

                    const prevLink = document.createElement('a');
                    prevLink.href = '#';
                    prevLink.className = 'pagination__link pagination__link--prev';
                    prevLink.innerHTML = '<i class="fas fa-chevron-left"></i>';
                    if (currentPage === 1) {
                        prevLink.classList.add('pagination__link--disabled');
                    } else {
                        prevLink.addEventListener('click', (e) => {
                            e.preventDefault();
                            callback(currentPage - 1);
                        });
                    }
                    paginationContainer.appendChild(prevLink);

                    let startPage = Math.max(1, currentPage - 2);
                    let endPage = Math.min(totalPages, currentPage + 2);

                    if (startPage > 1) {
                        const firstPage = document.createElement('a');
                        firstPage.href = '#';
                        firstPage.className = 'pagination__link';
                        firstPage.textContent = '1';
                        firstPage.addEventListener('click', (e) => {
                            e.preventDefault();
                            callback(1);
                        });
                        paginationContainer.appendChild(firstPage);

                        if (startPage > 2) {
                            const dots = document.createElement('span');
                            dots.className = 'pagination__dots';
                            dots.textContent = '...';
                            paginationContainer.appendChild(dots);
                        }
                    }

                    for (let i = startPage; i <= endPage; i++) {
                        const pageLink = document.createElement('a');
                        pageLink.href = '#';
                        pageLink.className = 'pagination__link';
                        if (i === currentPage) {
                            pageLink.classList.add('pagination__link--active');
                        }
                        pageLink.textContent = i;
                        pageLink.addEventListener('click', (e) => {
                            e.preventDefault();
                            callback(i);
                        });
                        paginationContainer.appendChild(pageLink);
                    }

                    if (endPage < totalPages) {
                        if (endPage < totalPages - 1) {
                            const dots = document.createElement('span');
                            dots.className = 'pagination__dots';
                            dots.textContent = '...';
                            paginationContainer.appendChild(dots);
                        }

                        const lastPage = document.createElement('a');
                        lastPage.href = '#';
                        lastPage.className = 'pagination__link';
                        lastPage.textContent = totalPages;
                        lastPage.addEventListener('click', (e) => {
                            e.preventDefault();
                            callback(totalPages);
                        });
                        paginationContainer.appendChild(lastPage);
                    }

                    const nextLink = document.createElement('a');
                    nextLink.href = '#';
                    nextLink.className = 'pagination__link pagination__link--next';
                    nextLink.innerHTML = '<i class="fas fa-chevron-right"></i>';
                    if (currentPage === totalPages) {
                        nextLink.classList.add('pagination__link--disabled');
                    } else {
                        nextLink.addEventListener('click', (e) => {
                            e.preventDefault();
                            callback(currentPage + 1);
                        });
                    }
                    paginationContainer.appendChild(nextLink);
                }

                // Cargar libros
                if (!type || type === 'libro') {
                    params.set('type', 'libro');
                    params.set('page', pageBooks);
                    fetch(`../../backend/gestionRecursos/search_resources.php?${params.toString()}`)
                        .then(response => response.json())
                        .then(data => {
                            console.log('Datos libros:', data);
                            const booksGrid = document.getElementById('books-grid');
                            booksGrid.innerHTML = '';

                            if (data.resources.length === 0) {
                                booksGrid.innerHTML = '<p>No se encontraron libros.</p>';
                                booksSection.style.display = 'none'; // Ocultar la sección si no hay resultados
                                booksPagination.innerHTML = '';
                            } else {
                                booksSection.style.display = 'block'; // Mostrar la sección si hay resultados
                                data.resources.forEach(resource => {
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
                                                <a href="ver_libro.php?id=${resource.id}" class="btn btn--primary view-resource" data-id="${resource.id}">
                                                    <i class="fas fa-book-reader"></i> Leer ahora
                                                </a>
                                                <a href="#" class="btn btn--outline ${resource.es_favorito ? 'remove-favorite' : 'add-favorite'}" data-id="${resource.id}">
                                                    <i class="fas fa-heart${resource.es_favorito ? '-broken' : ''}"></i>
                                                    ${resource.es_favorito ? 'Quitar favorito' : 'Añadir a favoritos'}
                                                </a>
                                                <a href="#" class="btn btn--outline save-resource" data-id="${resource.id}">
                                                    <i class="fas fa-bookmark"></i> Guardar para después
                                                </a>
                                            </div>
                                        </div>
                                    `;
                                    booksGrid.appendChild(resourceCard);
                                });

                                generatePagination(data.total, pageBooks, 'books', (newPage) => {
                                    loadResources(newPage, pageVideos, pageDocuments);
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error al cargar libros:', error);
                            document.getElementById('books-grid').innerHTML = '<p>Error al cargar los libros.</p>';
                            booksSection.style.display = 'none'; // Ocultar la sección en caso de error
                            booksPagination.innerHTML = '';
                        });
                } else {
                    booksSection.style.display = 'none';
                    booksPagination.innerHTML = '';
                }

                // Cargar videos
                if (!type || type === 'video') {
                    params.set('type', 'video');
                    params.set('page', pageVideos);
                    fetch(`../../backend/gestionRecursos/search_resources.php?${params.toString()}`)
                        .then(response => response.json())
                        .then(data => {
                            console.log('Datos videos:', data);
                            const videosGrid = document.getElementById('videos-grid');
                            videosGrid.innerHTML = '';

                            if (data.resources.length === 0) {
                                videosGrid.innerHTML = '<p>No se encontraron videos.</p>';
                                videosSection.style.display = 'none'; // Ocultar la sección si no hay resultados
                                videosPagination.innerHTML = '';
                            } else {
                                videosSection.style.display = 'block'; // Mostrar la sección si hay resultados
                                data.resources.forEach(resource => {
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
                                                <a href="ver_video.php?id=${resource.id}" class="btn btn--primary view-resource" data-id="${resource.id}">
                                                    <i class="fas fa-play-circle"></i> Ver video
                                                </a>
                                                <a href="#" class="btn btn--outline ${resource.es_favorito ? 'remove-favorite' : 'add-favorite'}" data-id="${resource.id}">
                                                    <i class="fas fa-heart${resource.es_favorito ? '-broken' : ''}"></i>
                                                    ${resource.es_favorito ? 'Quitar favorito' : 'Añadir a favoritos'}
                                                </a>
                                                <a href="#" class="btn btn--outline save-resource" data-id="${resource.id}">
                                                    <i class="fas fa-bookmark"></i> Guardar para después
                                                </a>
                                            </div>
                                        </div>
                                    `;
                                    videosGrid.appendChild(resourceCard);
                                });

                                generatePagination(data.total, pageVideos, 'videos', (newPage) => {
                                    loadResources(pageBooks, newPage, pageDocuments);
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error al cargar videos:', error);
                            document.getElementById('videos-grid').innerHTML = '<p>Error al cargar los videos.</p>';
                            videosSection.style.display = 'none'; // Ocultar la sección en caso de error
                            videosPagination.innerHTML = '';
                        });
                } else {
                    videosSection.style.display = 'none';
                    videosPagination.innerHTML = '';
                }

                // Cargar documentos e imágenes
                if (!type || type === 'documento' || type === 'imagen') {
                    // Hacer una solicitud específica para documentos
                    params.set('type', 'documento');
                    params.set('page', pageDocuments);
                    fetch(`../../backend/gestionRecursos/search_resources.php?${params.toString()}`)
                        .then(response => response.json())
                        .then(dataDocs => {
                            console.log('Datos documentos:', dataDocs);

                            // Hacer una solicitud específica para imágenes
                            params.set('type', 'imagen');
                            fetch(`../../backend/gestionRecursos/search_resources.php?${params.toString()}`)
                                .then(response => response.json())
                                .then(dataImages => {
                                    console.log('Datos imágenes:', dataImages);

                                    const documentsGrid = document.getElementById('documents-grid');
                                    documentsGrid.innerHTML = '';

                                    // Combinar documentos e imágenes
                                    const documents = [
                                        ...(dataDocs.resources || []),
                                        ...(dataImages.resources || [])
                                    ];

                                    if (documents.length === 0) {
                                        documentsGrid.innerHTML = '<p>No se encontraron documentos.</p>';
                                        documentsSection.style.display = 'none'; // Ocultar la sección si no hay resultados
                                        documentsPagination.innerHTML = '';
                                    } else {
                                        documentsSection.style.display = 'block'; // Mostrar la sección si hay resultados
                                        documents.forEach(resource => {
                                            const categorias = Array.isArray(resource.categorias) && resource.categorias.length > 0 ? resource.categorias : ['Sin categoría'];
                                            const etiquetas = Array.isArray(resource.etiquetas) && resource.etiquetas.length > 0 ? resource.etiquetas : [];
                                            const defaultImage = '../inicio/img/default-cover.jpg';
                                            const coverImage = resource.portada && resource.portada !== '' ? resource.portada : defaultImage;

                                            const resourceCard = document.createElement('div');
                                            resourceCard.className = 'resource-card document-card';
                                            resourceCard.innerHTML = `
                                                <div class="resource-card__image-container">
                                                    <img src="${coverImage}" alt="${resource.titulo}" class="resource-card__image" loading="lazy" onerror="this.src='${defaultImage}'">
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
                                                        <a href="ver_documento.php?id=${resource.id}" class="btn btn--primary view-resource" data-id="${resource.id}">
                                                            <i class="fas fa-eye"></i> Ver documento
                                                        </a>
                                                        <a href="#" class="btn btn--outline ${resource.es_favorito ? 'remove-favorite' : 'add-favorite'}" data-id="${resource.id}">
                                                            <i class="fas fa-heart${resource.es_favorito ? '-broken' : ''}"></i>
                                                            ${resource.es_favorito ? 'Quitar favorito' : 'Añadir a favoritos'}
                                                        </a>
                                                        <a href="#" class="btn btn--outline save-resource" data-id="${resource.id}">
                                                            <i class="fas fa-bookmark"></i> Guardar para después
                                                        </a>
                                                    </div>
                                                </div>
                                            `;
                                            documentsGrid.appendChild(resourceCard);
                                        });

                                        const totalDocuments = (dataDocs.total || 0) + (dataImages.total || 0);
                                        generatePagination(totalDocuments, pageDocuments, 'documents', (newPage) => {
                                            loadResources(pageBooks, pageVideos, newPage);
                                        });
                                    }
                                })
                                .catch(error => {
                                    console.error('Error al cargar imágenes:', error);
                                    document.getElementById('documents-grid').innerHTML = '<p>Error al cargar los documentos.</p>';
                                    documentsSection.style.display = 'none'; // Ocultar la sección en caso de error
                                    documentsPagination.innerHTML = '';
                                });
                        })
                        .catch(error => {
                            console.error('Error al cargar documentos:', error);
                            document.getElementById('documents-grid').innerHTML = '<p>Error al cargar los documentos.</p>';
                            documentsSection.style.display = 'none'; // Ocultar la sección en caso de error
                            documentsPagination.innerHTML = '';
                        });
                } else {
                    documentsSection.style.display = 'none';
                    documentsPagination.innerHTML = '';
                }

                // Agregar eventos para acciones
                document.querySelectorAll('.view-resource').forEach(button => {
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
                            }
                        })
                        .catch(error => console.error('Error al registrar vista:', error));
                    });
                });

                document.querySelectorAll('.add-favorite, .remove-favorite').forEach(button => {
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
                                loadResources(pageBooks, pageVideos, pageDocuments);
                            } else {
                                alert(data.message);
                            }
                        })
                        .catch(error => console.error(`Error al ${action === 'add' ? 'añadir a' : 'quitar de'} favoritos:`, error));
                    });
                });

                document.querySelectorAll('.save-resource').forEach(button => {
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
            }

            searchButton.addEventListener('click', () => loadResources(1, 1, 1));
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    loadResources(1, 1, 1);
                }
            });
            filterType.addEventListener('change', () => loadResources(1, 1, 1));
            filterCategory.addEventListener('change', () => loadResources(1, 1, 1));
            filterRelevance.addEventListener('change', () => loadResources(1, 1, 1));
            filterLanguage.addEventListener('change', () => loadResources(1, 1, 1));
        });
    </script>
    <footer class="footer">
    <div class="container">
        <div class="footer__grid">
            <div>
                <h3 class="footer__logo">El Rincón de ADSO</h3>
                <p class="footer__description">Tu repositorio digital de confianza para el acceso al conocimiento académico y literario.</p>
            </div>
            <div>
                <h4 class="footer__heading">Nuestra Filosofía</h4>
                <p class="footer__quote">El aprendizaje continuo es el camino hacia la excelencia personal y profesional.</p>
                <p class="footer__philosophy-text">Nos dedicamos a cultivar mentes curiosas y a fomentar el pensamiento crítico a través del acceso a recursos educativos de calidad.</p>
            </div>
        </div>
        <div class="footer__bottom">
            <div class="footer__copyright">
                © <?php echo date('Y'); ?> El Rincón de ADSO. Todos los derechos reservados.
            </div>
        </div>
    </div>
</footer>
</body>

</html>