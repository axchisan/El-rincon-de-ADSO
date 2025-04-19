<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="../inicio/img/icono.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repositorio de Libros</title>
    <link rel="stylesheet" href="../inicio/styles.css">
    <!-- Cargar Font Awesome de manera optimizada -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
    <!-- Carga diferida de AOS -->
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" media="print" onload="this.media='all'">
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
                <li class="navbar__menu-item"><a href="#buscar">Búsquedas</a></li>
                <li class="navbar__menu-item"><a href="#nosotros">Nosotros</a></li>
                <li class="navbar__menu-item"><a href="#recientes">Recientes</a></li>
                <li class="navbar__menu-item"><a href="#comunidad">Comunidad</a></li>
                <li class="navbar__menu-item"><a href="../login/registro.php">Registro</a></li>
                <li class="navbar__menu-item navbar__menu-item--button"><a href="../login/home.php">Iniciar sesión</a></li>
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
                <li class="navbar__menu-item"><a href="#buscar">Búsquedas</a></li>
                <li class="navbar__menu-item"><a href="#nosotros">Nosotros</a></li>
                <li class="navbar__menu-item"><a href="#recientes">Recientes</a></li>
                <li class="navbar__menu-item"><a href="#comunidad">Comunidad</a></li>
                <li class="navbar__mobile-item"><a href="../login/registro.php">Registro</a></li>
                <li class="navbar__menu-item navbar__menu-item--button"><a href="../login/home.php">Iniciar sesión</a></li>
            </ul>
        </div>
    </nav>

    <!-- Sección de Inicio -->
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

    <!-- Nueva Sección de Búsqueda -->
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
        <section class="section section--white">
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

        <!-- Libros Recientes -->
        <section id="recientes" class="section section--gray">
            <div class="container">
                <div class="books__header">
                    <h2 class="books__title">Libros Recientes</h2>
                </div>

                <div class="books__grid">
                    <?php
                    // Array de libros recientes (simulado)
                    $libros_recientes = [
                        [
                            'titulo' => 'Inteligencia Artificial: Un Enfoque Moderno',
                            'autor' => 'Stuart Russell',
                            'categoria' => 'Tecnología',
                            'imagen' => 'https://images.unsplash.com/photo-1620712943543-bcc4688e7485?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'
                        ],
                        [
                            'titulo' => 'Pyhton para Principiantes',
                            'autor' => 'Daniel Correa',
                            'categoria' => 'Programación',
                            'imagen' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQy8RED7UqmmNVEEnhokd_hl8192-oCm4UPRw&s'
                        ],
                        [
                            'titulo' => 'Código Limpio: Manual de estilo para el desarrollo ágil de software',
                            'autor' => 'Robert C. Martin',
                            'categoria' => 'Desarrollo de Software',
                            'imagen' => 'https://images.cdn3.buscalibre.com/fit-in/360x360/10/fb/10fb170d7732b7dca25ebb81ded2572d.jpg'
                        ],
                        [
                            'titulo' => 'Inteligencia artificial: 101 cosas que debes saber hoy sobre nuestro futuro',
                            'autor' => 'Lasse Rouhiainen',
                            'categoria' => 'Tecnología',
                            'imagen' => 'https://m.media-amazon.com/images/I/61zcsGsOhvL._AC_UF1000,1000_QL80_.jpg'
                        ]
                    ];

                    // Mostrar cada libro con animación
                    foreach ($libros_recientes as $libro) {
                        echo '<div class="book-card">';
                        echo '<div class="book-card__image-container">';
                        echo '<img src="' . $libro['imagen'] . '" alt="' . $libro['titulo'] . '" class="book-card__image" loading="lazy">';
                        echo '</div>';
                        echo '<div class="book-card__content">';
                        echo '<span class="book-card__category">' . $libro['categoria'] . '</span>';
                        echo '<h3 class="book-card__title">' . $libro['titulo'] . '</h3>';
                        echo '<p class="book-card__author">' . $libro['autor'] . '</p>';
                        echo '<div class="book-card__footer">';
                        echo '<a href="#" class="book-card__link">Leer más</a>';
                        echo '<button class="book-card__bookmark"><i class="far fa-bookmark"></i></button>';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                    ?>
                </div>
                <div class="books__link-wrapper">
                    <a href="#" class="books__link">Ver todos <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
            </s>

            <!-- Nueva Sección de Comunidad -->
            <section id="comunidad" class="section section--community">
                <div class="container">
                    <div class="community__header">
                        <h2 class="community__title">Comunidad</h2>
                        <p class="community__description">Descubre lo que nuestra comunidad está comentando sobre sus lecturas favoritas</p>
                    </div>

                    <div class="community__grid">
                        <?php
                        // Array de comentarios recientes (simulado)
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

                        // Mostrar cada comentario
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

                            // Mostrar estrellas según valoración
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
                            &copy; <?php echo date('Y'); ?> El Rincón de ADSO. Todos los derechos reservados.
                        </div>
                    </div>
                </div>
            </footer>

            <!-- Scripts -->
            <script>
                // Cargar AOS de manera diferida
                document.addEventListener('DOMContentLoaded', function() {
                    // Cargar AOS solo cuando sea necesario
                    const script = document.createElement('script');
                    script.src = 'https://unpkg.com/aos@next/dist/aos.js';
                    script.onload = function() {
                        AOS.init({
                            once: true,
                            disable: window.innerWidth < 768 // Desactivar en móviles
                        });
                    };
                    document.body.appendChild(script);
                });

                // Toggle mobile menu
                document.getElementById('mobile-menu-button').addEventListener('click', function() {
                    const mobileMenu = document.getElementById('mobile-menu');
                    mobileMenu.classList.toggle('hidden');
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

                // Animación para las tarjetas de libros al hacer hover
                document.querySelectorAll('.book-card').forEach(card => {
                    card.addEventListener('mouseenter', function() {
                        this.classList.add('book-card--hover');
                    });
                    card.addEventListener('mouseleave', function() {
                        this.classList.remove('book-card--hover');
                    });
                });
            </script>
</body>

</html>