<?php
session_start();
$_SESSION = [];       // vaciar variables de sesión
session_destroy();    // destruir la sesión
header('Location: login.php');
exit;