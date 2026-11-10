// CAMBIO DE SALA
// Al elegir otra sala en el desplegable, el formulario se envía solo (sin botón).
// Va en un archivo aparte porque script.js busca el formulario del login, que aquí no existe.

document.getElementById("id_sala").onchange = cambiarSala;

function cambiarSala() {
    document.getElementById("formulario-sala").submit();
}