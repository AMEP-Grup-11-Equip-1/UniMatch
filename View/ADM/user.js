function getQueryParam(param) {
  const params = new URLSearchParams(window.location.search);
  return params.get(param);
}

const userId = getQueryParam("id");

// Carregar dados do usuário
if (!userId) {
  alert("No se especificó el usuario.");
  document.getElementById("user-name").textContent = "Error";
  document.getElementById("user-email").textContent = "";
  document.getElementById("user-status").textContent = "";
} else {
  fetch(`../../Controller/usuari_per_id.php?id=${userId}`)
    .then((res) => {
      if (!res.ok) throw new Error("Usuario no encontrado");
      return res.json();
    })
    .then((data) => {
      document.getElementById("user-name").textContent =
        data.nombre || "Sin nombre";
      document.getElementById("user-email").textContent =
        data.email || "Sin email";
      if (data.ok == 1) {
        document.getElementById("user-status").textContent = "Verificado";
      } else if (data.ok == 0) {
        // Si el usuario está bloqueado, muestra "Bloqueado", si no, "Nuevo usuario"
        document.getElementById("user-status").textContent = "Bloqueado";
      } else {
        document.getElementById("user-status").textContent = "Nuevo usuario";
      }
    })
    // Si ocurre un error al obtener los datos del usuario
    .catch((err) => {
      console.error(err);
      alert(err.message);
      // Muestra mensaje de error en los campos correspondientes
      document.getElementById("user-name").textContent =
        "Error al cargar usuario";
      document.getElementById("user-email").textContent = "";
    });
}

// Función para enviar el estado del usuario mediante una solicitud POST
function enviarEstado(valor) {
  // Verifica si el ID del usuario está presente
  if (!userId) {
    alert("ID do usuário não encontrado.");
    return;
  }

  // Realiza la solicitud POST para actualizar el estado del usuario
  fetch("../../Controller/usuari_per_id.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    // Envía el ID y el nuevo estado codificados en la URL
    body: `id=${encodeURIComponent(userId)}&estado=${encodeURIComponent(
      valor
    )}`,
  })
    // Procesa la respuesta como JSON
    .then((res) => res.json())
    .then((data) => {
      // Muestra un mensaje de éxito y redirige a la página principal
      alert(data.message || "Operación realizada");
      window.location.href = "home.html";
    })
    // Si ocurre un error al enviar el estado
    .catch((err) => {
      alert("Erro ao enviar estado.");
      console.error(err);
    });
}
