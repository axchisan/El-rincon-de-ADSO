<?php
session_start(); // Move session_start to the top before any output
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <title>Inicio de sesión</title>
  <link rel="icon" href="./img/icono.png" type="image/png">
  <link rel="stylesheet" href="./css/login.css">
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
    <?php
    if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])) {
      echo '<div class="error-message"><ul>';
      foreach ($_SESSION['errors'] as $error) {
        echo '<li>' . htmlspecialchars($error) . '</li>';
      }
      echo '</ul></div>';
      unset($_SESSION['errors']);
    }
    ?>
    <form action="../../backend/loginValidation/validar_login.php" method="POST" autocomplete="off">
      <label for="usuario">Usuario o Correo:</label>
      <input type="text" name="usuario" id="usuario" required class="<?php echo (isset($_SESSION['field_error']) && $_SESSION['field_error'] === 'usuario') ? 'input-error' : ''; ?>"><br><br>

      <label for="clave">Contraseña:</label>
      <input type="password" name="clave" id="clave" required class="<?php echo (isset($_SESSION['field_error']) && $_SESSION['field_error'] === 'clave') ? 'input-error' : ''; ?>"><br><br>
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