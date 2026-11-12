// CONFIRMAR ANTES DE OCUPAR O LIBERAR UN PUESTO
// Lo usan sala.php y ocupados.php: las dos pintan las mismas tarjetas (.tarjeta-puesto).
// Hay muchas tarjetas, así que las recorremos con un for y a cada una le asignamos el evento.
const tarjetas = document.getElementsByClassName("tarjeta-puesto");
for (let i = 0; i < tarjetas.length; i++) {
    tarjetas[i].onclick = confirmarCambio;
}

// Dentro de la función, "this" es la tarjeta que se ha pulsado.
// Devuelve false para que el enlace NO se abra todavía: solo se abre si se confirma en el modal.
function confirmarCambio() {
    const enlace = this;

    Swal.fire({
        icon: "question",
        title: "¿" + enlace.title + "?",
        text: "El cambio quedará guardado en el histórico.",
        showCancelButton: true,
        confirmButtonText: "Sí, confirmar",
        cancelButtonText: "Cancelar",
        background: "#161b22",
        color: "#e6edf3",
        confirmButtonColor: "#00d2ff",
        cancelButtonColor: "#30363d",
        // Por defecto SweetAlert pone "height: auto" al body mientras está abierto y eso anula
        // nuestro height: 100vh, así que la sala se encogía. Con false deja la página como está.
        heightAuto: false
    }).then(function (resultado) {
        // then se ejecuta cuando se cierra el modal; solo seguimos si se ha pulsado "Sí"
        if (resultado.isConfirmed) {
            window.location.href = enlace.href;
        }
    });

    return false;
}
