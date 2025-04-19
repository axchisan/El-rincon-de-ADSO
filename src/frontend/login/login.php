<!DOCTYPE html>
<html>
<head>
  <title>Inicio de sesión</title>
  <link rel="icon" href="./img/icono.png" type="image/png">
  <link rel="stylesheet" href="./css/estudiantes.css">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap JS (para que el carrusel funcione) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
</head>
<body>
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

  <div class="login-container">
    <img src="img/logo.png" alt="Logo institucional" class="logo-login"> 
    <h4>Iniciar Sesión</h4>
    <?php
    session_start();
    if (isset($_SESSION['error'])) {
        echo '<p style="color: red;">' . htmlspecialchars($_SESSION['error']) . '</p>';
        unset($_SESSION['error']); 
    }
    ?>
    <form action="../../backend/loginValidation/validar_login.php" method="POST">
      <label>Usuario o Correo:</label>
      <input type="text" name="usuario" required><br><br>
      <label>Contraseña:</label>
      <input type="password" name="clave" required><br><br>
      <input type="submit" value="Entrar">
    </form>
    <button onclick="window.location.href='../inicio/index.php'">
      Volver a la página principal
    </button>
  </div>
</body>
</html>