<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Repositorio de Libros</title>
    <link rel="stylesheet" href="../inicio/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    

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
                <li class="navbar__menu-item"><a href="#">Repositorio</a></li>
                <li class="navbar__menu-item"><a href="#">Comunidad</a></li>
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
                <li class="navbar__mobile-item navbar__mobile-item--active"><a href="#">Inicio</a></li>
                <li class="navbar__mobile-item"><a href="#">Repositorio</a></li>
                <li class="navbar__mobile-item"><a href="#">Comunidad</a></li>
                <li class="navbar__mobile-item"><a href="../login/registro.php">Registro</a></li>
                <li class="navbar__mobile-item"><a href="../login/home.php">Iniciar sesión</a></li>
            </ul>
        </div>
    </nav>

    <!-- Sección de Inicio -->
    <section class="hero">
        <div class="container hero__container">
            <div class="hero__content">
                <h1 class="hero__title">Tu biblioteca digital al alcance de todos</h1>
                <p class="hero__description">Explora una amplia colección de libros, artículos y académicos sobre programación, todo en un solo lugar.</p>
                <div class="hero__buttons">
                    <a href="#" class="btn btn--secondary">Explorar Repositorio</a>
                    <a href="../login/registro.php" class="btn btn--outline">Registrarse</a>
                </div>
            </div>
            <div class="hero__image">
                <img src="../inicio/img/inicio.jpg" alt="Biblioteca digital">
            </div>
        </div>
    </section>

      <!-- Nueva Sección de Búsqueda -->
  <section class="section section--search">
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
    <section class="section section--gray">
        <div class="container">
            <div class="books__header">
                <h2 class="books__title">Libros Recientes</h2>
                <a href="#" class="books__link">Ver todos <i class="fas fa-arrow-right"></i></a>
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
                        'titulo' => 'Historia del Arte Contemporáneo',
                        'autor' => 'María González',
                        'categoria' => 'Arte',
                        'imagen' => 'https://images.unsplash.com/photo-1544967082-d9d25d867d66?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'
                    ],
                    [
                        'titulo' => 'Economía Global en el Siglo XXI',
                        'autor' => 'Carlos Ramírez',
                        'categoria' => 'Economía',
                        'imagen' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'
                    ],
                    [
                        'titulo' => 'Fundamentos de Biología Molecular',
                        'autor' => 'Ana Martínez',
                        'categoria' => 'Ciencia',
                        'imagen' => 'https://images.unsplash.com/photo-1518932945647-7a1c969f8be2?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80'
                    ]
                ];

                // Mostrar cada libro
                foreach ($libros_recientes as $libro) {
                    echo '<div class="book-card">';
                    echo '<img src="' . $libro['imagen'] . '" alt="' . $libro['titulo'] . '" class="book-card__image">';
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
        </div>
    </section>
    <!-- Llamada a la acción -->
    <section class="section section--white">
        <div class="container">
            <div class="cta">
                <h2 class="cta__title">¿Listo para comenzar?</h2>
                <p class="cta__description">Únete a nuestra comunidad y accede a miles de recursos académicos y literarios.</p>
                <div class="cta__buttons">
                    <a href="../login/registro.php" class="btn btn--primary">Crear una cuenta</a>
                    <a href="#" class="btn btn--gray">Explorar como invitado</a>
                </div>
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

    <script>
        // Toggle mobile menu
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });
    </script>
</body>
</html>