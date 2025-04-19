<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel de Usuario - El Rincón de ADSO</title>
  <link rel="icon" type="image/png" href="../inicio/img/icono.png">
  <link rel="stylesheet" href="./css/styles-panel.css">
  <!-- Cargar Font Awesome de manera optimizada -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <!-- Navegación -->
  <nav class="navbar">
    <div class="container navbar__container">
      <a href="index.php" class="navbar__logo">
        <i class="fas fa-book-open"></i>
        El Rincón de ADSO
      </a>
      
      <!-- Navegación para escritorio -->
      <ul class="navbar__menu">
        <li class="navbar__menu-item"><a href="index.php">Inicio</a></li>
        <li class="navbar__menu-item"><a href="repositorio.php">Repositorio</a></li>
        <li class="navbar__menu-item"><a href="panel-usuario.php">Panel</a></li>
        <li class="navbar__menu-item navbar__menu-item--button"><a href="../../backend/logout.php">Cerrar sesión</a></li>
      </ul>
      
      <!-- Botón menú móvil -->
      <button id="mobile-menu-button" class="navbar__toggle">
        <i class="fas fa-bars"></i>
      </button>
    </div>
    
    <!-- Menú móvil desplegable -->
    <div id="mobile-menu" class="navbar__mobile container hidden">
      <ul>
        <li class="navbar__mobile-item"><a href="index.php">Inicio</a></li>
        <li class="navbar__mobile-item"><a href="repositorio.php">Repositorio</a></li>
        <li class="navbar__mobile-item navbar__mobile-item--active"><a href="panel-usuario.php">Panel</a></li>
        <li class="navbar__mobile-item"><a href="../../backend/logout.php">Cerrar sesión</a></li>
      </ul>
    </div>
  </nav>

  <!-- Cabecera del Panel -->
  <header class="panel-header">
  <div class="panel-header__bg"></div>
  <div class="container">
    <div class="user-welcome">
      <div class="user-avatar">
        <div class="avatar-decoration"></div>
        <img src="https://i.pravatar.cc/150?img=12" alt="Avatar de usuario" class="avatar-img">
        <div class="user-status online" title="En línea"></div>
      </div>
      <div class="user-info">
        <span class="user-greeting">¡Bienvenido de nuevo!</span>
        <h1>Carlos Gómez</h1>
        <p><i class="fas fa-clock"></i> Última conexión: 18 de abril, 2025</p>
        <div class="user-badges-preview">
          <span class="user-badge" title="Colaborador Activo"><i class="fas fa-award"></i></span>
          <span class="user-badge" title="Experto en Python"><i class="fab fa-python"></i></span>
          <span class="user-badge" title="Mentor"><i class="fas fa-user-graduate"></i></span>
        </div>
      </div>
    </div>
  </div>
</header>

  <!-- Navegación principal de pestañas -->
  <div class="main-tabs">
  <div class="container">
    <div class="main-tabs__wrapper">
      <button class="main-tab active" data-tab="repositorio">
        <div class="main-tab__icon">
          <i class="fas fa-book"></i>
        </div>
        <div class="main-tab__content">
          <span class="main-tab__title">Repositorio</span>
          <span class="main-tab__description">Tus recursos y aportes</span>
        </div>
      </button>
      <button class="main-tab" data-tab="comunidad">
        <div class="main-tab__icon">
          <i class="fas fa-users"></i>
        </div>
        <div class="main-tab__content">
          <span class="main-tab__title">Comunidad</span>
          <span class="main-tab__description">Foros y grupos</span>
        </div>
      </button>
      <button class="main-tab" data-tab="perfil">
        <div class="main-tab__icon">
          <i class="fas fa-user"></i>
        </div>
        <div class="main-tab__content">
          <span class="main-tab__title">Mi Perfil</span>
          <span class="main-tab__description">Configuración personal</span>
        </div>
      </button>
    </div>
  </div>
