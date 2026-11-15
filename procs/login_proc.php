<?php
// PROCESO: LOGIN
// Recibe el usuario y la contraseña del formulario de vistas/login.php.

session_start();

// Si alguien entra sin pasar por el formulario, lo devolvemos al login
if (!isset($_POST['usuario']) || !isset($_POST['contrasena'])) {
    header('Location: ../vistas/login.php');
    exit();
}

require_once '../bd/conexion.php';
require_once '../includes/funciones.php';

$nombre_usuario = limpiarDato($_POST['usuario']);
// La contraseña no pasa por limpiarDato: si la modificamos,
// ya no coincidiría con el hash guardado en la BD
$contrasena = $_POST['contrasena'];

// Comprobamos que no lleguen campos vacíos (el required del HTML se puede saltar)
if (camposVacios($nombre_usuario, $contrasena)) {
    $_SESSION['error'] = 'Tienes que rellenar todos los campos.';
    header('Location: ../vistas/login.php');
    exit();
}

// Nuestros usuarios solo llevan letras y números
if (!preg_match('/^[a-zA-Z0-9]+$/', $nombre_usuario)) {
    $_SESSION['error'] = 'El usuario solo puede tener letras y números.';
    header('Location: ../vistas/login.php');
    exit();
}

// Buscamos al usuario y nos traemos su id, el hash de la contraseña, el nombre y el rol
try {
    $sql = "SELECT id_usuario, password, nom_real, rol FROM usuarios WHERE username = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$nombre_usuario]);
} catch (PDOException $e) {
    echo "No se ha podido iniciar sesión. Inténtalo de nuevo más tarde.";
    die();
}

if ($stmt->rowCount() === 1) {
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (password_verify($contrasena, $usuario['password'])) {
        // Creamos un id de sesión nuevo al entrar: si alguien tenía el anterior,
        // ya no le sirve (el true borra la sesión vieja)
        session_regenerate_id(true);

        // Guardamos el id, y el nombre y el rol para las cabeceras
        $_SESSION['usuario'] = $usuario['id_usuario'];
        $_SESSION['nom_real'] = $usuario['nom_real'];
        $_SESSION['rol'] = $usuario['rol'];
        header('Location: ../vistas/dashboard.php');
        exit();
    }
}

// Mismo mensaje si falla el usuario o la contraseña, para no dar pistas
$_SESSION['error'] = 'Credenciales incorrectas';
header('Location: ../vistas/login.php');
exit();
?>