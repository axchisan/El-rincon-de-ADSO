<!DOCTYPE html>
<html>
<head>
  <title>Registro</title>
  <link rel="icon" href="./img/logo.png" type="image/png">
  <link rel="stylesheet" href="registro.css">
</head>
<body>

<div class="register-container">
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

</body>
</html>
