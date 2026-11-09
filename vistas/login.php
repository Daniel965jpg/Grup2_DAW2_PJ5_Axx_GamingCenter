<?php
// VISTA: LOGIN
// Formulario de acceso para los trabajadores del local. Envía los datos a procs/login_proc.php.

session_start();

// Si ya ha iniciado sesión no tiene sentido enseñarle el login otra vez
if (isset($_SESSION['usuario'])) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gaming Center</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body class="pagina-login">
    <main class="login-panel">
        <div class="login-cabecera">
            <span class="login-estado">● Sistema en línea</span>
            <h1 class="login-titulo">Gaming Center</h1>
            <p class="login-subtitulo">Panel de control · Acceso del personal</p>
        </div>

        <?php
        // El mensaje se enseña una sola vez: después de pintarlo lo borramos de la sesión
        if (isset($_SESSION['error'])) {
            echo "<div class='mensaje-aviso mensaje-aviso-error'>" . $_SESSION['error'] . "</div>";
            unset($_SESSION['error']);
        }
        ?>

        <form class="login-formulario" action="../procs/login_proc.php" method="POST">
            <div class="campo">
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" maxlength="30" placeholder="gamemaster01" required>
            </div>

            <div class="campo">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="boton-login">Iniciar sesión</button>
        </form>

        <div class="login-pie">
            <span class="punto punto-pc"></span>
            <span class="punto punto-consola"></span>
            <span class="punto punto-vip"></span>
        </div>
    </main>
</body>
</html>
