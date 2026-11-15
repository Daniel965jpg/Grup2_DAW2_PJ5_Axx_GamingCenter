<?php
// PROCESO: CERRAR SESIÓN
// Se llama desde el botón "Cerrar sesión" del panel.

session_start();
session_unset();
session_destroy();

header('Location: ../vistas/login.php');
exit();
?>