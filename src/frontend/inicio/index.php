<?php
session_start();
require_once "../../database/conexionDB.php";

// Verificar si el usuario está logueado
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
$nombre_usuario = '';
$usuario_imagen = '';
$unread_count = 0; // Variable para contar notificaciones no leídas

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
    <link rel="icon" type="image/png" href="./img/icono.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repositorio de Libros</title>
    <link rel="stylesheet" href="../repositorio/css/repositorio.css">
    <link rel="stylesheet" href="./css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" media="print" onload="this.media='all'">
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
        .navbar__profile {
            position: relative;
        }
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
            <a href="#" class="navbar__logo">
                <i class="fas fa-book-open"></i>
                El Rincón de ADSO
            </a>

            <!-- Navegación para escritorio -->
            <ul class="navbar__menu">
                <li class="navbar__menu-item navbar__menu-item--active"><a href="#">Inicio</a></li>
                <li class="navbar__menu-item"><a href="../repositorio/repositorio.php">Repositorio</a></li>
                <li class="navbar__menu-item"><a href="#buscar">Búsquedas</a></li>
                <li class="navbar__menu-item"><a href="#nosotros">Nosotros</a></li>
                <li class="navbar__menu-item"><a href="#recientes">Recientes</a></li>
                <li class="navbar__menu-item"><a href="#comunidad">Comunidad</a></li>
                <?php if (!$usuario_id): ?>
                    <li class="navbar__menu-item"><a href="../register/registro.php">Registro</a></li>
                <?php endif; ?>
                <?php if ($usuario_id): ?>
                    <!-- Si hay sesión activa, mostrar la imagen de perfil con el círculo de notificaciones -->
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
                <li class="navbar__menu-item navbar__menu-item--active"><a href="#">Inicio</a></li>
                <li class="navbar__menu-item"><a href="../repositorio/repositorio.php">Repositorio</a></li>
                <li class="navbar__menu-item"><a href="#buscar">Búsquedas</a></li>
                <li class="navbar__menu-item"><a href="#nosotros">Nosotros</a></li>
                <li class="navbar__menu-item"><a href="#recientes">Recientes</a></li>
                <li class="navbar__menu-item"><a href="#comunidad">Comunidad</a></li>
                <?php if (!$usuario_id): ?>
                    <li class="navbar__mobile-item"><a href="../register/registro.php">Registro</a></li>
                <?php endif; ?>
                <?php if ($usuario_id): ?>
                    <li class="navbar__mobile-item">
                        <!-- Mostrar la imagen de perfil en el menú móvil también -->
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
                    <li class="navbar__menu-item navbar__menu-item--button"><a href="../login/login.php">Iniciar sesión</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Resto del contenido -->
    <section class="hero">
        <div class="container hero__container">
            <div class="hero__content">
                <h1 class="hero__title">Tu biblioteca digital al alcance de todos</h1>
                <p class="hero__description">Sumérgete en un espacio creado para potenciar tu aprendizaje, donde encontrarás todo lo necesario para fortalecer tus habilidades, expandir tus conocimientos y avanzar con confianza en tu camino como programador. Todo, reunido en un solo lugar.</p>
                <div class="hero__buttons">
                    <a href="../repositorio/repositorio.php" class="btn btn--secondary">Explorar Repositorio</a>
                </div>
            </div>
            <div class="hero__image">
                <img src="../inicio/img/inicio.png" alt="Biblioteca digital" loading="lazy">
            </div>
        </div>
        <div class="hero__wave"></div>
    </section>

    <!-- Presentación de la plataforma -->
    <section id="nosotros" class="section section--white">
        <div class="container">
            <div class="features__header">
                <h2 class="features__title">Nuestra Plataforma</h2>
                <p class="features__description">El Rincón de ADSO es un repositorio digital abierto diseñado para democratizar el acceso al conocimiento y fomentar la colaboración académica.</p>
            </div>
            <div class="features__grid">
                <div class="feature-card">
                    <div class="feature-card__icon">
                        <i class="fas fa-laptop"></i>
                    </div>
                    <h3 class="feature-card__title">Amplia Colección</h3>
                    <p class="feature-card__description">Accede a más de 50,000 libros, artículos y documentos académicos en diversos formatos.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-card__icon">
                        <i class="fas fa-code"></i>
                    </div>
                    <h3 class="feature-card__title">Comunidad Activa</h3>
                    <p class="feature-card__description">Forma parte de una comunidad de lectores, investigadores y académicos que comparten conocimiento.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-card__icon">
                        <i class="fas fa-robot"></i>
                    </div>
                    <h3 class="feature-card__title">Acceso Universal</h3>
                    <p class="feature-card__description">Consulta el repositorio desde cualquier dispositivo, en cualquier momento y lugar.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Recursos Recientes -->
    <section id="recientes" class="resources-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Recursos Recientes</h2>
                <p class="section-description">Explora los recursos más recientes añadidos a nuestra colección.</p>
            </div>
            <div class="search-container">
                <div class="search-box">
                    <input type="text" id="recent-search-input" placeholder="Buscar por título, autor, tema...">
                    <button class="search-button" id="recent-search-button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
                <div class="filter-container">
                    <div class="filter-group">
                        <label for="recent-filter-type">Tipo de recurso</label>
                        <select id="recent-filter-type">
                            <option value="">Todos</option>
                            <option value="libro">Libros</option>
                            <option value="video">Videos</option>
                            <option value="documento">Documentos</option>
                            <option value="imagen">Imágenes</option>
                            <option value="otro">Otros</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="recent-filter-category">Categoría</label>
                        <select id="recent-filter-category">
                            <option value="">Todas</option>
                            <!-- Se llenará dinámicamente -->
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="recent-filter-relevance">Ordenar por</label>
                        <select id="recent-filter-relevance">
                            <option value="">Relevancia</option>
                            <option value="Low">Baja</option>
                            <option value="Medium">Media</option>
                            <option value="High">Alta</option>
                            <option value="Critical">Crítica</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="recent-filter-language">Idioma</label>
                        <select id="recent-filter-language">
                            <option value="">Todos</option>
                            <option value="es">Español</option>
                            <option value="en">Inglés</option>
                            <option value="fr">Francés</option>
                            <option value="de">Alemán</option>
                        </select>
                    </div>
                    <button class="filter-button" id="recent-filter-toggle">
                        <i class="fas fa-sliders-h"></i> Filtros avanzados
                    </button>
                </div>
            </div>
            <div class="resources-grid" id="recent-resources-grid">
                <p>Cargando recursos recientes...</p>
            </div>
            <div class="pagination" id="recent-pagination"></div>
            <div class="books__link-wrapper">
                <a href="../repositorio/repositorio.php" class="books__link">Ver todos <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </section>

    <!-- Sección de Comunidad -->
    <section id="comunidad" class="section section--community">
        <div class="container">
            <div class="community__header">
                <h2 class="community__title">Comunidad</h2>
                <p class="community__description">Descubre lo que nuestra comunidad está comentando sobre sus lecturas favoritas</p>
            </div>
            <div class="community__grid">
                <?php
                $comentarios_recientes = [
                    [
                        'usuario' => 'María García',
                        'avatar' => 'https://randomuser.me/api/portraits/women/12.jpg',
                        'fecha' => '2 días atrás',
                        'libro' => 'Python para Principiantes',
                        'comentario' => 'Este libro me ha ayudado muchísimo a entender los conceptos básicos de Python. Lo recomiendo para todos los que están comenzando en la programación.',
                        'valoracion' => 5
                    ],
                    [
                        'usuario' => 'Carlos Rodríguez',
                        'avatar' => 'https://randomuser.me/api/portraits/men/32.jpg',
                        'fecha' => '1 semana atrás',
                        'libro' => 'Código Limpio',
                        'comentario' => 'Una lectura obligada para cualquier desarrollador. Ha cambiado completamente mi forma de escribir código y de pensar en la arquitectura de software.',
                        'valoracion' => 5
                    ],
                    [
                        'usuario' => 'Laura Martínez',
                        'avatar' => 'https://randomuser.me/api/portraits/women/22.jpg',
                        'fecha' => '3 días atrás',
                        'libro' => 'Inteligencia Artificial: Un Enfoque Moderno',
                        'comentario' => 'Aunque es un libro bastante técnico, explica los conceptos de manera clara. Me ha servido mucho para mi proyecto final de carrera.',
                        'valoracion' => 4
                    ],
                    [
                        'usuario' => 'Javier López',
                        'avatar' => 'https://randomuser.me/api/portraits/men/45.jpg',
                        'fecha' => '5 días atrás',
                        'libro' => 'Inteligencia artificial: 101 cosas',
                        'comentario' => 'Un libro muy accesible para entender el impacto de la IA en nuestro futuro. Lo recomendaría a cualquiera interesado en el tema, incluso sin conocimientos técnicos.',
                        'valoracion' => 4
                    ]
                ];
                foreach ($comentarios_recientes as $comentario) {
                    echo '<div class="comment-card">';
                    echo '<div class="comment-card__header">';
                    echo '<div class="comment-card__user">';
                    echo '<img src="' . $comentario['avatar'] . '" alt="' . $comentario['usuario'] . '" class="comment-card__avatar" loading="lazy">';
                    echo '<div class="comment-card__user-info">';
                    echo '<h4 class="comment-card__username">' . $comentario['usuario'] . '</h4>';
                    echo '<span class="comment-card__date">' . $comentario['fecha'] . '</span>';
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="comment-card__rating">';
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $comentario['valoracion']) {
                            echo '<i class="fas fa-star"></i>';
                        } else {
                            echo '<i class="far fa-star"></i>';
                        }
                    }
                    echo '</div>';
                    echo '</div>';
                    echo '<div class="comment-card__content">';
                    echo '<h5 class="comment-card__book">Sobre: ' . $comentario['libro'] . '</h5>';
                    echo '<p class="comment-card__text">"' . $comentario['comentario'] . '"</p>';
                    echo '</div>';
                    echo '<div class="comment-card__footer">';
                    echo '<button class="comment-card__like"><i class="far fa-heart"></i> Me gusta</button>';
                    echo '<button class="comment-card__reply"><i class="far fa-comment"></i> Responder</button>';
                    echo '</div>';
                    echo '</div>';
                }
                ?>
            </div>
            <div class="community__cta">
                <h3 class="community__cta-title">¿Quieres compartir tu opinión?</h3>
                <p class="community__cta-text">Únete a nuestra comunidad y comparte tus pensamientos sobre tus lecturas favoritas</p>
                <a href="../login/registro.php" class="btn btn--primary community__cta-button">Crear una cuenta</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer__grid">
                <div>
                    <h3 class="footer__logo">El Rincón de ADSO</h3>
                    <p class="footer__description">Tu repositorio digital de confianza para el acceso al conocimiento académico y literario.</p>
                </div>
                <div>
                    <h4 class="footer__heading">Suscríbete</h4>
                    <p class="footer__description">Recibe actualizaciones sobre nuevos libros y recursos.</p>
                    <form class="footer__form">
                        <input type="email" placeholder="Tu email" class="footer__input" required>
                        <button type="submit" class="footer__button">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div class="footer__bottom">
                <div class="footer__copyright">
                    © <?php echo date('Y'); ?> El Rincón de ADSO. Todos los derechos reservados.
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/aos@next/dist/aos.js';
            script.onload = function() {
                AOS.init({
                    once: true,
                    disable: window.innerWidth < 768
                });
            };
            document.body.appendChild(script);

            // Toggle mobile menu
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
            });

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

            document.querySelectorAll('.comment-card__like').forEach(button => {
                button.addEventListener('click', function() {
                    const icon = this.querySelector('i');
                    if (icon.classList.contains('far')) {
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        icon.classList.add('liked');
                        this.classList.add('comment-card__like--active');
                    } else {
                        icon.classList.remove('fas');
                        icon.classList.remove('liked');
                        icon.classList.add('far');
                        this.classList.remove('comment-card__like--active');
                    }
                });
            });

            const resourcesGrid = document.getElementById('recent-resources-grid');
            const isLoggedIn = <?php echo json_encode($usuario_id !== null); ?>;
            const searchInput = document.getElementById('recent-search-input');
            const searchButton = document.getElementById('recent-search-button');
            const filterCategory = document.getElementById('recent-filter-category');
            const filterType = document.getElementById('recent-filter-type');
            const filterRelevance = document.getElementById('recent-filter-relevance');
            const filterLanguage = document.getElementById('recent-filter-language');
            const filterToggle = document.getElementById('recent-filter-toggle');
            const filterContainer = filterToggle.parentElement.parentElement; 

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
                        loadRecentResources();
                    })
                    .catch(error => {
                        console.error('Error al cargar categorías:', error);
                        filterCategory.innerHTML = '<option value="">Error al cargar categorías</option>';
                    });
            }

            loadCategories();

            filterToggle.addEventListener('click', function() {
                filterContainer.classList.toggle('active');
            });

            searchButton.addEventListener('click', loadRecentResources);
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    loadRecentResources();
                }
            });

            filterType.addEventListener('change', loadRecentResources);
            filterCategory.addEventListener('change', loadRecentResources);
            filterRelevance.addEventListener('change', loadRecentResources);
            filterLanguage.addEventListener('change', loadRecentResources);

            function loadRecentResources() {
                const search = searchInput.value.trim();
                const category = filterCategory.value;
                const type = filterType.value;
                const relevance = filterRelevance.value;
                const language = filterLanguage.value;

                const params = new URLSearchParams();
                params.append('limit', 3);
                if (search) params.append('search', search);
                if (category) params.append('category', category);
                if (type) params.append('type', type);
                if (relevance) params.append('relevance', relevance);
                if (language) params.append('language', language);

                fetch(`../../backend/gestionRecursos/get_recent_resources.php?${params.toString()}`)
                    .then(response => response.json())
                    .then(data => {
                        resourcesGrid.innerHTML = '';
                        if (data.error) {
                            resourcesGrid.innerHTML = `<p>${data.error}</p>`;
                            return;
                        }

                        if (data.length === 0) {
                            resourcesGrid.innerHTML = '<p>No se encontraron recursos recientes.</p>';
                        } else {
                            data.forEach(resource => {
                                const categorias = Array.isArray(resource.categorias) && resource.categorias.length > 0 ?
                                    resource.categorias :
                                    ['Sin categoría'];
                                const etiquetas = Array.isArray(resource.etiquetas) && resource.etiquetas.length > 0 ? resource.etiquetas : [];

                                const defaultImage = './img/default-cover.jpg';
                                const coverImage = resource.portada && resource.portada !== '' ? resource.portada : defaultImage;

                                let resourceCard;

                                if (resource.tipo === 'libro') {
                                    resourceCard = document.createElement('div');
                                    resourceCard.className = 'resource-card book-card';
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
                                            <a href="#" class="btn btn--primary view-resource" data-id="${resource.id}">
                                                <i class="fas fa-book-reader"></i> Leer ahora
                                            </a>
                                            ${isLoggedIn ? `
                                                <a href="#" class="btn btn--outline ${resource.es_favorito ? 'remove-favorite' : 'add-favorite'}" data-id="${resource.id}">
                                                    <i class="fas fa-heart${resource.es_favorito ? '-broken' : ''}"></i>
                                                    ${resource.es_favorito ? 'Quitar favorito' : 'Añadir a favoritos'}
                                                </a>
                                                <a href="#" class="btn btn--outline save-resource" data-id="${resource.id}">
                                                    <i class="fas fa-bookmark"></i> Guardar para después
                                                </a>
                                            ` : `
                                                <a href="../login/login.php" class="btn btn--outline">
                                                    <i class="fas fa-heart"></i> Añadir a favoritos
                                                </a>
                                                <a href="../login/login.php" class="btn btn--outline">
                                                    <i class="fas fa-bookmark"></i> Guardar para después
                                                </a>
                                            `}
                                        </div>
                                    </div>
                                `;
                                } else if (resource.tipo === 'video') {
                                    resourceCard = document.createElement('div');
                                    resourceCard.className = 'resource-card video-card';
                                    resourceCard.innerHTML = `
                                    <div class="resource-card__image-container">
                                        <img src="${coverImage}" alt="${resource.titulo}" class="resource-card__image" loading="lazy" onerror="this.src='${defaultImage}'">
                                        <div class="resource-card__format">${resource.tipo.toUpperCase()}</div>
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
                                            ${isLoggedIn ? `
                                                <a href="#" class="btn btn--outline ${resource.es_favorito ? 'remove-favorite' : 'add-favorite'}" data-id="${resource.id}">
                                                    <i class="fas fa-heart${resource.es_favorito ? '-broken' : ''}"></i>
                                                    ${resource.es_favorito ? 'Quitar favorito' : 'Añadir a favoritos'}
                                                </a>
                                                <a href="#" class="btn btn--outline save-resource" data-id="${resource.id}">
                                                    <i class="fas fa-bookmark"></i> Guardar para después
                                                </a>
                                            ` : `
                                                <a href="../login/login.php" class="btn btn--outline">
                                                    <i class="fas fa-heart"></i> Añadir a favoritos
                                                </a>
                                                <a href="../login/login.php" class="btn btn--outline">
                                                    <i class="fas fa-bookmark"></i> Guardar para después
                                                </a>
                                            `}
                                        </div>
                                    </div>
                                `;
                                } else {
                                    resourceCard = document.createElement('div');
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
                                            <a href="#" class="btn btn--primary view-resource" data-id="${resource.id}">
                                                <i class="fas fa-eye"></i> Ver documento
                                            </a>
                                            ${isLoggedIn ? `
                                                <a href="#" class="btn btn--outline ${resource.es_favorito ? 'remove-favorite' : 'add-favorite'}" data-id="${resource.id}">
                                                    <i class="fas fa-heart${resource.es_favorito ? '-broken' : ''}"></i>
                                                    ${resource.es_favorito ? 'Quitar favorito' : 'Añadir a favoritos'}
                                                </a>
                                                <a href="#" class="btn btn--outline save-resource" data-id="${resource.id}">
                                                    <i class="fas fa-bookmark"></i> Guardar para después
                                                </a>
                                            ` : `
                                                <a href="../login/login.php" class="btn btn--outline">
                                                    <i class="fas fa-heart"></i> Añadir a favoritos
                                                </a>
                                                <a href="../login/login.php" class="btn btn--outline">
                                                    <i class="fas fa-bookmark"></i> Guardar para después
                                                </a>
                                            `}
                                        </div>
                                    </div>
                                `;
                                }

                                resourcesGrid.appendChild(resourceCard);
                            });
                        }

                        resourcesGrid.querySelectorAll('.view-resource').forEach(button => {
                            button.addEventListener('click', (e) => {
                                e.preventDefault();
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
                                            alert('Vista registrada. Aquí iría la lógica para ver el recurso.');
                                        } else {
                                            alert(data.message);
                                        }
                                    })
                                    .catch(error => console.error('Error al registrar vista:', error));
                            });
                        });

                        resourcesGrid.querySelectorAll('.add-favorite, .remove-favorite').forEach(button => {
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
                                            loadRecentResources();
                                        } else {
                                            alert(data.message);
                                        }
                                    })
                                    .catch(error => console.error(`Error al ${action === 'add' ? 'añadir a' : 'quitar de'} favoritos:`, error));
                            });
                        });

                        resourcesGrid.querySelectorAll('.save-resource').forEach(button => {
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
                    })
                    .catch(error => {
                        console.error('Error al cargar recursos recientes:', error);
                        resourcesGrid.innerHTML = '<p>Error al cargar los recursos recientes.</p>';
                    });
            }
        });
    </script>
</body>
</html>