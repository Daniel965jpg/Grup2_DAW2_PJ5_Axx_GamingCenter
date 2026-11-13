<?php
// PROCESO: CERRAR SESIÓN
// Borra todo lo guardado en la sesión y vuelve al login.

session_start();
session_unset();
session_destroy();

header('Location: ../vistas/login.php');
exit();
?>