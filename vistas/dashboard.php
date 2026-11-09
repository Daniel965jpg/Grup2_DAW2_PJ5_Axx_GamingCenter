<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Gaming Center</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    
</body>
</html>