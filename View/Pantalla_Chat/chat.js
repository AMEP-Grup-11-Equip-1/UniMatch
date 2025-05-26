function enviarMensaje() {
    const input = document.getElementById("mensajeInput");
    const mensaje = input.value.trim();


    if (mensaje === "") return; // No enviar si está vacío


    // Crear elemento del mensaje
    const contenedor = document.getElementById("messagesContainer");
    const nuevoMensaje = document.createElement("div");
    nuevoMensaje.classList.add("message", "message-sent");


    // Insertar contenido usando concatenación
    nuevoMensaje.innerHTML =
        '<span class="message-text">' + mensaje + '</span>' +
        '<div class="message-time">' + obtenerHoraActual() + '</div>';


    contenedor.appendChild(nuevoMensaje);
    contenedor.scrollTop = contenedor.scrollHeight; // Baja automáticamente


    input.value = ""; // Limpiar campo
}


// Detectar tecla Enter
document.getElementById("mensajeInput").addEventListener("keydown", function(event) {
    if (event.key === "Enter") {
        event.preventDefault(); // Evitar salto de línea
        enviarMensaje();
    }
});


// Obtener hora actual en formato HH:MM
function obtenerHoraActual() {
    const ahora = new Date();
    const horas = ahora.getHours().toString().padStart(2, "0");
    const minutos = ahora.getMinutes().toString().padStart(2, "0");
    return horas + ":" + minutos;
}


