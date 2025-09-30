# 📚 El Rincón de ADSO

<div align="center">

![El Rincón de ADSO](https://github.com/user-attachments/assets/bde3a473-09b7-4797-9282-74271dea1c3e)



**Tu biblioteca digital al alcance de todos**

[![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-316192?style=for-the-badge&logo=postgresql&logoColor=white)](https://www.postgresql.org/)
[![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
[![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://www.docker.com/)

[Características](#-características-principales) • [Instalación](#-instalación) • [Uso](#-uso) • [Tecnologías](#-tecnologías) • [Contribuir](#-contribuir)

</div>

---

## 🎯 Descripción

**El Rincón de ADSO** es una plataforma web innovadora diseñada para centralizar recursos educativos relacionados con la programación y la informática. Desarrollada por un equipo de nueve aprendices del programa de **Análisis y Desarrollo de Software (ADSO)** del SENA, esta plataforma busca democratizar el acceso al conocimiento técnico de alta calidad.

### 🌟 Propósito

Crear un espacio digital colaborativo donde estudiantes, aprendices y entusiastas del desarrollo de software puedan:
- Acceder a materiales educativos de calidad
- Compartir conocimientos y recursos
- Interactuar en una comunidad de aprendizaje
- Fortalecer competencias técnicas en un campo de alta demanda laboral

---

## ✨ Características Principales

### 📖 Gestión de Recursos
- **Múltiples formatos**: Libros, videos, documentos, imágenes
- **Sistema de categorías**: Organización intuitiva por temas
- **Etiquetas personalizadas**: Búsqueda y clasificación avanzada
- **Portadas personalizadas**: Visualización atractiva de recursos
- **Control de visibilidad**: Recursos públicos, privados o por grupos

### 👥 Comunidad Interactiva
- **Sistema de comentarios**: Opina sobre recursos y lecturas
- **Respuestas anidadas**: Conversaciones organizadas
- **Sistema de "Me gusta"**: Valora las contribuciones de otros
- **Calificaciones con estrellas**: Evalúa la calidad de los recursos
- **Foros de discusión**: Espacios para debates técnicos
- **Sistema de eventos**: Organiza y participa en webinars y talleres

### 🔐 Gestión de Usuarios
- **Autenticación segura**: Sistema de registro y login robusto
- **Perfiles personalizables**: Avatar, biografía, profesión
- **Panel de usuario completo**: Gestiona tus recursos y favoritos
- **Sistema de notificaciones**: Mantente al día con la actividad
- **Historial de actividad**: Recursos vistos recientemente
- **Favoritos y guardados**: Organiza tu biblioteca personal

### 🔍 Búsqueda Avanzada
- **Filtros múltiples**: Por tipo, categoría, idioma, relevancia
- **Búsqueda en tiempo real**: Resultados instantáneos
- **Ordenamiento flexible**: Por fecha, relevancia, popularidad
- **Paginación inteligente**: Navegación eficiente de resultados

### 📊 Características Técnicas
- **Arquitectura MVC**: Código organizado y mantenible
- **API RESTful**: Endpoints bien estructurados
- **Responsive Design**: Funciona en todos los dispositivos
- **Optimización de imágenes**: Carga rápida de contenido
- **Lazy loading**: Mejora el rendimiento
- **Validación de formularios**: Seguridad en el frontend y backend

---

## 🛠 Tecnologías

### Backend
- **PHP 8.x**: Lenguaje principal del servidor
- **PostgreSQL**: Base de datos relacional robusta
- **PDO**: Capa de abstracción de base de datos segura

### Frontend
- **HTML5**: Estructura semántica
- **CSS3**: Estilos modernos y responsivos
- **JavaScript (ES6+)**: Interactividad y dinamismo
- **Font Awesome**: Iconografía profesional
- **AOS (Animate On Scroll)**: Animaciones suaves

### DevOps
- **Docker**: Contenedorización de la aplicación
- **Docker Compose**: Orquestación de servicios
- **Apache**: Servidor web
- **Git**: Control de versiones

---

## 📦 Instalación

### Prerrequisitos
- Docker y Docker Compose instalados
- Git instalado
- Puerto 89 disponible (o modificar en `docker-compose.yml`)

### Pasos de Instalación

1. **Clonar el repositorio**
\`\`\`bash
git clone https://github.com/axchisan/El-rincon-de-ADSO.git
cd el-rincon-de-adso
\`\`\`

2. **Configurar variables de entorno**
Crea un archivo `.env` en la raíz del proyecto:
\`\`\`env
PGHOST=tu_host_postgresql
PGPORT=5432
PGDATABASE=nombre_base_datos
PGUSER=usuario_postgresql
PGPASSWORD=contraseña_postgresql
\`\`\`

3. **Construir y levantar los contenedores**
\`\`\`bash
docker-compose up -d --build
\`\`\`

4. **Acceder a la aplicación**
Abre tu navegador en: `http://localhost:89`

### Instalación Manual (sin Docker)

1. **Configurar servidor web** (Apache/Nginx)
2. **Instalar PHP 8.x** con extensiones: `pdo`, `pdo_pgsql`, `mbstring`, `gd`
3. **Configurar PostgreSQL**
4. **Actualizar** `src/database/configuracion.php` con tus credenciales
5. **Importar esquema de base de datos** (si está disponible)

---

## 🚀 Uso

### Para Usuarios

1. **Registro**: Crea una cuenta con tu correo electrónico
2. **Explorar**: Navega por el repositorio de recursos
3. **Buscar**: Utiliza filtros para encontrar contenido específico
4. **Guardar**: Marca recursos como favoritos o guárdalos para después
5. **Comentar**: Participa en la comunidad compartiendo opiniones
6. **Contribuir**: Sube tus propios recursos educativos

### Para Administradores

1. **Panel de usuario**: Accede a `panel-usuario.php`
2. **Gestión de recursos**: Edita o elimina recursos
3. **Moderación**: Revisa comentarios y contenido
4. **Estadísticas**: Monitorea la actividad de la plataforma

---

## 📂 Estructura del Proyecto

\`\`\`
El_Rincon_de_ADSO/
├── src/
│   ├── backend/
│   │   ├── api/                    # APIs REST
│   │   │   ├── eventos.php
│   │   │   ├── foro.php
│   │   │   ├── mensajes.php
│   │   │   └── notificaciones.php
│   │   ├── comunidad/              # Gestión de comunidad
│   │   │   ├── add_comment.php
│   │   │   ├── add_reply.php
│   │   │   ├── toggle_like.php
│   │   │   └── ...
│   │   ├── gestionRecursos/        # CRUD de recursos
│   │   │   ├── upload_resource.php
│   │   │   ├── search_resources.php
│   │   │   ├── get_user_favorites.php
│   │   │   └── ...
│   │   ├── loginValidation/        # Autenticación
│   │   ├── perfil/                 # Gestión de perfiles
│   │   └── register/               # Registro de usuarios
│   ├── database/
│   │   ├── conexionDB.php          # Conexión a PostgreSQL
│   │   └── configuracion.php       # Configuración de BD
│   └── frontend/
│       ├── inicio/                 # Página principal
│       ├── repositorio/            # Exploración de recursos
│       ├── panel/                  # Panel de usuario
│       ├── login/                  # Inicio de sesión
│       ├── register/               # Registro
│       ├── foro/                   # Foros de discusión
│       ├── eventos/                # Gestión de eventos
│       ├── mensajes/               # Sistema de mensajería
│       ├── notificaciones/         # Centro de notificaciones
│       └── friends/                # Sistema de amigos
├── docs/                           # Documentación del proyecto
├── docker-compose.yml              # Configuración Docker
├── Dockerfile                      # Imagen Docker
└── README.md                       # Este archivo
\`\`\`

---

## 🎨 Capturas de Pantalla

### Página Principal
<img width="1857" height="932" alt="image" src="https://github.com/user-attachments/assets/e001ccce-34d7-4050-aec9-abb65f103b86" />


### Repositorio
<img width="1857" height="932" alt="image" src="https://github.com/user-attachments/assets/dfbedfe3-6018-46bb-bfaa-4ebe29cb642b" />


---

## 🤝 Contribuir

¡Las contribuciones son bienvenidas! Si deseas mejorar el proyecto:

1. **Fork** el repositorio
2. Crea una **rama** para tu feature (`git checkout -b feature/AmazingFeature`)
3. **Commit** tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. **Push** a la rama (`git push origin feature/AmazingFeature`)
5. Abre un **Pull Request**

### Guías de Contribución

- Sigue las convenciones de código existentes
- Documenta nuevas funcionalidades
- Prueba tu código antes de hacer commit
- Escribe mensajes de commit descriptivos

---

## 📋 Roadmap

- [ ] Sistema de mensajería en tiempo real
- [ ] Integración con APIs de terceros (YouTube, Google Drive)
- [ ] Sistema de insignias y gamificación
- [ ] Modo oscuro
- [ ] Aplicación móvil (PWA)
- [ ] Sistema de recomendaciones con IA
- [ ] Exportación de recursos a PDF
- [ ] Integración con sistemas LMS

---

## 🐛 Reporte de Bugs

Si encuentras un bug, por favor comentalo en esta pagina.
- Descripción detallada del problema
- Pasos para reproducirlo
- Comportamiento esperado vs. actual
- Capturas de pantalla (si aplica)
- Información del navegador/sistema operativo

---

## 📄 Licencia

Este proyecto fue desarrollado como parte del programa ADSO del SENA. 

---

## 👥 Equipo de Desarrollo

Desarrollado con ❤️ por el equipo de aprendices ADSO del SENA:

- **Duvan Arciniegas** - Lider y desarrollo general del proyecto
- **Lenis García** - Documentacion e indagacion de recursos
- **Daniela Gomez** - Desarrollo frontend
- **Gerardo Ardila** - Desarrollo backend
- **Jhony Saavedra** - Desarrollo y testing
- **Daniela Pardo** - Desarrollo backend
- **Nelson Arias** - Desarrollo frontend
- **Disler Celeny** - Desarrollo frontend
- **Julian Jaramillo** -Gestion de recursos

---

## 📞 Contacto

¿Tienes preguntas o sugerencias? 

- **Email**: axchisan923@gmail.com
- **GitHub Issues**: [Reportar problema](https://github.com/axchisan/El-rincon-de-ADSO/issues)

---

## 🙏 Agradecimientos

- **SENA** - Por el programa de formación ADSO
- **Instructores** - Por su guía y apoyo
- **Comunidad Open Source** - Por las herramientas y librerías utilizadas
- **Font Awesome** - Por los iconos
- **Todos los contribuidores** - Por hacer este proyecto posible

---

<div align="center">

**⭐ Si este proyecto te fue útil, considera darle una estrella ⭐**

Hecho con 💻 y ☕ por el equipo ADSO

</div>
