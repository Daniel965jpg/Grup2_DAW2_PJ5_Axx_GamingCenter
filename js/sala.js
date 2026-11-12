// CAMBIO DE SALA (solo en sala.php)
// Al elegir otra sala en el desplegable, el formulario se envía solo (sin botón).
// La confirmación de ocupar/liberar está en confirmar.js, que también usa ocupados.php.
document.getElementById("id_sala").onchange = cambiarSala;

function cambiarSala() {
    document.getElementById("formulario-sala").submit();
}
