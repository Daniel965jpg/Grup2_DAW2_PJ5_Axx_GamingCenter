// JS DEL PROYECTO (login.php, sala.php y ocupados.php)
// Cada bloque comprueba antes si su elemento está en la página: getElementById devuelve null
// si no existe, y sin ese if el script daría error y dejaría de funcionar en las otras páginas.

// Colores del modo oscuro para todos los SweetAlert
const FONDO_MODAL = "#161b22";
const TEXTO_MODAL = "#e6edf3";
const COLOR_CANCELAR = "#30363d";


// ---------- 1. LOGIN: no dejar enviar el formulario con campos vacíos ----------
const formularioLogin = document.getElementById("formulario-login");
if (formularioLogin != null) {
    formularioLogin.onsubmit = validarLogin;
    document.getElementById("usuario").onblur = validarUsuario;
    document.getElementById("contrasena").onblur = validarContrasena;
}

// Si devuelve false el formulario no se envía
function validarLogin() {
    // Validamos los dos para que salgan los dos avisos a la vez
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

    // Vacío o solo espacios
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


// ---------- 2. SALA: al cambiar el desplegable se carga la otra sala ----------
const formularioSala = document.getElementById("formulario-sala");
if (formularioSala != null) {
    document.getElementById("id_sala").onchange = cambiarSala;
}

function cambiarSala() {
    document.getElementById("formulario-sala").submit();
}


// ---------- 3. OCUPADOS: al cambiar un filtro se recarga la página filtrada ----------
const formularioFiltros = document.getElementById("formulario-filtros");
if (formularioFiltros != null) {
    document.getElementById("filtro-sala").onchange = enviarFiltros;
    document.getElementById("filtro-gamemaster").onchange = enviarFiltros;
}

function enviarFiltros() {
    document.getElementById("formulario-filtros").submit();
}


// ---------- 4. SALA y OCUPADOS: confirmar antes de ocupar o liberar ----------
// Hay muchas tarjetas, así que con un for le ponemos el evento a cada una.
// getElementsByClassName funciona como getElementsByName, pero buscando por la clase.
const tarjetas = document.getElementsByClassName("tarjeta-puesto");
for (let i = 0; i < tarjetas.length; i++) {
    tarjetas[i].onclick = confirmarCambio;
}

// Color del botón según la zona: azul PC, morado consolas, dorado VIP
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

// "this" es la tarjeta pulsada. Devolvemos false para que el enlace no se abra todavía:
// solo se abre si se confirma en el SweetAlert
function confirmarCambio() {
    const enlace = this;
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
        // swal-sala-pc, swal-sala-consola o swal-sala-vip: borde del color de la zona (en el CSS)
        customClass: {
            popup: "swal-sala-" + tipo
        },
        // Sin esto SweetAlert cambia la altura de la página y la sala se encoge
        heightAuto: false
    }).then(function (resultado) {
        // Se ejecuta al cerrar el modal: solo seguimos si se ha pulsado "Sí"
        if (resultado.isConfirmed) {
            window.location.href = enlace.href;
        }
    });

    return false;
}


// ---------- 5. OCUPADOS: aviso rojo antes del cierre de local ----------
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


// ---------- 6. Mensajes del servidor (error o éxito) en un SweetAlert ----------
// PHP deja el texto en un div oculto (aviso-error o aviso-exito) y aquí lo enseñamos
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