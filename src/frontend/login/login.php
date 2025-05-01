<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Inicio de sesión</title>
  <link rel="icon" href="./img/icono.png" type="image/png">
  <link rel="stylesheet" href="./css/estudiantes.css">
  <link href="../lib/Bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

  <!-- Carrusel de fondo -->
  <div id="carouselFondo" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="5000">
    <div class="carousel-inner">
      <div class="carousel-item active">
        <img src="./img/slide1.jpg" class="d-block w-100" alt="Fondo 1">
      </div>
      <div class="carousel-item">
        <img src="./img/slide2.jpg" class="d-block w-100" alt="Fondo 2">
      </div>
      <div class="carousel-item">
        <img src="./img/slide3.jpg" class="d-block w-100" alt="Fondo 3">
      </div>
    </div>
  </div>

  <!-- Formulario de inicio de sesión -->
  <div class="login-container">
    <img src="img/logo.png" alt="Logo institucional" class="logo-login">
    <h4>Iniciar Sesión</h4>
    <form action="../../backend/loginValidation/validar_login.php" method="POST" autocomplete="off">
      <label for="usuario">Usuario o Correo:</label>
      <input type="text" name="usuario" id="usuario" required><br><br>

      <label for="clave">Contraseña:</label>
      <input type="password" name="clave" id="clave" required><br><br>
      <p style="text-align: center;">
      ¿No te has registrado?
      <a href="../register/registro.php" style="color: #007bff; text-decoration: none;">Registrate aquí</a>
      </p>
      <input type="submit" value="Entrar">
    </form>

    <button onclick="window.location.href='../inicio/index.php'" class="btn btn-secondary mt-3">
      Volver a la página principal
    </button>
  </div>

  <script src="../lib/Bootstrap/js/bootstrap.bundle.min.js" defer></script>
</body>
</html>
