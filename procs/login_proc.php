<?php
// PROCESO: LOGIN
// Recibe el formulario de vistas/login.php, comprueba el usuario y la contraseña
// contra la BD y redirige al dashboard o de vuelta al login con un mensaje de error.

session_start();

// Si alguien entra aquí escribiendo la URL, sin pasar por el formulario, lo devolvemos al login
if (!isset($_POST['username']) || !isset($_POST['password'])) {
    header('Location: ../vistas/login.php');
    exit();
}

require_once '../bd/conexion.php';
require_once '../includes/funciones.php';

$username = limpiarDato($_POST['username']);
// La contraseña no se limpia: si se tocara ya no coincidiría con el hash guardado
$password = $_POST['password'];

// El required del HTML se puede saltar, así que lo volvemos a comprobar en el servidor
if (camposVacios($username, $password)) {
    $_SESSION['error'] = 'Tienes que rellenar todos los campos.';
    header('Location: ../vistas/login.php');
    exit();
}

try {
    $sql = "SELECT id_usuario, password, rol FROM usuarios WHERE username = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$username]);
} catch (PDOException $e) {
    echo "Error al comprobar el usuario: " . $e->getMessage();
    die();
}

// username es UNIQUE, así que si existe solo puede salir una fila
if ($stmt->rowCount() === 1) {
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    $bd_password = $usuario['password'];

    if (password_verify($password, $bd_password)) {
        $_SESSION['usuario'] = $usuario['id_usuario'];
        header('Location: ../vistas/dashboard.php');
        exit();
    }
}

// Mismo mensaje tanto si falla el usuario como la contraseña, para no dar pistas
$_SESSION['error'] = 'Credenciales incorrectas';
header('Location: ../vistas/login.php');
exit();
?>