</div>

  <!-- Contenedor principal -->
  <main class="main-content">
    <div class="container">
      
      <!-- Sección Repositorio -->
      <section id="repositorio" class="tab-section active">
        <!-- Pestañas secundarias de Repositorio -->
        <div class="sub-tabs">
          <button class="sub-tab active" data-subtab="mis-favoritos">Mis Favoritos</button>
          <button class="sub-tab" data-subtab="recientes">Vistos Recientemente</button>
          <button class="sub-tab" data-subtab="guardados">Guardados</button>
          <button class="sub-tab" data-subtab="mis-aportes">Mis Aportes</button>
        </div>
        
        <!-- Contenido de pestañas secundarias de Repositorio -->
        <div class="sub-content">
          <!-- Mis Favoritos -->
          <div id="mis-favoritos" class="sub-panel active">
            <div class="panel-header-secondary">
              <h2>Mis Recursos Favoritos</h2>
              <p>Aquí encontrarás todos los recursos que has marcado como favoritos</p>
            </div>
            
            <div class="resources-grid">
              <!-- Recurso 1 -->
              <div class="resource-card">
                <div class="resource-card__image-container">
                  <img src="https://edit.org/photos/img/blog/t9i-edit-books-online.jpg-840.jpg" alt="Fundamentos de Programación en Python" class="resource-card__image">
                  <div class="resource-card__format">PDF</div>
                </div>
                <div class="resource-card__content">
                  <div class="resource-card__category">Programación</div>
                  <h3 class="resource-card__title">Fundamentos de Programación en Python</h3>
                  <p class="resource-card__author">Por Ana Martínez</p>
                  <div class="resource-card__meta">
                    <span><i class="fas fa-calendar-alt"></i> 15/05/2022</span>
                    <span><i class="fas fa-star"></i> Favorito desde: 10/04/2025</span>
                  </div>
                  <div class="resource-card__actions">
                    <a href="#" class="btn btn--primary"><i class="fas fa-book-reader"></i> Leer ahora</a>
                    <a href="#" class="btn btn--outline"><i class="fas fa-heart-broken"></i> Quitar favorito</a>
                  </div>
                </div>
              </div>
              
              <!-- Recurso 2 -->
              <div class="resource-card">
                <div class="resource-card__image-container">
                  <img src="https://img.youtube.com/vi/w7ejDZ8SWv8/maxresdefault.jpg" alt="Introducción a React.js" class="resource-card__image">
                  <div class="resource-card__duration"><i class="fas fa-clock"></i> 45:20</div>
                  <div class="resource-card__play-button"><i class="fas fa-play"></i></div>
                </div>
                <div class="resource-card__content">
                  <div class="resource-card__category">Desarrollo web</div>
                  <h3 class="resource-card__title">Introducción a React.js</h3>
                  <p class="resource-card__author">Por Miguel Torres</p>
                  <div class="resource-card__meta">
                    <span><i class="fas fa-calendar-alt"></i> 18/02/2023</span>
                    <span><i class="fas fa-star"></i> Favorito desde: 15/04/2025</span>
                  </div>
                  <div class="resource-card__actions">
                    <a href="#" class="btn btn--primary"><i class="fas fa-play-circle"></i> Ver video</a>
                    <a href="#" class="btn btn--outline"><i class="fas fa-heart-broken"></i> Quitar favorito</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Vistos Recientemente -->
          <div id="recientes" class="sub-panel">
            <div class="panel-header-secondary">
              <h2>Vistos Recientemente</h2>
              <p>Recursos que has consultado en los últimos días</p>
            </div>
            
            <div class="resources-grid">
              <!-- Recurso 1 -->
              <div class="resource-card">
                <div class="resource-card__image-container">
                  <img src="https://marketplace.canva.com/EAFauhQZ3zI/1/0/1003w/canva-portada-de-libro-de-misterio-y-thriller-moderno-negro-y-blanco-Md-gBXPxLQU.jpg" alt="Arquitectura de Software Moderna" class="resource-card__image">
                  <div class="resource-card__format">PDF</div>
                </div>
                <div class="resource-card__content">
                  <div class="resource-card__category">Programación</div>
                  <h3 class="resource-card__title">Arquitectura de Software Moderna</h3>
                  <p class="resource-card__author">Por Roberto Sánchez</p>
                  <div class="resource-card__meta">
                    <span><i class="fas fa-calendar-alt"></i> 20/11/2021</span>
                    <span><i class="fas fa-clock"></i> Visto hace 2 horas</span>
                  </div>
                  <div class="resource-card__actions">
                    <a href="#" class="btn btn--primary"><i class="fas fa-book-reader"></i> Continuar leyendo</a>
                    <a href="#" class="btn btn--outline"><i class="fas fa-heart"></i> Añadir a favoritos</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Guardados -->
          <div id="guardados" class="sub-panel">
            <div class="panel-header-secondary">
              <h2>Recursos Guardados</h2>
              <p>Recursos que has guardado para consultar más tarde</p>
            </div>
            
            <div class="empty-state">
              <div class="empty-state__icon">
                <i class="fas fa-bookmark"></i>
              </div>
              <h3>No tienes recursos guardados</h3>
              <p>Explora el repositorio y guarda recursos para verlos más tarde</p>
              <a href="repositorio.php" class="btn btn--primary">Explorar repositorio</a>
            </div>
          </div>
          
          <!-- Mis Aportes -->
          <div id="mis-aportes" class="sub-panel">
            <div class="panel-header-secondary">
              <h2>Mis Aportes al Repositorio</h2>
              <p>Recursos que has compartido con la comunidad</p>
            </div>
            
            <div class="action-bar">
              <a href="#" class="btn btn--primary"><i class="fas fa-plus"></i> Nuevo aporte</a>
            </div>
            
            <div class="resources-grid">
              <!-- Recurso 1 -->
              <div class="resource-card">
                <div class="resource-card__image-container">
                  <img src="https://marketplace.canva.com/EAFaB3zD5GQ/1/0/1003w/canva-portada-de-libro-de-ciencia-ficci%C3%B3n-moderno-azul-y-morado-Xpwm-Bf-Lfw.jpg" alt="SQL Avanzado para Análisis de Datos" class="resource-card__image">
                  <div class="resource-card__format">PDF</div>
                </div>
                <div class="resource-card__content">
                  <div class="resource-card__category">Bases de datos</div>
                  <h3 class="resource-card__title">SQL Avanzado para Análisis de Datos</h3>
                  <p class="resource-card__author">Por Ti</p>
                  <div class="resource-card__meta">
                    <span><i class="fas fa-calendar-alt"></i> 05/04/2025</span>
                    <span><i class="fas fa-download"></i> 24 descargas</span>
                  </div>
                  <div class="resource-card__actions">
                    <a href="#" class="btn btn--primary"><i class="fas fa-edit"></i> Editar</a>
                    <a href="#" class="btn btn--outline"><i class="fas fa-chart-bar"></i> Estadísticas</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      
      <!-- Sección Comunidad -->
      <section id="comunidad" class="tab-section">
        <!-- Pestañas secundarias de Comunidad -->
        <div class="sub-tabs">
          <button class="sub-tab active" data-subtab="foro">Foro</button>
          <button class="sub-tab" data-subtab="grupos">Mis Grupos</button>
          <button class="sub-tab" data-subtab="eventos">Eventos</button>
          <button class="sub-tab" data-subtab="mensajes">Mensajes</button>
        </div>
        
        <!-- Contenido de pestañas secundarias de Comunidad -->
        <div class="sub-content">
          <!-- Foro -->
          <div id="foro" class="sub-panel active">
            <div class="panel-header-secondary">
              <h2>Foro de Discusión</h2>
              <p>Participa en conversaciones sobre temas de interés</p>
            </div>
            
            <div class="action-bar">
              <a href="#" class="btn btn--primary"><i class="fas fa-plus"></i> Nuevo tema</a>
              <div class="search-mini">
                <input type="text" placeholder="Buscar en el foro...">
                <button><i class="fas fa-search"></i></button>
              </div>
            </div>
            
            <div class="forum-topics">
              <div class="forum-topic">
                <div class="forum-topic__icon">
                  <i class="fas fa-fire"></i>
                </div>
                <div class="forum-topic__content">
                  <h3 class="forum-topic__title">¿Cuál es el mejor framework para desarrollo web en 2025?</h3>
                  <div class="forum-topic__meta">
                    <span><i class="fas fa-user"></i> Iniciado por: Laura Pérez</span>
                    <span><i class="fas fa-calendar-alt"></i> 15/04/2025</span>
                    <span><i class="fas fa-comments"></i> 24 respuestas</span>
                  </div>
                  <div class="forum-topic__tags">
                    <span class="tag">Desarrollo web</span>
                    <span class="tag">Frameworks</span>
                    <span class="tag">Debate</span>
                  </div>
                </div>
                <div class="forum-topic__activity">
                  <div class="activity-indicator high"></div>
                  <span>Activo</span>
                </div>
              </div>
              
              <div class="forum-topic">
                <div class="forum-topic__icon">
                  <i class="fas fa-book"></i>
                </div>
                <div class="forum-topic__content">
                  <h3 class="forum-topic__title">Recursos recomendados para aprender Inteligencia Artificial</h3>
                  <div class="forum-topic__meta">
                    <span><i class="fas fa-user"></i> Iniciado por: Javier López</span>
                    <span><i class="fas fa-calendar-alt"></i> 10/04/2025</span>
                    <span><i class="fas fa-comments"></i> 18 respuestas</span>
                  </div>
                  <div class="forum-topic__tags">
                    <span class="tag">IA</span>
                    <span class="tag">Aprendizaje</span>
                    <span class="tag">Recursos</span>
                  </div>
                </div>
                <div class="forum-topic__activity">
                  <div class="activity-indicator medium"></div>
                  <span>Moderado</span>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Grupos -->
          <div id="grupos" class="sub-panel">
            <div class="panel-header-secondary">
              <h2>Mis Grupos</h2>
              <p>Grupos de estudio y colaboración a los que perteneces</p>
            </div>
            
            <div class="action-bar">
              <a href="#" class="btn btn--primary"><i class="fas fa-plus"></i> Crear grupo</a>
              <a href="#" class="btn btn--outline"><i class="fas fa-search"></i> Explorar grupos</a>
            </div>
            
            <div class="groups-grid">
              <div class="group-card">
                <div class="group-card__header">
                  <img src="https://img.freepik.com/free-vector/gradient-network-connection-background_23-2148865392.jpg" alt="Desarrolladores Full Stack" class="group-card__image">
                </div>
                <div class="group-card__content">
                  <h3 class="group-card__title">Desarrolladores Full Stack</h3>
                  <p class="group-card__description">Grupo para compartir conocimientos y resolver dudas sobre desarrollo web full stack.</p>
                  <div class="group-card__meta">
                    <span><i class="fas fa-users"></i> 28 miembros</span>
                    <span><i class="fas fa-calendar-alt"></i> Creado: 01/03/2025</span>
                  </div>
                  <div class="group-card__actions">
                    <a href="#" class="btn btn--primary"><i class="fas fa-sign-in-alt"></i> Entrar</a>
                  </div>
                </div>
              </div>
              
              <div class="group-card">
                <div class="group-card__header">
                  <img src="https://img.freepik.com/free-vector/gradient-technology-background_23-2149171134.jpg" alt="Ciencia de Datos" class="group-card__image">
                </div>
                <div class="group-card__content">
                  <h3 class="group-card__title">Ciencia de Datos</h3>
                  <p class="group-card__description">Comunidad enfocada en análisis de datos, machine learning y visualización.</p>
                  <div class="group-card__meta">
                    <span><i class="fas fa-users"></i> 42 miembros</span>
                    <span><i class="fas fa-calendar-alt"></i> Creado: 15/02/2025</span>
                  </div>
                  <div class="group-card__actions">
                    <a href="#" class="btn btn--primary"><i class="fas fa-sign-in-alt"></i> Entrar</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Eventos -->
          <div id="eventos" class="sub-panel">
            <div class="panel-header-secondary">
              <h2>Eventos</h2>
              <p>Próximos eventos y webinars de la comunidad</p>
            </div>
            
            <div class="events-list">
              <div class="event-card">
                <div class="event-card__date">
                  <div class="event-date">
                    <span class="event-date__day">25</span>
                    <span class="event-date__month">ABR</span>
                  </div>
                </div>
                <div class="event-card__content">
                  <h3 class="event-card__title">Webinar: Tendencias en Desarrollo Web 2025</h3>
                  <p class="event-card__description">Descubre las últimas tendencias y tecnologías que están definiendo el desarrollo web este año.</p>
                  <div class="event-card__meta">
                    <span><i class="fas fa-clock"></i> 18:00 - 19:30</span>
                    <span><i class="fas fa-video"></i> Online</span>
                    <span><i class="fas fa-user"></i> Ponente: María Rodríguez</span>
                  </div>
                  <div class="event-card__actions">
                    <a href="#" class="btn btn--primary"><i class="fas fa-calendar-plus"></i> Inscribirme</a>
                    <a href="#" class="btn btn--outline"><i class="fas fa-info-circle"></i> Más información</a>
                  </div>
                </div>
              </div>
              
              <div class="event-card">
                <div class="event-card__date">
                  <div class="event-date">
                    <span class="event-date__day">03</span>
                    <span class="event-date__month">MAY</span>
                  </div>
                </div>
                <div class="event-card__content">
                  <h3 class="event-card__title">Taller: Introducción a Docker y Kubernetes</h3>
                  <p class="event-card__description">Taller práctico para aprender a utilizar contenedores y orquestarlos con Kubernetes.</p>
                  <div class="event-card__meta">
                    <span><i class="fas fa-clock"></i> 10:00 - 14:00</span>
                    <span><i class="fas fa-map-marker-alt"></i> Campus Central</span>
                    <span><i class="fas fa-user"></i> Instructor: Carlos Vega</span>
                  </div>
                  <div class="event-card__actions">
                    <a href="#" class="btn btn--primary"><i class="fas fa-calendar-plus"></i> Inscribirme</a>
                    <a href="#" class="btn btn--outline"><i class="fas fa-info-circle"></i> Más información</a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Mensajes -->
          <div id="mensajes" class="sub-panel">
            <div class="panel-header-secondary">
              <h2>Mensajes</h2>
              <p>Tus conversaciones con otros miembros de la comunidad</p>
            </div>
            
            <div class="messages-container">
              <div class="messages-sidebar">
                <div class="messages-search">
                  <input type="text" placeholder="Buscar conversaciones...">
                </div>
                <div class="conversation-list">
                  <div class="conversation-item active">
                    <div class="conversation-item__avatar">
                      <img src="https://i.pravatar.cc/150?img=32" alt="Laura Pérez">
                      <span class="status-indicator online"></span>
                    </div>
                    <div class="conversation-item__content">
                      <h4>Laura Pérez</h4>
                      <p>Gracias por compartir ese recurso...</p>
                    </div>
                    <div class="conversation-item__meta">
                      <span class="time">12:45</span>
                      <span class="unread">2</span>
                    </div>
                  </div>
                  
                  <div class="conversation-item">
                    <div class="conversation-item__avatar">
                      <img src="https://i.pravatar.cc/150?img=68" alt="Miguel Torres">
                      <span class="status-indicator offline"></span>
                    </div>
                    <div class="conversation-item__content">
                      <h4>Miguel Torres</h4>
                      <p>¿Viste el nuevo curso de React?</p>
                    </div>
                    <div class="conversation-item__meta">
                      <span class="time">Ayer</span>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="messages-main">
                <div class="messages-header">
                  <div class="contact-info">
                    <img src="https://i.pravatar.cc/150?img=32" alt="Laura Pérez" class="contact-avatar">
                    <div>
                      <h3>Laura Pérez</h3>
                      <span class="status">En línea</span>
                    </div>
                  </div>
                  <div class="messages-actions">
                    <button class="icon-button"><i class="fas fa-phone"></i></button>
                    <button class="icon-button"><i class="fas fa-video"></i></button>
                    <button class="icon-button"><i class="fas fa-info-circle"></i></button>
                  </div>
                </div>
                
                <div class="messages-body">
                  <div class="message-date">
                    <span>Hoy</span>
                  </div>
                  
                  <div class="message received">
                    <div class="message__avatar">
                      <img src="https://i.pravatar.cc/150?img=32" alt="Laura Pérez">
                    </div>
                    <div class="message__content">
                      <p>Hola Carlos, ¿cómo estás? Vi que compartiste un recurso sobre SQL avanzado.</p>
                      <span class="message__time">11:30</span>
                    </div>
                  </div>
                  
                  <div class="message sent">
                    <div class="message__content">
                      <p>¡Hola Laura! Sí, es un material que preparé para el grupo de bases de datos.</p>
                      <span class="message__time">11:45</span>
                    </div>
                  </div>
                  
                  <div class="message received">
                    <div class="message__avatar">
                      <img src="https://i.pravatar.cc/150?img=32" alt="Laura Pérez">
                    </div>
                    <div class="message__content">
                      <p>Está muy completo, gracias por compartir ese recurso. Me ha ayudado mucho con mi proyecto.</p>
                      <span class="message__time">12:45</span>
                    </div>
                  </div>
                </div>
                
                <div class="messages-footer">
                  <button class="icon-button"><i class="fas fa-paperclip"></i></button>
                  <input type="text" placeholder="Escribe un mensaje...">
                  <button class="send-button"><i class="fas fa-paper-plane"></i></button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      
      <!-- Sección Mi Perfil -->
      <section id="perfil" class="tab-section">
        <!-- Pestañas secundarias de Mi Perfil -->
        <div class="sub-tabs">
          <button class="sub-tab active" data-subtab="datos-personales">Datos Personales</button>
          <button class="sub-tab" data-subtab="seguridad">Seguridad</button>
          <button class="sub-tab" data-subtab="notificaciones">Notificaciones</button>
        </div>
        
        <!-- Contenido de pestañas secundarias de Mi Perfil -->
        <div class="sub-content">
          <!-- Datos Personales -->
          <div id="datos-personales" class="sub-panel active">
            <div class="panel-header-secondary">
              <h2>Datos Personales</h2>
              <p>Gestiona tu información personal</p>
            </div>
            <div class="profile-container">
              <div class="profile-sidebar">
                <div class="profile-avatar">
                  <img src="https://i.pravatar.cc/150?img=12" alt="Tu avatar">
                  <button class="change-avatar-btn"><i class="fas fa-camera"></i></button>
                </div>
                <div class="profile-badges">
                  <h4>Insignias</h4>
                  <div class="badges-container">
                    <div class="badge" title="Colaborador Activo">
                      <i class="fas fa-award"></i>
                    </div>
                    <div class="badge" title="Experto en Python">
                      <i class="fab fa-python"></i>
                    </div>
                    <div class="badge" title="Mentor">
                      <i class="fas fa-user-graduate"></i>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="profile-main">
                <form class="profile-form">
                  <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" value="Carlos Gómez">
                  </div>
                  <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" id="email" name="email" value="carlos.gomez@ejemplo.com">
                  </div>
                  <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono" value="+57 300 123 4567">
                  </div>
                  <div class="form-group">
                    <label for="profesion">Profesión</label>
                    <input type="text" id="profesion" name="profesion" value="Desarrollador Web">
                  </div>
                  <div class="form-group">
                    <label for="bio">Biografía</label>
                    <textarea id="bio" name="bio" rows="4">Desarrollador web con experiencia en tecnologías frontend y backend. Apasionado por compartir conocimientos y aprender constantemente.</textarea>
                  </div>
                  <div class="form-actions">
                    <button type="submit" class="btn btn--primary">Guardar cambios</button>
                    <button type="reset" class="btn btn--outline">Cancelar</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <!-- Seguridad -->
          <div id="seguridad" class="sub-panel">
            <div class="panel-header-secondary">
              <h2>Seguridad</h2>
              <p>Gestiona la seguridad de tu cuenta</p>
            </div>
            
            <div class="security-container">
              <div class="form-section">
                <h3>Cambiar contraseña</h3>
                <form class="security-form">
                  <div class="form-group">
                    <label for="current-password">Contraseña actual</label>
                    <input type="password" id="current-password" name="current-password">
                  </div>
                  <div class="form-group">
                    <label for="new-password">Nueva contraseña</label>
                    <input type="password" id="new-password" name="new-password">
                    <div class="password-strength">
                      <div class="strength-meter">
                        <div class="strength-meter-fill" style="width: 70%;"></div>
                      </div>
                      <span>Seguridad: Buena</span>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="confirm-password">Confirmar nueva contraseña</label>
                    <input type="password" id="confirm-password" name="confirm-password">
                  </div>
                  <div class="form-actions">
                    <button type="submit" class="btn btn--primary">Cambiar contraseña</button>
                  </div>
                </form>
              </div>
              
          <!-- Notificaciones -->
          <div id="notificaciones" class="sub-panel">
            <div class="panel-header-secondary">
              <h2>Notificaciones</h2>
              <p>Configura cómo quieres recibir notificaciones</p>
            </div>
            
            <div class="notifications-container">
              <form class="notifications-form">
                <div class="form-section">
                  <h3>Notificaciones por correo electrónico</h3>
                  <div class="notification-option">
                    <div>
                      <h4>Nuevos recursos</h4>
                      <p>Recibe notificaciones cuando se añadan nuevos recursos en categorías de tu interés</p>
                    </div>
                    <label class="switch">
                      <input type="checkbox" checked>
                      <span class="slider"></span>
                    </label>
                  </div>
                  
                  <div class="notification-option">
                    <div>
                      <h4>Comentarios en tus aportes</h4>
                      <p>Recibe notificaciones cuando alguien comente en tus aportes</p>
                    </div>
                    <label class="switch">
                      <input type="checkbox" checked>
                      <span class="slider"></span>
                    </label>
                  </div>
                  
                  <div class="notification-option">
                    <div>
                      <h4>Mensajes directos</h4>
                      <p>Recibe notificaciones cuando alguien te envíe un mensaje directo</p>
                    </div>
                    <label class="switch">
                      <input type="checkbox" checked>
                      <span class="slider"></span>
                    </label>
                  </div>
                  
                  <div class="notification-option">
                    <div>
                      <h4>Eventos</h4>
                      <p>Recibe notificaciones sobre próximos eventos y webinars</p>
                    </div>
                    <label class="switch">
                      <input type="checkbox">
                      <span class="slider"></span>
                    </label>
                  </div>
                </div>
                
                <div class="form-section">
                  <h3>Notificaciones en la plataforma</h3>
                  <div class="notification-option">
                    <div>
                      <h4>Mostrar notificaciones en tiempo real</h4>
                      <p>Recibe notificaciones mientras estás usando la plataforma</p>
                    </div>
                    <label class="switch">
                      <input type="checkbox" checked>
                      <span class="slider"></span>
                    </label>
                  </div>
                  
                  <div class="notification-option">
                    <div>
                      <h4>Sonidos de notificación</h4>
                      <p>Reproducir sonidos cuando recibas notificaciones</p>
                    </div>
                    <label class="switch">
                      <input type="checkbox">
                      <span class="slider"></span>
                    </label>
                  </div>
                </div>
                
                <div class="form-actions">
                  <button type="submit" class="btn btn--primary">Guardar configuración</button>
                  <button type="reset" class="btn btn--outline">Restaurar valores predeterminados</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </section>
    </div>
  </main>
  <!-- Scripts -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Menú movil
      const mobileMenuButton = document.getElementById('mobile-menu-button');
      const mobileMenu = document.getElementById('mobile-menu');
      
      if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
          mobileMenu.classList.toggle('hidden');
        });
      }
      
      // Funcionalidad de las pestañas principales
      const mainTabs = document.querySelectorAll('.main-tab');
      const tabSections = document.querySelectorAll('.tab-section');
      
      mainTabs.forEach(tab => {
        tab.addEventListener('click', function() {
          // Eliminar la clase activa de todas las pestañas
          mainTabs.forEach(t => t.classList.remove('active'));
          
          // Añadir clase activa a la pestaña seleccionada
          this.classList.add('active');
          
          // Ocultar la sección de todas las pestañas
          tabSections.forEach(section => {
            section.classList.remove('active');
          });
          
          // Mostrar la pestaña seleccionada
          const tabId = this.getAttribute('data-tab');
          document.getElementById(tabId).classList.add('active');
        });
      });
      
      // Funcionalidad de las subpestañas
      const subTabs = document.querySelectorAll('.sub-tab');
      
      subTabs.forEach(tab => {
        tab.addEventListener('click', function() {
          // Obtener sección principal
          const parentSection = this.closest('.tab-section');
          
          // Eliminar la clase activa de todas las pastañas de esta sección
          parentSection.querySelectorAll('.sub-tab').forEach(t => {
            t.classList.remove('active');
          });
          
          // Añadir clase activa a la pestaña seleccionada
          this.classList.add('active');
          
          // Ocultar todos los subpaneles de esta sección
          parentSection.querySelectorAll('.sub-panel').forEach(panel => {
            panel.classList.remove('active');
          });
          
          //Mostrar el subpanel seleccionado
          const subTabId = this.getAttribute('data-subtab');
          parentSection.querySelector('#' + subTabId).classList.add('active');
        });
      });
    });
  </script>
</body>
</html>

