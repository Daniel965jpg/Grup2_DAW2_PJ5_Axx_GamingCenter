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
?>