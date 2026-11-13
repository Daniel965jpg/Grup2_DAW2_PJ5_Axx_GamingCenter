<?php
// VISTA: LOGIN
// Formulario de acceso del personal. Envía los datos a procs/login_proc.php.

session_start();

// Si ya ha entrado, directo al panel
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
    <title>Login - Hidden Arena</title>
    <link rel="icon" type="image/png" href="../img/logo-hidden-mini.png">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/responsive.css">
</head>
<body class="pagina-login">
    <!-- muted hace falta para que el navegador deje reproducirlo solo -->
    <video id="bg-video" class="video-fondo" autoplay loop muted playsinline>
        <source src="../img/video-fondo-gamingcenter.mp4" type="video/mp4">
    </video>

    <main class="login-panel">
        <div class="login-cabecera">
            <span class="login-estado">● Sistema en línea</span>
            <img src="../img/logo-hidden-mini.png" alt="Hidden Arena" class="logo-login">
            <p class="login-subtitulo">Panel de control · Acceso del personal</p>
        </div>

        <?php
        // Mensaje de error de una sola vez: se enseña y se borra
        if (isset($_SESSION['error'])) {
            echo "<div class='mensaje-aviso mensaje-aviso-error'>" . $_SESSION['error'] . "</div>";
            unset($_SESSION['error']);
        }
        ?>

        <!-- novalidate quita los avisos del navegador para que salgan los nuestros (script.js) -->
        <form class="login-formulario" action="../procs/login_proc.php" method="POST" id="formulario-login" novalidate>
            <div class="campo">
                <label for="usuario">Usuario</label>
                <input type="text" id="usuario" name="usuario" maxlength="30" placeholder="gamemaster01" required>
                <small id="usuario-error" class="aviso-campo-error"></small>
            </div>

            <div class="campo">
                <label for="contrasena">Contraseña</label>
                <input type="password" id="contrasena" name="contrasena" placeholder="••••••••" required>
                <small id="contrasena-error" class="aviso-campo-error"></small>
            </div>

            <button type="submit" class="boton-login">Iniciar sesión</button>
        </form>

        <!-- Los tres colores de las salas, como adorno -->
        <div class="login-pie">
            <span class="punto punto-pc"></span>
            <span class="punto punto-consola"></span>
            <span class="punto punto-vip"></span>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    <script src="../js/script.js"></script>
</body>
</html>
