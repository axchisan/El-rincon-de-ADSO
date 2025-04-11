

<!DOCTYPE html>
<html lang="es">
<head>
<link rel="stylesheet" href="../estudiantes.css">

    <meta charset="UTF-8">
    <title>Registro de Aprendices</title>
</head>
<body>
    <h2>Registro de Aprendiz</h2>
    <?php if (!empty($mensaje)) echo "<p>$mensaje</p>"; ?>

    <form method="POST" action="">
        <label>Nombre completo:</label><br>
        <input type="text" name="nombre" required><br><br>

        <label>Identificación:</label><br>
        <input type="text" name="identificacion" required><br><br>

        <label>Edad:</label><br>
        <input type="number" name="edad" min="1" required><br><br>

        <label>Correo electrónico:</label><br>
        <input type="email" name="correo" required><br><br>

        <label>Número de ficha:</label><br>
        <input type="text" name="ficha" required><br><br>

        <label>Contraseña:</label><br>
        <input type="password" name="clave" required><br><br>

        <button type="submit">Registrarse</button>
    </form>
</body>
</html>
