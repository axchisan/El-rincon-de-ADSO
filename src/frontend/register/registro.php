<?php require_once "../../database/configuracion.php"; ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro | El Rincón ADSO</title>
    <link rel="icon" href="./img/icono.png" type="image/png">
    <link rel="stylesheet" href="./css/registro.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        .password-container {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <a href="../inicio/index.php" class="btn-inicio" title="Volver al inicio">🏠</a>
    
    <div class="contenedor-registro">
        <div class="registro-form">
            <img src="../login/img/logo.png" alt="Logo institucional" class="logo-login">
            <form action="../../backend/register/register.php" method="POST" id="registro-form">
                <input type="text" name="nombre" placeholder="Nombre completo" required>
                <input type="text" name="nombre_usuario" placeholder="Nombre de usuario" required>
                <input type="text" name="documento" placeholder="Número de documento" required>
                <input type="number" name="edad" placeholder="Edad" min="0" required>
                <input type="email" name="correo" placeholder="Correo electrónico" required>
                <input type="text" name="ficha" placeholder="Número de ficha" required>

                <div class="password-container">
                    <input type="password" name="clave" id="clave" placeholder="Contraseña" required
                        pattern="^(?=(?:.*\d){3,})(?=.*[A-Z])(?=.*[!@#$%^&*()_+{}\[\]:;<>,.?~\\/-]).{6,}$"
                        title="Debe tener al menos una letra mayúscula, tres números y un carácter especial. Mínimo 6 caracteres.">
                    <i class="bi bi-eye toggle-password" id="togglePassword" onclick="togglePassword()"></i>
                </div>

                <input type="submit" value="Registrarse"><br><br>
                <p style="text-align: center;">
                ¿Ya tienes una cuenta?
                <a href="../login/login.php" style="color: #007bff; text-decoration: none;">Inicia sesión aquí</a>
                </p>
            </form>
        </div>

        <div class="frase-lateral">
            <div class="overlay"></div>
            <h3>Un lector vive mil vidas antes de morir...</h3>
            <p>— George R.R. Martin</p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('clave');
            const toggleIcon = document.getElementById('togglePassword');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }

        document.getElementById('registro-form').addEventListener('submit', async function(event) {
            event.preventDefault();

            const formData = new FormData(this);
            try {
                const response = await fetch('../../backend/register/register.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    window.location.href = '../inicio/index.php';
                } else {
                    alert(result.message);
                }
            } catch (error) {
                alert('Error en la solicitud: ' + error.message);
            }
        });
    </script>
</body>
</html>
