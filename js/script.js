// JS DEL PROYECTO
// Lo cargan login.php, sala.php y ocupados.php; cada parte mira antes si su elemento existe

// Colores oscuros que usamos en todos los SweetAlert
const FONDO_MODAL = "#161b22";
const TEXTO_MODAL = "#e6edf3";
const COLOR_CANCELAR = "#30363d";


// Login: no dejamos enviar el formulario con campos vacíos
const formularioLogin = document.getElementById("formulario-login");
if (formularioLogin != null) {
    formularioLogin.onsubmit = validarLogin;
    document.getElementById("usuario").onblur = validarUsuario;
    document.getElementById("contrasena").onblur = validarContrasena;
}

// Si devolvemos false, el formulario no se envía
function validarLogin() {
    // Validamos los dos campos para que salgan los dos avisos a la vez
    const usuarioValido = validarUsuario();
    const contrasenaValida = validarContrasena();

    if (!usuarioValido) {
        document.getElementById("usuario").focus();
        avisoCampoVacio("el usuario");
        return false;
    }

    if (!contrasenaValida) {
        document.getElementById("contrasena").focus();
        avisoCampoVacio("la contraseña");
        return false;
    }

    return true;
}

function validarUsuario() {
    const valor = document.getElementById("usuario").value;
    const error = document.getElementById("usuario-error");

    // Vacío o solo con espacios
    if (valor == null || valor.length == 0 || /^\s+$/.test(valor)) {
        error.textContent = "Falta escribir el usuario.";
        return false;
    }

    error.textContent = "";
    return true;
}

function validarContrasena() {
    const valor = document.getElementById("contrasena").value;
    const error = document.getElementById("contrasena-error");

    if (valor == null || valor.length == 0 || /^\s+$/.test(valor)) {
        error.textContent = "Falta escribir la contraseña.";
        return false;
    }

    error.textContent = "";
    return true;
}

function avisoCampoVacio(nombreCampo) {
    Swal.fire({
        icon: "warning",
        title: "¡Ups! Falta un dato",
        text: "Falta escribir " + nombreCampo + ".",
        confirmButtonText: "Vale, lo corrijo",
        background: FONDO_MODAL,
        color: TEXTO_MODAL,
        confirmButtonColor: "#00d2ff"
    });
}


// Sala: al cambiar el desplegable cargamos la otra sala
const formularioSala = document.getElementById("formulario-sala");
if (formularioSala != null) {
    document.getElementById("id_sala").onchange = cambiarSala;
}

function cambiarSala() {
    document.getElementById("formulario-sala").submit();
}


// Ocupados: al cambiar un filtro recargamos la página filtrada
const formularioFiltros = document.getElementById("formulario-filtros");
if (formularioFiltros != null) {
    document.getElementById("filtro-sala").onchange = enviarFiltros;
    document.getElementById("filtro-gamemaster").onchange = enviarFiltros;
}

function enviarFiltros() {
    document.getElementById("formulario-filtros").submit();
}


// Tarjetas: pedimos confirmación antes de ocupar o liberar
// Color del botón según la zona: azul PC, morado consolas y dorado VIP
function colorSala(tipo) {
    let color;
    if (tipo == "vip") {
        color = "#f59e0b";
    } else if (tipo == "consola") {
        color = "#a855f7";
    } else {
        color = "#00d2ff";
    }
    return color;
}

// La tarjeta llega por parámetro (onclick="return confirmarCambio(this)") y devolvemos false
// para que el enlace solo se abra si se confirma en el SweetAlert
function confirmarCambio(enlace) {
    const tipo = enlace.getAttribute("data-tipo");

    Swal.fire({
        icon: "question",
        title: "¿" + enlace.title + "?",
        text: "El cambio quedará guardado en el histórico.",
        showCancelButton: true,
        confirmButtonText: "Sí, confirmar",
        cancelButtonText: "Cancelar",
        background: FONDO_MODAL,
        color: TEXTO_MODAL,
        confirmButtonColor: colorSala(tipo),
        cancelButtonColor: COLOR_CANCELAR,
        // Borde del color de la zona (swal-sala-pc, swal-sala-consola o swal-sala-vip)
        customClass: {
            popup: "swal-sala-" + tipo
        },
        // Sin esto SweetAlert cambia la altura de la página
        heightAuto: false
    }).then(function (resultado) {
        // Al cerrar el modal, solo seguimos el enlace si se ha pulsado "Sí"
        if (resultado.isConfirmed) {
            window.location.href = enlace.href;
        }
    });

    return false;
}


// Cierre de local: aviso en rojo antes de liberar todo
const botonCierre = document.getElementById("boton-cierre");
if (botonCierre != null) {
    botonCierre.onclick = confirmarCierre;
}

function confirmarCierre() {
    const enlace = this;

    Swal.fire({
        icon: "warning",
        title: "¿Estás seguro?",
        text: "Se liberarán todos los dispositivos y se cerrará el registro",
        showCancelButton: true,
        confirmButtonText: "Sí, cerrar el local",
        cancelButtonText: "Cancelar",
        background: FONDO_MODAL,
        color: TEXTO_MODAL,
        confirmButtonColor: "#ff4655",
        cancelButtonColor: COLOR_CANCELAR,
        customClass: {
            popup: "swal-peligro"
        },
        heightAuto: false
    }).then(function (resultado) {
        if (resultado.isConfirmed) {
            window.location.href = enlace.href;
        }
    });

    return false;
}


// Mensajes del servidor: PHP deja el texto en un div oculto (aviso-error o aviso-exito)
// y aquí lo enseñamos en un SweetAlert
const avisoError = document.getElementById("aviso-error");
if (avisoError != null) {
    Swal.fire({
        icon: "error",
        title: "No permitido",
        text: avisoError.textContent,
        confirmButtonText: "Entendido",
        background: FONDO_MODAL,
        color: TEXTO_MODAL,
        confirmButtonColor: "#ff4655",
        customClass: {
            popup: "swal-peligro"
        },
        heightAuto: false
    });
}

const avisoExito = document.getElementById("aviso-exito");
if (avisoExito != null) {
    Swal.fire({
        icon: "success",
        title: "Hecho",
        text: avisoExito.textContent,
        confirmButtonText: "Vale",
        background: FONDO_MODAL,
        color: TEXTO_MODAL,
        confirmButtonColor: "#00ff88",
        customClass: {
            popup: "swal-exito"
        },
        heightAuto: false
    });
}