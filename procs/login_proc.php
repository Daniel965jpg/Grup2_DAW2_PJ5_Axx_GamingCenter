<?php
// PROCESO: LOGIN
// Recibe el formulario de vistas/login.php. Si el usuario y la contraseña son correctos
// entra al panel; si no, vuelve al login con un error.

session_start();

// Si se entra escribiendo la URL, sin pasar por el formulario, fuera
if (!isset($_POST['usuario']) || !isset($_POST['contrasena'])) {
    header('Location: ../vistas/login.php');
    exit();
}

require_once '../bd/conexion.php';
require_once '../includes/funciones.php';

$nombre_usuario = limpiarDato($_POST['usuario']);
// La contraseña no se limpia: si se modifica ya no coincide con el hash
$contrasena = $_POST['contrasena'];

// El required del HTML se puede saltar, así que lo comprobamos también aquí
if (camposVacios($nombre_usuario, $contrasena)) {
    $_SESSION['error'] = 'Tienes que rellenar todos los campos.';
    header('Location: ../vistas/login.php');
    exit();
}

// Nuestros usuarios solo llevan letras y números (admin, izan, dani...)
if (!preg_match('/^[a-zA-Z0-9]+$/', $nombre_usuario)) {
    $_SESSION['error'] = 'El usuario solo puede tener letras y números.';
    header('Location: ../vistas/login.php');
    exit();
}

try {
    $sql = "SELECT id_usuario, password, nom_real, rol FROM usuarios WHERE username = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$nombre_usuario]);
} catch (PDOException $e) {
    echo "Error al comprobar el usuario: " . $e->getMessage();
    die();
}

// username es UNIQUE, así que como mucho sale una fila
if ($stmt->rowCount() === 1) {
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (password_verify($contrasena, $usuario['password'])) {
        // El id va en 'usuario' (es lo que comprueban todas las páginas).
        // El nombre y el rol son para enseñar en las cabeceras quién está conectado.
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