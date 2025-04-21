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
        }
    } catch (PDOException $e) {
        // No redirigimos aquí, ya que el index puede ser visto por usuarios no logueados
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
    <link rel="stylesheet" href="./css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" media="print" onload="this.media='all'">
    <style>
        .spinner {
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-left-color: #333;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .search-container {
            margin-bottom: 20px;
        }
        .search-box {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        .search-box input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .search-box button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .search-box button:hover {
            background-color: #0056b3;
        }
        .filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .filter-group label {
            font-size: 14px;
            color: #333;
        }
        .filter-group select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            min-width: 150px;
        }
        .filter-button {
            padding: 8px 15px;
            background-color: #f0f0f0;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: background-color 0.3s;
        }
        .filter-button:hover {
            background-color: #e0e0e0;
        }
        .resources-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }
        .resource-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .resource-card:hover {
            transform: translateY(-5px);
        }
        .resource-card__image-container {
            position: relative;
            width: 100%;
            height: 150px;
        }
        .resource-card__image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .resource-card__format {
            position: absolute;
            top: 10px;
            left: 10px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
        }
        .resource-card__duration {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .resource-card__play-button {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.5);
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .resource-card:hover .resource-card__play-button {
            opacity: 1;
        }
        .resource-card__content {
            padding: 15px;
        }
        .resource-card__category {
            font-size: 12px;
            color: #777;
            margin-bottom: 5px;
        }
        .resource-card__title {
            font-size: 16px;
            margin: 0 0 5px;
            color: #333;
        }
        .resource-card__author {
            font-size: 14px;
            color: #555;
            margin-bottom: 10px;
        }
        .resource-card__meta {
            display: flex;
            gap: 15px;
            font-size: 12px;
            color: #777;
            margin-bottom: 10px;
        }
        .resource-card__meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .resource-card__tags {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 10px;
        }
        .tag {
            background: #f0f0f0;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            color: #333;
        }
        .resource-card__actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .btn {
            padding: 8px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: background-color 0.3s;
        }
        .btn--primary {
            background-color: #007bff;
            color: white;
        }
        .btn--primary:hover {
            background-color: #0056b3;
        }
        .btn--outline {
            border: 1px solid #007bff;
            color: #007bff;
            background: transparent;
        }
        .btn--outline:hover {
            background: #007bff;
            color: white;
        }
        .document-card {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .document-card__icon {
            width: 50px;
            height: 50px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            flex-shrink: 0;
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

    <!-- Sección de Búsqueda -->
    <section id="buscar" class="section section--search">
        <div class="container">
            <div class="search">
                <div class="search__header">
                    <h2 class="search__title">Encuentra tu próxima lectura</h2>
                    <p class="search__description">Explora nuestra extensa colección de libros, artículos y documentos académicos.</p>
                </div>
                <div class="search__container">
                    <form class="search__form">
                        <div class="search__main">
                            <div class="search__input-wrapper">
                                <i class="fas fa-search search__icon"></i>
                                <input type="text" class="search__input" placeholder="Buscar por título, autor o palabra clave...">
                            </div>
                            <button type="submit" class="search__button btn btn--primary">
                                Buscar
                            </button>
                        </div>
                        <div class="search__filters">
                            <div class="search__filter">
                                <select class="search__select">
                                    <option value="">Todas las categorías</option>
                                    <option value="arte">Arte</option>
                                    <option value="ciencia">Ciencia</option>
                                    <option value="economia">Economía</option>
                                    <option value="historia">Historia</option>
                                    <option value="literatura">Literatura</option>
                                    <option value="tecnologia">Tecnología</option>
                                </select>
                            </div>
                            <div class="search__filter">
                                <select class="search__select">
                                    <option value="">Todos los formatos</option>
                                    <option value="pdf">PDF</option>
                                    <option value="doc">DOC</option>
                                </select>
                            </div>
                            <div class="search__filter">
                                <select class="search__select">
                                    <option value="">Ordenar por</option>
                                    <option value="relevancia">Relevancia</option>
                                    <option value="reciente">Más reciente</option>
                                    <option value="antiguo">Más antiguo</option>
                                    <option value="az">A-Z</option>
                                    <option value="za">Z-A</option>
                                </select>
                            </div>
                            <div class="search__advanced">
                                <a href="#" class="search__advanced-link">
                                    <i class="fas fa-sliders-h"></i> Búsqueda avanzada
                                </a>
                            </div>
                        </div>
                    </form>
                    <div class="search__tags">
                        <span class="search__tag-label">Búsquedas populares:</span>
                        <div class="search__tag-container">
                            <a href="#" class="search__tag">Lenguaje de Consulta Estructurado HTML </a>
                            <a href="#" class="search__tag">Python</a>
                            <a href="#" class="search__tag">CSS</a>
                            <a href="#" class="search__tag">Inteligencia artificial</a>
                            <a href="#" class="search__tag">Java</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
    <section id="recientes" class="section section--gray">
        <div class="container">
            <div class="books__header">
                <h2 class="books__title">Recursos Recientes</h2>
                <p class="books__description">Explora los recursos más recientes añadidos a nuestra colección.</p>
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
                <div class="spinner"></div>
            </div>
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
            document.querySelectorAll('.navbar__profile-icon').forEach(icon => {
                icon.addEventListener('click', function() {
                    const menu = this.nextElementSibling;
                    menu.classList.toggle('active');
                });
            });

            // Interactividad para los botones de me gusta
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

            // Cargar los recursos recientes
            const resourcesGrid = document.getElementById('recent-resources-grid');
            const isLoggedIn = <?php echo json_encode($usuario_id !== null); ?>;
            const searchInput = document.getElementById('recent-search-input');
            const searchButton = document.getElementById('recent-search-button');
            const filterCategory = document.getElementById('recent-filter-category');
            const filterType = document.getElementById('recent-filter-type');
            const filterRelevance = document.getElementById('recent-filter-relevance');
            const filterLanguage = document.getElementById('recent-filter-language');
            const filterToggle = document.getElementById('recent-filter-toggle');
            const filterContainer = filterToggle.parentElement;

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

            // Toggle filtros
            filterToggle.addEventListener('click', function() {
                filterContainer.classList.toggle('active');
            });

            // Cargar recursos al inicio
            loadRecentResources();

            // Buscar al hacer clic en el botón o presionar Enter
            searchButton.addEventListener('click', loadRecentResources);
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    loadRecentResources();
                }
            });

            // Aplicar filtros al cambiar cualquier select
            filterType.addEventListener('change', loadRecentResources);
            filterCategory.addEventListener('change', loadRecentResources);
            filterRelevance.addEventListener('change', loadRecentResources);
            filterLanguage.addEventListener('change', loadRecentResources);

            function loadRecentResources() {
                const search = searchInput.value;
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
                                const categorias = Array.isArray(resource.categorias) && resource.categorias.length > 0 ? resource.categorias : ['Sin categoría'];
                                const etiquetas = Array.isArray(resource.etiquetas) && resource.etiquetas.length > 0 ? resource.etiquetas : [];
                                let resourceCard;

                                if (resource.tipo === 'libro' || resource.tipo === 'video') {
                                    const isVideo = resource.tipo === 'video';
                                    resourceCard = document.createElement('div');
                                    resourceCard.className = `resource-card ${isVideo ? 'video-card' : 'book-card'}`;
                                    resourceCard.innerHTML = `
                                        <div class="resource-card__image-container">
                                            <img src="${resource.portada}" alt="${resource.titulo}" class="resource-card__image" loading="lazy">
                                            <div class="resource-card__format">${resource.tipo.toUpperCase()}</div>
                                            ${isVideo ? `
                                                <div class="resource-card__duration"><i class="fas fa-clock"></i> ${resource.duracion}</div>
                                                <div class="resource-card__play-button"><i class="fas fa-play"></i></div>
                                            ` : ''}
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
                                                <a href="../repositorio/repositorio.php" class="btn btn--primary">
                                                    <i class="fas ${isVideo ? 'fa-play-circle' : 'fa-book-reader'}"></i> ${isVideo ? 'Ver video' : 'Leer ahora'}
                                                </a>
                                                ${isLoggedIn ? `
                                                    <a href="#" class="btn btn--outline ${resource.es_favorito ? 'remove-favorite' : 'add-favorite'}" data-id="${resource.id}">
                                                        <i class="fas fa-heart${resource.es_favorito ? '-broken' : ''}"></i>
                                                        ${resource.es_favorito ? 'Quitar favorito' : 'Añadir a favoritos'}
                                                    </a>
                                                ` : `
                                                    <a href="../login/login.php" class="btn btn--outline">
                                                        <i class="fas fa-heart"></i> Añadir a favoritos
                                                    </a>
                                                `}
                                            </div>
                                        </div>
                                    `;
                                } else {
                                    let iconClass = 'fas fa-file-alt';
                                    let iconColor = '#9b59b6';
                                    if (resource.tipo === 'documento') {
                                        iconClass = 'fas fa-file-pdf';
                                        iconColor = '#e74c3c';
                                    } else if (resource.tipo === 'imagen') {
                                        iconClass = 'fas fa-file-image';
                                        iconColor = '#f39c12';
                                    }

                                    resourceCard = document.createElement('div');
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
                                                <a href="../repositorio/repositorio.php" class="btn btn--primary">
                                                    <i class="fas fa-eye"></i> Ver documento
                                                </a>
                                                ${isLoggedIn ? `
                                                    <a href="#" class="btn btn--outline ${resource.es_favorito ? 'remove-favorite' : 'add-favorite'}" data-id="${resource.id}">
                                                        <i class="fas fa-heart${resource.es_favorito ? '-broken' : ''}"></i>
                                                        ${resource.es_favorito ? 'Quitar favorito' : 'Añadir a favoritos'}
                                                    </a>
                                                ` : `
                                                    <a href="../login/login.php" class="btn btn--outline">
                                                        <i class="fas fa-heart"></i> Añadir a favoritos
                                                    </a>
                                                `}
                                            </div>
                                        </div>
                                    `;
                                }

                                resourcesGrid.appendChild(resourceCard);
                            });
                        }

                        // Agregar eventos a los botones de favoritos
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
                                        loadRecentResources(); // Recargar recursos para actualizar el estado de favoritos
                                    } else {
                                        alert(data.message);
                                    }
                                })
                                .catch(error => console.error(`Error al ${action === 'add' ? 'añadir a' : 'quitar de'} favoritos:`, error));
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