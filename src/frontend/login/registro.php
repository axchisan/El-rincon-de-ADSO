<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro</title>
  <link rel="icon" href="./img/icono.png" type="image/png">
  <link rel="stylesheet" href="registro.css">
</head>
<body>
<a href="../inicio/index.php" class="btn-inicio" title="Volver al inicio">🏠</a>

<div class="contenedor-registro">

  <!-- FORMULARIO IZQUIERDA -->
  <div class="registro-form">
    <img src="./img/logo.png" alt="Logo institucional" class="logo-login">
    <h2>Registro de Usuario</h2>
    <form action="guardar_registro.php" method="POST">
      <input type="text" name="Nombre" placeholder="Nombre" required><br>
      <input type="text" name="Apellido" placeholder="Apellido" required><br>
      <input type="text" name="identificación" placeholder="Identificación" required><br>
      <input type="email" name="correo" placeholder="Correo electrónico" required><br>
      <input type="text" name="Numero de ficha" placeholder="Número de ficha" required><br>
      <input type="password" name="contraseña" placeholder="Contraseña" required><br>
      <input type="submit" value="Registrarse">
    </form>
  </div>

  <!-- FRASE Y LOGOS DERECHA -->
  <div class="frase-lateral">
    <div class="overlay">
      <h3>"Un lector vive mil vidas antes de morir."</h3>
      <p>— George R. R. Martin</p>
   <!-- aca va imagem-->
    

</div>

</body>
</html>