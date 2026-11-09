<?php
session_start();
require '../sql/db.php';

// Si ya hay sesión iniciada, no hace falta volver a loguearse
if (isset($_SESSION['gamemaster_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validación en servidor: campos obligatorios
    if ($username === '' || $password === '') {
        $error = "Cal omplir tots els camps.";
    } else {
        // Consulta preparada para evitar inyección SQL
        $stmt = $pdo->prepare("SELECT id, username, password, nom, cognom FROM gamemasters WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $gm = $stmt->fetch();

        // password_verify compara la contraseña con el hash guardado
        if ($gm && password_verify($password, $gm['password'])) {
            session_regenerate_id(true); // evita fijación de sesión
            $_SESSION['gamemaster_id'] = $gm['id'];
            $_SESSION['username']      = $gm['username'];
            $_SESSION['nom']           = $gm['nom'];
            $_SESSION['cognom']        = $gm['cognom'];
            header('Location: index.php');
            exit;
        } else {
            // Mensaje genérico: no revelar si falla el usuario o la contraseña
            $error = "Usuari o contrasenya incorrectes.";
        }
    }
}
?>
