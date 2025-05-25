let usuario_id = null;

// Cargar usuario desde la sesión
fetch('../../Controller/get_session.php')
  .then(res => res.json())
  .then(data => {
    if (data.usuarioID) {
      usuario_id = data.usuarioID;
    } else {
      console.warn("Sesión no encontrada.");
      window.location.href = "../Pantalla_de_Bloqueo/Pantalladebloqueo.html";
    }
  });

// Expandir/colapsar FAQs
function toggleFaq(button) {
  const answer = button.nextElementSibling;
  answer.classList.toggle('show');
}

// Enviar pregunta
function enviarMensaje() {
  const mensaje = document.getElementById('mensaje').value.trim();
  const confirmacion = document.getElementById('confirmacion');

  if (!usuario_id) {
    alert("Debes iniciar sesión para enviar tu pregunta.");
    return;
  }

  if (mensaje === "") {
    alert("Por favor, escribe tu mensaje antes de enviarlo.");
    return;
  }

  fetch("../../Controller/ayuda.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      usuario_id: usuario_id,
      tipo: "pregunta",
      mensaje: mensaje
    })
  })
    .then(res => res.json())
    .then(data => {
      if (data.status === "success") {
        confirmacion.textContent = "✅ Tu pregunta ha sido enviada correctamente.";
        confirmacion.style.display = "block";
        document.getElementById("mensaje").value = "";
      } else {
        confirmacion.textContent = "❌ Error al enviar la pregunta.";
        confirmacion.style.display = "block";
      }
    })
    .catch(() => {
      confirmacion.textContent = "❌ Error de red al enviar la pregunta.";
      confirmacion.style.display = "block";
    });
}

// Enviar sugerencia
function enviarSugerencia() {
  const texto = document.getElementById("sugerenciaTexto").value.trim();
  const confirmacion = document.getElementById("sugerenciaConfirmacion");

  if (!usuario_id) {
    alert("Debes iniciar sesión para enviar sugerencias.");
    return;
  }

  if (texto === "") {
    alert("Por favor, escribe una sugerencia.");
    return;
  }

  fetch("../../Controller/ayuda.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      usuario_id: usuario_id,
      tipo: "sugerencia",
      mensaje: texto
    })
  })
    .then(res => res.json())
    .then(data => {
      if (data.status === "success") {
        confirmacion.textContent = "✅ ¡Gracias por tu sugerencia!";
        confirmacion.style.display = "block";
        document.getElementById("sugerenciaTexto").value = "";
      } else {
        confirmacion.textContent = "❌ Error al enviar la sugerencia.";
        confirmacion.style.display = "block";
      }
    })
    .catch(() => {
      confirmacion.textContent = "❌ Error de red al enviar la sugerencia.";
      confirmacion.style.display = "block";
    });
}

// Enviar valoración
function enviarValoracion(valor) {
  if (!usuario_id) {
    alert("Debes iniciar sesión para valorar.");
    return;
  }

  fetch("../../Controller/ayuda.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      usuario_id: usuario_id,
      tipo: "valoracion",
      puntuacion: valor
    })
  })
    .then(res => res.json())
    .then(data => {
      if (data.status === "success") {
        alert(`Has valorado con ${valor} estrella${valor > 1 ? "s" : ""}.`);
      } else {
        alert("❌ Error al registrar la valoración.");
      }
    })
    .catch(() => {
      alert("❌ Error de red al enviar la valoración.");
    });
}

// Lógica de estrellas
document.addEventListener('DOMContentLoaded', () => {
  const stars = document.querySelectorAll('.rating .star');
  let selectedRating = 0;

  stars.forEach((star, index) => {
    star.addEventListener('mouseover', () => highlightStars(index));
    star.addEventListener('mouseout', () => highlightStars(selectedRating - 1));
    star.addEventListener('click', () => {
      selectedRating = index + 1;
      highlightStars(index);
      enviarValoracion(selectedRating);
    });
  });

  function highlightStars(index) {
    stars.forEach((star, i) => {
      star.classList.toggle('selected', i <= index);
      star.classList.toggle('hover', i <= index);
    });
  }
});

// Popup sugerencias
function toggleSugerenciaPopup() {
    const sugerenciaPopup = document.getElementById('sugerenciaPopup');
    const guiaPopup = document.getElementById('guiaPopup');

    // Tancar guia
    guiaPopup?.classList.remove('show');
    guiaPopup?.style.display && (guiaPopup.style.display = 'none');

    // Obre o tanca aquest
    sugerenciaPopup.style.display = sugerenciaPopup.style.display === "block" ? "none" : "block";
}

function openMenu() {
    document.getElementById('sideMenu').style.width = '250px';
}

function closeMenu() {
    document.getElementById('sideMenu').style.width = '0';
}

function eliminarCuenta() {
    if (confirm("¿Estás seguro de que deseas eliminar tu cuenta? Esta acción no se puede deshacer.")) {
        fetch("../../Controller/eliminar_usuario.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            }
        })
        .then(res => res.text())
        .then(text => {
            let data;
            try {
                data = JSON.parse(text);
            } catch (e) {
                console.error("Respuesta no JSON:", text);
                alert("❌ Error inesperado del servidor. Revisa la consola.");
                return;
            }

            if (data.status === "success") {
                alert("Tu cuenta ha sido eliminada correctamente.");
                window.location.href = "../Pantalla_de_Bloqueo/Pantalladebloqueo.html";
            } else {
                alert("❌ Error al eliminar la cuenta: " + (data.message || "Error desconocido."));
            }
        })
        .catch(error => {
            console.error("Error en la solicitud:", error);
            alert("❌ Error de red al intentar eliminar la cuenta.");
        });
    }
}

function toggleGuiaPopup() {
    const guiaPopup = document.getElementById('guiaPopup');
    const sugerenciaPopup = document.getElementById('sugerenciaPopup');

    // Tancar sugg
    sugerenciaPopup?.classList.remove('show');
    sugerenciaPopup?.style.display && (sugerenciaPopup.style.display = 'none');

    // Obre o tanca aquest
    guiaPopup.style.display = guiaPopup.style.display === "block" ? "none" : "block";
}



