// VALIDACIÓN DEL LOGIN
// Evita enviar el formulario con campos vacíos y avisa al usuario.
// Es solo una ayuda: login_proc.php vuelve a validarlo en el servidor.

// Asignamos los eventos desde aquí y no en el HTML.
// El script se carga al final del <body>, así que los elementos ya existen.
document.getElementById("formulario-login").onsubmit = validarLogin;
document.getElementById("username").onblur = validarUsuario;
document.getElementById("password").onblur = validarPassword;

// Al enviar el formulario. Si devuelve false, el formulario no se envía.
function validarLogin() {
    // Validamos los dos para que salgan los dos mensajes a la vez si hace falta
    const usuarioValido = validarUsuario();
    const passwordValida = validarPassword();

    if (!usuarioValido) {
        document.getElementById("username").focus();
        mostrarAviso("el usuario");
        return false;
    }

    if (!passwordValida) {
        document.getElementById("password").focus();
        mostrarAviso("la contraseña");
        return false;
    }

    return true;
}

// Al salir del campo usuario: escribe el aviso debajo o lo borra si ya está bien
function validarUsuario() {
    const usuario = document.getElementById("username").value;
    const error = document.getElementById("username-error");

    if (usuario.trim() == "") {
        error.textContent = "Falta escribir el usuario.";
        return false;
    }

    error.textContent = "";
    return true;
}

// Al salir del campo contraseña: lo mismo que con el usuario
function validarPassword() {
    const password = document.getElementById("password").value;
    const error = document.getElementById("password-error");

    if (password.trim() == "") {
        error.textContent = "Falta escribir la contraseña.";
        return false;
    }

    error.textContent = "";
    return true;
}

// Modal de SweetAlert2 con los colores oscuros del Gaming Center
function mostrarAviso(nombreCampo) {
    Swal.fire({
        icon: "warning",
        title: "¡Ups! Falta un dato",
        text: "Falta escribir " + nombreCampo + ".",
        confirmButtonText: "Vale, lo corrijo",
        background: "#161b22",
        color: "#e6edf3",
        confirmButtonColor: "#00d2ff"
    });
}