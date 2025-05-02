<?php
session_start();
require_once "../../database/conexionDB.php"; //ruta
$usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
$nombre_usuario = '';
$usuario_imagen = '';
$unread_count = 0; // contador notificaciones no leídas

if ($usuario_id) {
    try {
        $db = conexionDB::getConexion();
        $query = "SELECT nombre_usuario, imagen FROM usuarios WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->execute([':id' => $usuario_id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($usuario) {
            $nombre_usuario = htmlspecialchars($usuario['nombre_usuario']);
            // Constructor de la ruta de la imagen del usuario
            $usuario_imagen = $usuario['imagen'] ? "../../backend/perfil/" . htmlspecialchars($usuario['imagen']) . "?v=" . time() : "./img/default-avatar.png";
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
    <meta name="description" content="Tu biblioteca digital al alcance de todos
Sumérgete en un espacio creado para potenciar tu aprendizaje, donde encontrarás todo lo necesario para fortalecer tus habilidades, expandir tus conocimientos y avanzar con confianza en tu camino como programador. Todo, reunido en un solo lugar.">
    <title>Repositorio de Libros</title>
    <link rel="stylesheet" href="../repositorio/css/repositorio.css">
    <link rel="stylesheet" href="./css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" media="print" onload="this.media='all'">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
        <div class="container" id="buscar">
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
                <div class="community__buttons">
                    <a href="../eventos/eventos.php" class="btn btn--secondary">Eventos</a>
                    <a href="../foro/foro.php" class="btn btn--secondary">Foros</a>
                </div>
            </div>

            <!-- Form. agregar un comentario solo si hay sesión activa -->
            <?php if ($usuario_id): ?>
                <div class="community__add-comment">
                    <h3 class="community__add-comment-title">Comparte tu opinión</h3>
                    <form id="add-comment-form" action="../../backend/comunidad/add_comment.php" method="POST">
                        <div class="form-group">
                            <label for="libro">Sobre:</label>
                            <input type="text" id="libro" name="libro" placeholder="Nombre del libro" required>
                        </div>
                        <div class="form-group">
                            <label for="comentario">Comentario:</label>
                            <textarea id="comentario" name="comentario" placeholder="Escribe tu comentario..." required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Calificación:</label>
                            <div class="star-rating" id="star-rating">
                                <input type="hidden" name="valoracion" id="valoracion" value="0">
                                <span class="star" data-value="1"><i class="far fa-star"></i></span>
                                <span class="star" data-value="2"><i class="far fa-star"></i></span>
                                <span class="star" data-value="3"><i class="far fa-star"></i></span>
                                <span class="star" data-value="4"><i class="far fa-star"></i></span>
                                <span class="star" data-value="5"><i class="far fa-star"></i></span>
                            </div>
                        </div>
                        <button type="submit" class="btn btn--primary">Publicar Comentario</button>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Lista de comentarios -->
            <div class="community__grid" id="community-grid">
                <p>Cargando comentarios...</p>
            </div>

            <!-- Paginación -->
            <div class="pagination" id="community-pagination">
                <button id="load-more-comments" class="btn btn--secondary">Cargar más</button>
            </div>

            <!-- Mostrar solo si NO hay sesión activa -->
            <?php if (!$usuario_id): ?>
                <div class="community__cta">
                    <h3 class="community__cta-title">¿Quieres compartir tu opinión?</h3>
                    <p class="community__cta-text">Únete a nuestra comunidad y comparte tus pensamientos sobre tus lecturas favoritas</p>
                    <a href="../register/registro.php" class="btn btn--primary community__cta-button">Crear una cuenta</a>
                </div>
            <?php endif; ?>
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
                    event.stopPropagation();
                    profileMenu.classList.toggle('active');
                });

                document.addEventListener('click', function(event) {
                    if (!profileImg.contains(event.target) && !profileMenu.contains(event.target)) {
                        profileMenu.classList.remove('active');
                    }
                });
            } else {
                console.error('No se encontraron los elementos profile-img o profile-menu');
            }

            // Estrellas interactivas
            const starRating = document.getElementById('star-rating');
            const valoracionInput = document.getElementById('valoracion');
            if (starRating) {
                const stars = starRating.querySelectorAll('.star');
                stars.forEach(star => {
                    star.addEventListener('click', function() {
                        const value = this.getAttribute('data-value');
                        valoracionInput.value = value;

                        stars.forEach(s => {
                            s.classList.remove('selected');
                            const icon = s.querySelector('i');
                            icon.classList.remove('fas');
                            icon.classList.add('far');
                        });

                        for (let i = 0; i < value; i++) {
                            stars[i].classList.add('selected');
                            const icon = stars[i].querySelector('i');
                            icon.classList.remove('far');
                            icon.classList.add('fas');
                        }
                    });

                    star.addEventListener('mouseover', function() {
                        const value = this.getAttribute('data-value');
                        for (let i = 0; i < value; i++) {
                            const icon = stars[i].querySelector('i');
                            if (!stars[i].classList.contains('selected')) {
                                icon.classList.remove('far');
                                icon.classList.add('fas');
                            }
                        }
                    });

                    star.addEventListener('mouseout', function() {
                        stars.forEach(s => {
                            if (!s.classList.contains('selected')) {
                                const icon = s.querySelector('i');
                                icon.classList.remove('fas');
                                icon.classList.add('far');
                            }
                        });
                    });
                });
            }

            // Lógica formulario de agregar comentario
            const addCommentForm = document.getElementById('add-comment-form');
            if (addCommentForm) {
                addCommentForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    const valoracion = valoracionInput.value;

                    if (parseInt(valoracion) < 1 || parseInt(valoracion) > 5) {
                        alert('Por favor, selecciona una calificación entre 1 y 5 estrellas.');
                        return;
                    }

                    fetch(this.action, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert(data.message);
                                currentPage = 1; // Reiniciar a la primera página
                                loadCommunityComments();
                                addCommentForm.reset();
                                starRating.querySelectorAll('.star').forEach(star => {
                                    star.classList.remove('selected');
                                    const icon = star.querySelector('i');
                                    icon.classList.remove('fas');
                                    icon.classList.add('far');
                                });
                                valoracionInput.value = 0;
                            } else {
                                alert(data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error al agregar comentario:', error);
                            alert('Error al agregar el comentario. Por favor, intenta de nuevo.');
                        });
                });
            }

            // Botones like
            function attachLikeButtonListeners() {
                document.querySelectorAll('.comment-card__like').forEach(button => {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        const comentarioId = this.getAttribute('data-comment-id');
                        if (!comentarioId) return;

                        fetch('../../backend/comunidad/toggle_like.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: `comentario_id=${comentarioId}`
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    const icon = this.querySelector('i');
                                    if (data.action === 'added') {
                                        icon.classList.remove('far');
                                        icon.classList.add('fas', 'liked');
                                        this.classList.add('comment-card__like--active');
                                    } else {
                                        icon.classList.remove('fas', 'liked');
                                        icon.classList.add('far');
                                        this.classList.remove('comment-card__like--active');
                                    }
                                    this.innerHTML = `<i class="${icon.className} fa-heart"></i> Me gusta (${data.likes})`;
                                } else {
                                    alert(data.message);
                                }
                            })
                            .catch(error => {
                                console.error('Error al manejar el me gusta:', error);
                                alert('Error al procesar el me gusta. Por favor, intenta de nuevo.');
                            });
                    });
                });
            }

            // Botones de Responder
            function attachReplyButtonListeners() {
                document.querySelectorAll('.comment-card__reply').forEach(button => {
                    button.addEventListener('click', function() {
                        const commentId = this.closest('.comment-card').getAttribute('data-comment-id');
                        const replyForm = document.getElementById(`reply-form-${commentId}`);
                        if (replyForm) {
                            replyForm.style.display = replyForm.style.display === 'block' ? 'none' : 'block';
                        }
                    });
                });
            }

            // Lógica envío de respuestas
            function attachReplyFormListeners() {
                document.querySelectorAll('.add-reply-form').forEach(form => {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const comentarioId = this.getAttribute('data-comment-id');
                        const respuesta = this.querySelector('textarea[name="respuesta"]').value;

                        fetch('../../backend/comunidad/add_reply.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: `comentario_id=${comentarioId}&respuesta=${encodeURIComponent(respuesta)}`
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert(data.message);
                                    currentPage = 1; // Reiniciar a la primera página
                                    loadCommunityComments();
                                    this.reset();
                                    this.closest('.reply-form').style.display = 'none';
                                } else {
                                    alert(data.message);
                                }
                            })
                            .catch(error => {
                                console.error('Error al agregar respuesta:', error);
                                alert('Error al agregar la respuesta. Por favor, intenta de nuevo.');
                            });
                    });
                });
            }

            // Botones de Editar y Eliminar
            function attachEditDeleteListeners() {
                // Botones de eliminar
                document.querySelectorAll('.comment-card__delete').forEach(button => {
                    button.addEventListener('click', function() {
                        const comentarioId = this.getAttribute('data-comment-id');
                        if (confirm('¿Estás seguro de que quieres eliminar este comentario?')) {
                            fetch('../../backend/comunidad/delete_comment.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: `comentario_id=${comentarioId}`
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert(data.message);
                                    currentPage = 1; // Reiniciar a la primera página
                                    loadCommunityComments();
                                } else {
                                    alert(data.message);
                                }
                            })
                            .catch(error => {
                                console.error('Error al eliminar comentario:', error);
                                alert('Error al eliminar el comentario. Por favor, intenta de nuevo.');
                            });
                        }
                    });
                });

                // Botones de editar
                document.querySelectorAll('.comment-card__edit').forEach(button => {
                    button.addEventListener('click', function() {
                        const comentarioId = this.getAttribute('data-comment-id');
                        const commentCard = this.closest('.comment-card');
                        const libro = commentCard.querySelector('.comment-card__book').textContent.replace('Sobre: ', '');
                        const comentario = commentCard.querySelector('.comment-card__text').textContent.replace(/"/g, '');
                        const valoracion = Array.from(commentCard.querySelectorAll('.comment-card__rating .fas.fa-star')).length;

                        // Crear formulario de edición
                        let editForm = commentCard.querySelector('.edit-comment-form');
                        if (!editForm) {
                            editForm = document.createElement('div');
                            editForm.className = 'edit-comment-form';
                            editForm.innerHTML = `
                                <form data-comment-id="${comentarioId}">
                                    <div class="form-group">
                                        <label for="edit-libro-${comentarioId}">Sobre:</label>
                                        <input type="text" id="edit-libro-${comentarioId}" name="libro" value="${libro}" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="edit-comentario-${comentarioId}">Comentario:</label>
                                        <textarea id="edit-comentario-${comentarioId}" name="comentario" required>${comentario}</textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>Calificación:</label>
                                        <div class="star-rating" id="edit-star-rating-${comentarioId}">
                                            <input type="hidden" name="valoracion" id="edit-valoracion-${comentarioId}" value="${valoracion}">
                                            <span class="star" data-value="1"><i class="${valoracion >= 1 ? 'fas' : 'far'} fa-star"></i></span>
                                            <span class="star" data-value="2"><i class="${valoracion >= 2 ? 'fas' : 'far'} fa-star"></i></span>
                                            <span class="star" data-value="3"><i class="${valoracion >= 3 ? 'fas' : 'far'} fa-star"></i></span>
                                            <span class="star" data-value="4"><i class="${valoracion >= 4 ? 'fas' : 'far'} fa-star"></i></span>
                                            <span class="star" data-value="5"><i class="${valoracion >= 5 ? 'fas' : 'far'} fa-star"></i></span>
                                        </div>
                                    </div>
                                    <button type="submit">Guardar</button>
                                    <button type="button" class="cancel-edit">Cancelar</button>
                                </form>
                            `;
                            commentCard.querySelector('.comment-card__content').appendChild(editForm);

                            // Lógica para las estrellas
                            const starRating = editForm.querySelector(`#edit-star-rating-${comentarioId}`);
                            const valoracionInput = editForm.querySelector(`#edit-valoracion-${comentarioId}`);
                            const stars = starRating.querySelectorAll('.star');
                            stars.forEach(star => {
                                star.addEventListener('click', function() {
                                    const value = this.getAttribute('data-value');
                                    valoracionInput.value = value;

                                    stars.forEach(s => {
                                        s.classList.remove('selected');
                                        const icon = s.querySelector('i');
                                        icon.classList.remove('fas');
                                        icon.classList.add('far');
                                    });

                                    for (let i = 0; i < value; i++) {
                                        stars[i].classList.add('selected');
                                        const icon = stars[i].querySelector('i');
                                        icon.classList.remove('far');
                                        icon.classList.add('fas');
                                    }
                                });
                            });

                            // Lógica para el formulario
                            editForm.querySelector('form').addEventListener('submit', function(e) {
                                e.preventDefault();
                                const formData = new FormData(this);
                                formData.append('comentario_id', comentarioId);

                                fetch('../../backend/comunidad/edit_comment.php', {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        alert(data.message);
                                        editForm.remove();
                                        loadCommunityComments(currentPage);
                                    } else {
                                        alert(data.message);
                                    }
                                })
                                .catch(error => {
                                    console.error('Error al editar comentario:', error);
                                    alert('Error al editar el comentario. Por favor, intenta de nuevo.');
                                });
                            });

                            // Botón de cancelar
                            editForm.querySelector('.cancel-edit').addEventListener('click', () => {
                                editForm.remove();
                            });
                        }

                        editForm.style.display = 'block';
                    });
                });
            }

            // Cargar comentarios dinámicamente con paginación
            let currentPage = 1;
            let totalPages = 1;

            function loadCommunityComments(page = 1) {
                const communityGrid = document.getElementById('community-grid');
                const loadMoreButton = document.getElementById('load-more-comments');
                const isLoggedIn = <?php echo json_encode($usuario_id !== null); ?>;
                const currentUserId = <?php echo json_encode($usuario_id ?: 0); ?>;

                fetch(`../../backend/comunidad/get_comments.php?page=${page}&limit=4`)
                    .then(response => response.json())
                    .then(data => {
                        if (page === 1) {
                            communityGrid.innerHTML = ''; // Limpiar si es la primera página
                        }

                        if (data.error) {
                            communityGrid.innerHTML = `<p>${data.error}</p>`;
                            loadMoreButton.style.display = 'none';
                            return;
                        }

                        totalPages = data.total_pages;
                        currentPage = data.current_page;

                        if (data.comments.length === 0) {
                            communityGrid.innerHTML = '<p>No hay comentarios en la comunidad todavía. ¡Sé el primero en compartir tu opinión!</p>';
                            loadMoreButton.style.display = 'none';
                        } else {
                            data.comments.forEach(comentario => {
                                const fecha = new Date(comentario.fecha_creacion);
                                const diffDays = Math.floor((new Date() - fecha) / (1000 * 60 * 60 * 24));
                                const fechaTexto = diffDays === 0 ? 'Hoy' : `${diffDays} días atrás`;

                                const commentCard = document.createElement('div');
                                commentCard.className = 'comment-card';
                                commentCard.setAttribute('data-comment-id', comentario.id);
                                let commentHTML = `
                                    <div class="comment-card__header">
                                        <div class="comment-card__user">
                                            <img src="${comentario.avatar}" alt="${comentario.nombre_usuario}" class="comment-card__avatar" loading="lazy">
                                            <div class="comment-card__user-info">
                                                <h4 class="comment-card__username">${comentario.nombre_usuario}</h4>
                                                <span class="comment-card__date">${fechaTexto}</span>
                                            </div>
                                        </div>
                                        <div class="comment-card__rating">
                                            ${Array.from({ length: 5 }, (_, i) => 
                                                i < comentario.valoracion 
                                                ? '<i class="fas fa-star"></i>' 
                                                : '<i class="far fa-star"></i>'
                                            ).join('')}
                                        </div>
                                    </div>
                                    <div class="comment-card__content">
                                        <h5 class="comment-card__book">Sobre: ${comentario.libro}</h5>
                                        <p class="comment-card__text">"${comentario.comentario}"</p>
                                    </div>
                                    <div class="comment-card__footer">
                                        ${isLoggedIn ? `
                                            <button class="comment-card__like ${comentario.user_liked ? 'comment-card__like--active' : ''}" data-comment-id="${comentario.id}">
                                                <i class="${comentario.user_liked ? 'fas liked' : 'far'} fa-heart"></i> Me gusta (${comentario.likes})
                                            </button>
                                        ` : `
                                            <a href="../login/login.php" class="comment-card__like">
                                                <i class="far fa-heart"></i> Me gusta (${comentario.likes})
                                            </a>
                                        `}
                                        <button class="comment-card__reply"><i class="far fa-comment"></i> Responder</button>
                                `;

                                // Añadir botones de editar y eliminar solo si el usuario es el dueño
                                if (isLoggedIn && currentUserId === comentario.usuario_id) {
                                    commentHTML += `
                                        <button class="comment-card__edit" data-comment-id="${comentario.id}"><i class="fas fa-edit"></i> Editar</button>
                                        <button class="comment-card__delete" data-comment-id="${comentario.id}"><i class="fas fa-trash"></i> Eliminar</button>
                                    `;
                                }

                                commentHTML += `</div>`;

                                // Formulario para responder
                                if (isLoggedIn) {
                                    commentHTML += `
                                        <div class="reply-form" id="reply-form-${comentario.id}">
                                            <form class="add-reply-form" data-comment-id="${comentario.id}">
                                                <textarea name="respuesta" placeholder="Escribe tu respuesta..." required></textarea>
                                                <button type="submit">Enviar Respuesta</button>
                                            </form>
                                        </div>
                                    `;
                                }

                                // Mostrar respuestas
                                if (comentario.respuestas && comentario.respuestas.length > 0) {
                                    commentHTML += '<div class="comment-card__replies">';
                                    comentario.respuestas.forEach(respuesta => {
                                        const replyFecha = new Date(respuesta.fecha_creacion);
                                        const replyDiffDays = Math.floor((new Date() - replyFecha) / (1000 * 60 * 60 * 24));
                                        const replyFechaTexto = replyDiffDays === 0 ? 'Hoy' : `${replyDiffDays} días atrás`;

                                        commentHTML += `
                                            <div class="reply-card">
                                                <img src="${respuesta.avatar}" alt="${respuesta.nombre_usuario}" class="reply-card__avatar" loading="lazy">
                                                <div class="reply-card__content">
                                                    <h5 class="reply-card__username">${respuesta.nombre_usuario}</h5>
                                                    <span class="reply-card__date">${replyFechaTexto}</span>
                                                    <p class="reply-card__text">${respuesta.respuesta}</p>
                                                </div>
                                            </div>
                                        `;
                                    });
                                    commentHTML += '</div>';
                                }

                                commentCard.innerHTML = commentHTML;
                                communityGrid.appendChild(commentCard);
                            });

                            // Mostrar u ocultar el botón "Cargar más"
                            loadMoreButton.style.display = currentPage < totalPages ? 'block' : 'none';

                            attachLikeButtonListeners();
                            attachReplyButtonListeners();
                            attachReplyFormListeners();
                            attachEditDeleteListeners();
                        }
                    })
                    .catch(error => {
                        console.error('Error al cargar comentarios:', error);
                        communityGrid.innerHTML = '<p>Error al cargar los comentarios. Por favor, intenta de nuevo más tarde.</p>';
                        loadMoreButton.style.display = 'none';
                    });
            }

            // Evento para el botón "Cargar más"
            document.getElementById('load-more-comments').addEventListener('click', () => {
                currentPage++;
                loadCommunityComments(currentPage);
            });

            // Cargar comentarios al iniciar
            loadCommunityComments();

            // Recursos recientes (sin cambios)
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
                                    resource.categorias : ['Sin categoría'];
                                const etiquetas = Array.isArray(resource.etiquetas) && resource.etiquetas.length > 0 ? resource.etiquetas : [];

                                const defaultImage = './img/default-cover.jpg';
                                const coverImage = resource.portada && resource.portada !== '' ? resource.portada : defaultImage;

                                let resourceCard;
                                let viewUrl;

                                if (resource.tipo === 'video') {
                                    viewUrl = `../repositorio/ver_video.php?id=${resource.id}`;
                                } else if (resource.tipo === 'libro') {
                                    viewUrl = `../repositorio/ver_libro.php?id=${resource.id}`;
                                } else {
                                    viewUrl = `../repositorio/ver_documento.php?id=${resource.id}`;
                                }

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
                                                <a href="${viewUrl}" class="btn btn--primary view-resource" data-id="${resource.id}">
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
                                                <a href="${viewUrl}" class="btn btn--primary view-resource" data-id="${resource.id}">
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
                                                <a href="${viewUrl}" class="btn btn--primary view-resource" data-id="${resource.id}">
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

        // Estilos rápidos para alerta centrada
        const estiloAlerta = `
            background: #e3f2fd;
            color: #0277bd;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            font-family: Arial, sans-serif;
            font-size: 16px;
            display: inline-block;
            border-left: 6px solid #0277bd;
        `;

        // Sobrescribe alert() para mostrar en el centro
        window.alert = function(mensaje) {
            const contenedor = document.getElementById('mensaje-alerta');
            contenedor.innerHTML = ''; // limpia alertas anteriores
            const alerta = document.createElement('div');
            alerta.style = estiloAlerta;
            alerta.innerHTML = `ℹ️ ${mensaje}`;
            contenedor.appendChild(alerta);

            // Elimina la alerta después de 4 segundos
            setTimeout(() => {
                alerta.remove();
            }, 4000);
        };
    </script>

    <!-- Contenedor para las alertas centradas -->
    <div id="mensaje-alerta" style="
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 9999;
        text-align: center;
    "></div>
</body>

</html>