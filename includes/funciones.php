<?php
// FUNCIONES DE APOYO
// Solo funciones sencillas: aquí no hay conexión, ni consultas, ni redirecciones.

// Quita espacios de los lados y convierte < > " ' & en texto normal (evita XSS)
function limpiarDato($dato) {
    $dato = trim($dato);
    $dato = htmlspecialchars($dato);
    return $dato;
}

// true si alguno de los dos campos llega vacío
function camposVacios($campo1, $campo2) {
    if (empty($campo1) || empty($campo2)) {
        $error = true;
    } else {
        $error = false;
    }
    return $error;
}

// Imagen de cada puesto según su nombre (SIM-03, VR-01...) y el tipo de sala.
// La usan sala.php y ocupados.php, así no repetimos la regla en dos sitios.
function imagenPuesto($nombre, $tipo_sala) {
    if (preg_match('/SIM/', $nombre)) {
        $imagen = '../img/simulador-coche.png';
    } elseif (preg_match('/VR/', $nombre)) {
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

// Cuenta los puestos de una lista: cuántos hay (su capacidad) y cuántos están libres.
// La usa sala.php para la sala entera, para cada bloque (Squad, Dúo...) y para cada módulo dúo.
function resumenPuestos($lista) {
    $resumen = ['puestos' => 0, 'libres' => 0];
    foreach ($lista as $puesto) {
        $resumen['puestos'] = $resumen['puestos'] + 1;
        if ($puesto['estado'] == 'lliure') {
            $resumen['libres'] = $resumen['libres'] + 1;
        }
    }
    return $resumen;
}

// Número con su palabra en singular o plural: "1 ocupado", "3 ocupados".
// Así no sale "1 ocupados" en los contadores del panel y de la sala.
function textoCantidad($numero, $singular, $plural) {
    if ($numero == 1) {
        $texto = $numero . ' ' . $singular;
    } else {
        $texto = $numero . ' ' . $plural;
    }
    return $texto;
}
?>