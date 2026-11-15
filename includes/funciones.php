<?php
// FUNCIONES
// Funciones sencillas que usamos en varias páginas (sin consultas ni redirecciones).

// Quitamos espacios y convertimos < > " ' & en texto normal para evitar XSS
function limpiarDato($dato) {
    $dato = trim($dato);
    $dato = htmlspecialchars($dato);
    return $dato;
}

// Devuelve true si alguno de los dos campos está vacío
function camposVacios($campo1, $campo2) {
    if (empty($campo1) || empty($campo2)) {
        $error = true;
    } else {
        $error = false;
    }
    return $error;
}

// Elegimos la imagen del puesto por su nombre (SIM, VR) o por el tipo de sala
function imagenPuesto($nombre, $tipo_sala) {
    if (preg_match('/SIM/', $nombre)) {
        $imagen = '../img/simulador-coche.png';
    } elseif (preg_match('/VR/', $nombre)) {
        $imagen = '../img/gafasrv.webp';
    } elseif ($tipo_sala == 'vip') {
        $imagen = '../img/ordenador-salavip.jpg';
    } elseif ($tipo_sala == 'consola') {
        $imagen = '../img/consola-sala.png';
    } else {
        $imagen = '../img/ordenador-estandar.png';
    }
    return $imagen;
}

// Contamos cuántos puestos hay en una lista y cuántos están libres
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

// Número con la palabra en singular o plural ("1 ocupado", "3 ocupados")
function textoCantidad($numero, $singular, $plural) {
    if ($numero == 1) {
        $texto = $numero . ' ' . $singular;
    } else {
        $texto = $numero . ' ' . $plural;
    }
    return $texto;
}
?>