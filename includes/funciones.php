<?php
// FUNCIONES DE SANEAMIENTO Y VALIDACIÓN
// Aquí solo van funciones sencillas: nada de conexión, consultas ni redirecciones.

// Quita espacios sobrantes y convierte los caracteres especiales en entidades HTML (evita XSS)
function limpiarDato($dato) {
    $dato = trim($dato);
    $dato = htmlspecialchars($dato);
    return $dato;
}

// Devuelve true si alguno de los dos campos ha llegado vacío
function camposVacios($campo1, $campo2) {
    if (empty($campo1) || empty($campo2)) {
        $error = true;
    } else {
        $error = false;
    }
    return $error;
}

// Devuelve la imagen que toca a un puesto según su nombre y el tipo de su sala.
// La usan sala.php y ocupados.php, así la regla está escrita una sola vez.
// str_contains mira si un texto contiene otro (SIM-03 contiene "SIM").
function imagenPuesto($nombre, $tipo_sala) {
    if (str_contains($nombre, 'SIM')) {
        // El nombre del archivo tiene un espacio: en una URL se escribe como %20
        $imagen = '../img/simuladorCoche%20(1).png';
    } elseif (str_contains($nombre, 'VR')) {
        $imagen = '../img/gafasrv.webp';
    } elseif ($tipo_sala == 'vip') {
        $imagen = '../img/logo_vip.png';
    } elseif ($tipo_sala == 'consola') {
        $imagen = '../img/consola-sala.png';
    } else {
        $imagen = '../img/ordenador-sala1.png';
    }
    return $imagen;
}
?>