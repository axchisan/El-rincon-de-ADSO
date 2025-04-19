<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="../inicio/img/icono.png">
  <title>Repositorio - El Rincón de ADSO</title>
  <link rel="stylesheet" href="./css/repositorio.css">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
  <?php session_start(); ?>
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
        <?php if (!isset($_SESSION['nombre_usuario'])): ?>
          <li class="navbar__menu-item"><a href="../register/registro.php">Registro</a></li>
        <?php endif; ?>
        <?php if (isset($_SESSION['nombre_usuario'])): ?>
          <!-- Si hay sesión activa, mostrar el icono de perfil -->
          <li class="navbar__profile">
            <i class="fas fa-user-circle navbar__profile-icon"></i>
            <div class="navbar__profile-menu">
              <a href="../panel/panel-usuario.php">Ver Perfil</a>
              <form action="../backend/logout.php" method="POST">
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
        <?php if (!isset($_SESSION['nombre_usuario'])): ?>
          <li class="navbar__mobile-item"><a href="../register/registro.php">Registro</a></li>
        <?php endif; ?>
        <?php if (isset($_SESSION['nombre_usuario'])): ?>
          <li class="navbar__mobile-item"><a href="../perfil/perfil.php">Ver Perfil</a></li>
          <li class="navbar__mobile-item">
            <form action="../backend/logout.php" method="POST">
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
          <input type="text" id="searchInput" placeholder="Buscar por título, autor, tema...">
          <button class="search-button">
            <i class="fas fa-search"></i>
          </button>
        </div>

        <div class="filter-container">
          <div class="filter-group">
            <label for="resourceType">Tipo de recurso</label>
            <select id="resourceType">
              <option value="all">Todos</option>
              <option value="books">Libros</option>
              <option value="videos">Videos</option>
              <option value="documents">Documentos</option>
            </select>
          </div>

          <div class="filter-group">
            <label for="category">Categoría</label>
            <select id="category">
              <option value="all">Todas</option>
              <option value="programming">Programación</option>
              <option value="databases">Bases de datos</option>
              <option value="webdev">Desarrollo web</option>
              <option value="mobile">Desarrollo móvil</option>
              <option value="ai">Inteligencia Artificial</option>
            </select>
          </div>

          <div class="filter-group">
            <label for="sortBy">Ordenar por</label>
            <select id="sortBy">
              <option value="relevance">Relevancia</option>
              <option value="newest">Más recientes</option>
              <option value="oldest">Más antiguos</option>
              <option value="az">A-Z</option>
              <option value="za">Z-A</option>
            </select>
          </div>

          <button class="filter-button">
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

      <div class="resources-grid">
        <?php
        // Array de libros (simulado)
        $libros = [
          [
            'id' => 1,
            'titulo' => 'Fundamentos de Programación en Python',
            'autor' => 'Ana Martínez',
            'categoria' => 'Programación',
            'imagen' => 'https://miro.medium.com/v2/resize:fit:700/1*3IcLSFuT8PQg4cUBaRXH1A.png',
            'descripcion' => 'Una introducción completa a la programación utilizando Python, ideal para principiantes.',
            'formato' => 'PDF',
            'paginas' => 320,
            'fecha' => '2022-05-15'
          ],
          [
            'id' => 2,
            'titulo' => 'Desarrollo Web Full Stack',
            'autor' => 'Carlos Gómez',
            'categoria' => 'Desarrollo web',
            'imagen' => 'data:image/jpeg;base64,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',
            'descripcion' => 'Guía completa para convertirse en desarrollador web full stack con JavaScript, Node.js y React.',
            'formato' => 'EPUB',
            'paginas' => 450,
            'fecha' => '2023-01-10'
          ],
          [
            'id' => 3,
            'titulo' => 'Arquitectura de Software Moderna',
            'autor' => 'Roberto Sánchez',
            'categoria' => 'Programación',
            'imagen' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ4SuP2eTW0D9nK9DjC3D_SbVzvVG97FAIKmQ&s',
            'descripcion' => 'Patrones y prácticas para diseñar sistemas escalables y mantenibles.',
            'formato' => 'PDF',
            'paginas' => 380,
            'fecha' => '2021-11-20'
          ],
          [
            'id' => 4,
            'titulo' => 'SQL Avanzado para Análisis de Datos',
            'autor' => 'Laura Pérez',
            'categoria' => 'Bases de datos',
            'imagen' => 'https://optim.tildacdn.one/tild6238-3035-4335-a333-306335373139/-/resize/824x/-/format/webp/IMG_3349.jpg.webp',
            'descripcion' => 'Técnicas avanzadas de SQL para extraer y analizar grandes volúmenes de datos.',
            'formato' => 'PDF',
            'paginas' => 280,
            'fecha' => '2022-08-05'
          ]
        ];

        // Mostrar cada libro
        foreach ($libros as $libro) {
          echo '<div class="resource-card book-card">';
          echo '<div class="resource-card__image-container">';
          echo '<img src="' . $libro['imagen'] . '" alt="' . $libro['titulo'] . '" class="resource-card__image" loading="lazy">';
          echo '<div class="resource-card__format">' . $libro['formato'] . '</div>';
          echo '</div>';
          echo '<div class="resource-card__content">';
          echo '<div class="resource-card__category">' . $libro['categoria'] . '</div>';
          echo '<h3 class="resource-card__title">' . $libro['titulo'] . '</h3>';
          echo '<p class="resource-card__author">Por ' . $libro['autor'] . '</p>';
          echo '<p class="resource-card__description">' . $libro['descripcion'] . '</p>';
          echo '<div class="resource-card__meta">';
          echo '<span><i class="fas fa-calendar-alt"></i> ' . date('d/m/Y', strtotime($libro['fecha'])) . '</span>';
          echo '<span><i class="fas fa-file-alt"></i> ' . $libro['paginas'] . ' páginas</span>';
          echo '</div>';
          echo '<div class="resource-card__actions">';
          echo '<a href="../login/home.php" class="btn btn--primary"><i class="fas fa-book-reader"></i> Leer ahora</a>';
          echo '<a href="../login/home.php" class="btn btn--outline"><i class="fas fa-download"></i> Descargar</a>';
          echo '</div>';
          echo '</div>';
          echo '</div>';
        }
        ?>
      </div>

      <div class="pagination">
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

      <div class="resources-grid">
        <?php
        // Array de videos (simulado)
        $videos = [
          [
            'id' => 1,
            'titulo' => 'Introducción a React.js',
            'autor' => 'Miguel Torres',
            'categoria' => 'Desarrollo web',
            'thumbnail' => 'https://img.youtube.com/vi/w7ejDZ8SWv8/maxresdefault.jpg',
            'descripcion' => 'Aprende los conceptos básicos de React.js y cómo crear tu primera aplicación.',
            'duracion' => '45:20',
            'fecha' => '2023-02-18'
          ],
          [
            'id' => 2,
            'titulo' => 'Curso de Node.js desde cero',
            'autor' => 'Patricia Ruiz',
            'categoria' => 'Desarrollo web',
            'thumbnail' => 'https://img.youtube.com/vi/TlB_eWDSMt4/maxresdefault.jpg',
            'descripcion' => 'Aprende a crear aplicaciones backend con Node.js y Express desde los fundamentos.',
            'duracion' => '1:22:45',
            'fecha' => '2022-11-30'
          ],
          [
            'id' => 3,
            'titulo' => 'Machine Learning con Python',
            'autor' => 'Javier López',
            'categoria' => 'Inteligencia Artificial',
            'thumbnail' => 'https://img.youtube.com/vi/7eh4d6sabA0/maxresdefault.jpg',
            'descripcion' => 'Introducción práctica al aprendizaje automático utilizando Python y scikit-learn.',
            'duracion' => '2:10:15',
            'fecha' => '2023-03-05'
          ],
          [
            'id' => 4,
            'titulo' => 'Desarrollo de Apps con Flutter',
            'autor' => 'Sofía Morales',
            'categoria' => 'Desarrollo móvil',
            'thumbnail' => 'https://img.youtube.com/vi/1ukSR1GRtMU/maxresdefault.jpg',
            'descripcion' => 'Crea aplicaciones multiplataforma para iOS y Android con Flutter y Dart.',
            'duracion' => '1:45:30',
            'fecha' => '2022-09-12'
          ]
        ];

        // Mostrar cada video
        foreach ($videos as $video) {
          echo '<div class="resource-card video-card">';
          echo '<div class="resource-card__image-container">';
          echo '<img src="' . $video['thumbnail'] . '" alt="' . $video['titulo'] . '" class="resource-card__image" loading="lazy">';
          echo '<div class="resource-card__duration"><i class="fas fa-clock"></i> ' . $video['duracion'] . '</div>';
          echo '<div class="resource-card__play-button"><i class="fas fa-play"></i></div>';
          echo '</div>';
          echo '<div class="resource-card__content">';
          echo '<div class="resource-card__category">' . $video['categoria'] . '</div>';
          echo '<h3 class="resource-card__title">' . $video['titulo'] . '</h3>';
          echo '<p class="resource-card__author">Por ' . $video['autor'] . '</p>';
          echo '<p class="resource-card__description">' . $video['descripcion'] . '</p>';
          echo '<div class="resource-card__meta">';
          echo '<span><i class="fas fa-calendar-alt"></i> ' . date('d/m/Y', strtotime($video['fecha'])) . '</span>';
          echo '</div>';
          echo '<div class="resource-card__actions">';
          echo '<a href="../login/home.php" class="btn btn--primary"><i class="fas fa-play-circle"></i> Ver video</a>';
          echo '<a href="../login/home.php" class="btn btn--outline"><i class="fas fa-list"></i> Añadir a lista</a>';
          echo '</div>';
          echo '</div>';
          echo '</div>';
        }
        ?>
      </div>

      <div class="pagination">
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

      <div class="resources-grid resources-grid--documents">
        <?php
        // Array de documentos (simulado)
        $documentos = [
          [
            'id' => 1,
            'titulo' => 'Guía de Buenas Prácticas en Git',
            'autor' => 'Equipo DevOps',
            'categoria' => 'Programación',
            'icono' => 'fas fa-file-pdf',
            'color' => '#e74c3c',
            'descripcion' => 'Aprende las mejores prácticas para gestionar repositorios Git en equipos de desarrollo.',
            'formato' => 'PDF',
            'paginas' => 25,
            'fecha' => '2023-01-05'
          ],
          [
            'id' => 2,
            'titulo' => 'Introducción a Docker y Contenedores',
            'autor' => 'Marina Vázquez',
            'categoria' => 'DevOps',
            'icono' => 'fas fa-file-powerpoint',
            'color' => '#e67e22',
            'descripcion' => 'Presentación detallada sobre los fundamentos de Docker y la contenerización de aplicaciones.',
            'formato' => 'PPTX',
            'paginas' => 45,
            'fecha' => '2022-12-10'
          ],
          [
            'id' => 3,
            'titulo' => 'Seguridad en Aplicaciones Web',
            'autor' => 'Carlos Segura',
            'categoria' => 'Seguridad',
            'icono' => 'fas fa-file-word',
            'color' => '#3498db',
            'descripcion' => 'Documento técnico sobre vulnerabilidades comunes y cómo proteger aplicaciones web.',
            'formato' => 'DOCX',
            'paginas' => 38,
            'fecha' => '2023-02-22'
          ],
          [
            'id' => 4,
            'titulo' => 'Optimización de Bases de Datos SQL',
            'autor' => 'Elena Rodríguez',
            'categoria' => 'Bases de datos',
            'icono' => 'fas fa-file-excel',
            'color' => '#2ecc71',
            'descripcion' => 'Técnicas y ejemplos para mejorar el rendimiento de consultas SQL en grandes bases de datos.',
            'formato' => 'XLSX',
            'paginas' => 20,
            'fecha' => '2022-10-15'
          ],
          [
            'id' => 5,
            'titulo' => 'Arquitectura Serverless',
            'autor' => 'Pablo Hernández',
            'categoria' => 'Cloud Computing',
            'icono' => 'fas fa-file-alt',
            'color' => '#9b59b6',
            'descripcion' => 'Guía completa sobre arquitecturas serverless y su implementación en AWS y Azure.',
            'formato' => 'PDF',
            'paginas' => 42,
            'fecha' => '2023-03-10'
          ],
          [
            'id' => 6,
            'titulo' => 'Introducción a GraphQL',
            'autor' => 'Lucía Fernández',
            'categoria' => 'Desarrollo web',
            'icono' => 'fas fa-file-code',
            'color' => '#f39c12',
            'descripcion' => 'Tutorial paso a paso para implementar APIs con GraphQL en aplicaciones modernas.',
            'formato' => 'HTML',
            'paginas' => 30,
            'fecha' => '2022-11-18'
          ]
        ];

        // Mostrar cada documento
        foreach ($documentos as $documento) {
          echo '<div class="resource-card document-card">';
          echo '<div class="document-card__icon" style="background-color: ' . $documento['color'] . ';">';
          echo '<i class="' . $documento['icono'] . '"></i>';
          echo '</div>';
          echo '<div class="resource-card__content">';
          echo '<div class="resource-card__category">' . $documento['categoria'] . '</div>';
          echo '<h3 class="resource-card__title">' . $documento['titulo'] . '</h3>';
          echo '<p class="resource-card__author">Por ' . $documento['autor'] . '</p>';
          echo '<p class="resource-card__description">' . $documento['descripcion'] . '</p>';
          echo '<div class="resource-card__meta">';
          echo '<span><i class="fas fa-calendar-alt"></i> ' . date('d/m/Y', strtotime($documento['fecha'])) . '</span>';
          echo '<span><i class="fas fa-file"></i> ' . $documento['formato'] . '</span>';
          echo '<span><i class="fas fa-file-alt"></i> ' . $documento['paginas'] . ' páginas</span>';
          echo '</div>';
          echo '<div class="resource-card__actions">';
          echo '<a href="../login/home.php" class="btn btn--primary"><i class="fas fa-eye"></i> Ver documento</a>';
          echo '<a href="../login/home.php" class="btn btn--outline"><i class="fas fa-download"></i> Descargar</a>';
          echo '</div>';
          echo '</div>';
          echo '</div>';
        }
        ?>
      </div>

      <div class="pagination">
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
            <a href="../login/registro.php" class="btn btn--primary btn--lg">Crear cuenta</a>
            <a href="../login/home.php" class="btn btn--outline btn--lg">Iniciar sesión</a>
          </div>
        </div>
        <div class="cta-image">
          <img src="https://cdn-icons-png.flaticon.com/512/10616/10616326.png" alt="Acceso a recursos" loading="lazy">
        </div>
      </div>
    </div>
  </section>
  <!-- Scripts -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
          // Toggle mobile menu
          const mobileMenuButton = document.getElementById('mobile-menu-button');
          const mobileMenu = document.getElementById('mobile-menu');

          if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', function() {
              mobileMenu.classList.toggle('hidden');
            });
          }

          // Tabs functionality
          const tabButtons = document.querySelectorAll('.tab-button');
          const resourceSections = document.querySelectorAll('.resources-section');

          tabButtons.forEach(button => {
            button.addEventListener('click', function() {
              // Remove active class from all buttons
              tabButtons.forEach(btn => btn.classList.remove('active'));

              // Add active class to clicked button
              this.classList.add('active');

              // Get the tab to show
              const tabToShow = this.getAttribute('data-tab');

              if (tabToShow === 'all') {
                // Show all sections
                resourceSections.forEach(section => {
                  section.style.display = 'block';
                });
              } else {
                // Hide all sections first
                resourceSections.forEach(section => {
                  section.style.display = 'none';
                });

                // Show only the selected section
                const sectionToShow = document.getElementById(tabToShow + 'Section');
                if (sectionToShow) {
                  sectionToShow.style.display = 'block';
                }
              }
            });
          });

          // Search functionality
          const searchInput = document.getElementById('searchInput');
          const resourceCards = document.querySelectorAll('.resource-card');

          if (searchInput) {
            searchInput.addEventListener('input', function() {
              const searchTerm = this.value.toLowerCase();

              resourceCards.forEach(card => {
                const title = card.querySelector('.resource-card__title').textContent.toLowerCase();
                const author = card.querySelector('.resource-card__author').textContent.toLowerCase();
                const description = card.querySelector('.resource-card__description').textContent.toLowerCase();
                const category = card.querySelector('.resource-card__category').textContent.toLowerCase();

                if (title.includes(searchTerm) ||
                  author.includes(searchTerm) ||
                  description.includes(searchTerm) ||
                  category.includes(searchTerm)) {
                  card.style.display = 'block';
                } else {
                  card.style.display = 'none';
                }
              });
            });
          }

          // Filter functionality
          const resourceTypeSelect = document.getElementById('resourceType');
          const categorySelect = document.getElementById('category');

          function applyFilters() {
            const resourceType = resourceTypeSelect.value;
            const category = categorySelect.value;

            resourceCards.forEach(card => {
              let showByType = true;
              let showByCategory = true;

              // Filter by resource type
              if (resourceType !== 'all') {
                if (resourceType === 'books' && !card.classList.contains('book-card')) {
                  showByType = false;
                } else if (resourceType === 'videos' && !card.classList.contains('video-card')) {
                  showByType = false;
                } else if (resourceType === 'documents' && !card.classList.contains('document-card')) {
                  showByType = false;
                }
              }

              // Filter by category
              if (category !== 'all') {
                const cardCategory = card.querySelector('.resource-card__category').textContent.toLowerCase();
                if (!cardCategory.includes(category.toLowerCase())) {
                  showByCategory = false;
                }
              }

              // Show or hide card based on filters
              if (showByType && showByCategory) {
                card.style.display = 'block';
              } else {
                card.style.display = 'none';
              }
            });
          }

          if (resourceTypeSelect && categorySelect) {
            resourceTypeSelect.addEventListener('change', applyFilters);
            categorySelect.addEventListener('change', applyFilters);
          }

          // Subscription form handling
          const subscriptionForm = document.getElementById('subscriptionForm');
          const subscriptionMessage = document.getElementById('subscriptionMessage');
          const subscribeBtn = document.getElementById('subscribeBtn');

          if (subscriptionForm) {
            subscriptionForm.addEventListener('submit', function(e) {
              e.preventDefault();
              {
                subscriptionForm.addEventListener('submit', function(e) {
                  e.preventDefault();

                  // Cambiar el botón a estado de carga
                  const originalBtnContent = subscribeBtn.innerHTML;
                  subscribeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                  subscribeBtn.disabled = true;

                  // Obtener el email
                  const emailInput = this.querySelector('input[name="email"]');
                  const email = emailInput.value;

                  // Crear objeto FormData
                  const formData = new FormData();
                  formData.append('email', email);

                  // Enviar solicitud AJAX
                  fetch('procesar-suscripcion.php', {
                      method: 'POST',
                      body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                      // Mostrar mensaje
                      subscriptionMessage.textContent = data.message;
                      subscriptionMessage.className = 'footer__message footer__message--' + data.status;

                      // Si fue exitoso, limpiar el campo
                      if (data.status === 'success') {
                        emailInput.value = '';
                      }

                      // Restaurar el botón
                      subscribeBtn.innerHTML = originalBtnContent;
                      subscribeBtn.disabled = false;

                      // Ocultar el mensaje después de 5 segundos
                      setTimeout(() => {
                        subscriptionMessage.textContent = '';
                        subscriptionMessage.className = 'footer__message';
                      }, 5000);
                    })
                    .catch(error => {
                      console.error('Error:', error);
                      subscriptionMessage.textContent = 'Ha ocurrido un error. Por favor, inténtalo de nuevo.';
                      subscriptionMessage.className = 'footer__message footer__message--error';

                      // Restaurar el botón
                      subscribeBtn.innerHTML = originalBtnContent;
                      subscribeBtn.disabled = false;
                    });
                });
              }
            });
  </script>
</body>

</html>
<?
