// Certifique-se de importar cargarUnis.js antes deste arquivo no HTML

document.addEventListener("DOMContentLoaded", function () {
  // Chama a função para carregar as universidades ao iniciar
  cargarUniversidades();

  function mostrarRegistro() {
    oculto("regs-form");
    document.getElementById("register-form").style.display = "block";
  }

  function oculto(id) {
    document.getElementById(id).style.display = "none";
  }

  document
    .getElementById("registerForm")
    .addEventListener("submit", function (event) {
      event.preventDefault();

      const formData = new URLSearchParams(new FormData(this));

      fetch("../../Controller/registro.php?" + formData.toString(), {
        method: "GET",
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            document.getElementById("register-form").style.display = "none";
            document.getElementById("regs-form").style.display = "block";

            document.getElementById("id_gender").value = 1;

            var emailCompleto = document.getElementById("hidden_email").value;
            document.getElementById("lbl_mail_regs").textContent = emailCompleto;

            var hiddenEmailInput = document.createElement("input");
            hiddenEmailInput.type = "hidden";
            hiddenEmailInput.name = "email_full";
            hiddenEmailInput.value = emailCompleto;
            document
              .querySelector("#regs-form form")
              .appendChild(hiddenEmailInput);

            // Preencher o campo oculto id_uni com o ID da universidade selecionada
            var selectedUniId = document.getElementById("SelectUni").value;
            document.getElementById("id_uni").value = selectedUniId;

            getGenders();
          } else {
            document.getElementById("lbl_error").textContent =
              data.message || "Error en la validación.";
          }
        })
        .catch((error) => {
          console.error("Error en la solicitud:", error);
          document.getElementById("lbl_error").textContent =
            "Error al conectar con el servidor.";
        });
    });

  function getGenders() {
    fetch("../../Controller/registro.php?action=getGenders")
      .then((response) => response.json())
      .then((data) => {
        if (data.error) {
          console.error("Error al cargar los datos de genero:", data.error);
          return;
        }

        if (data.data && Array.isArray(data.data)) {
          var datos = data.data;
          var select = document.getElementById("SelectGender");
          select.innerHTML = "";

          datos.forEach(function (item) {
            var option = document.createElement("option");
            option.value = item.id;
            option.text = item.Gender;
            select.appendChild(option);
          });

          // Preencher o campo oculto id_gender com o ID do gênero selecionado
          select.addEventListener("change", function () {
            document.getElementById("id_gender").value = this.value;
          });
        } else {
          console.error(
            "Datos de las universidades no encontrados o formato inválido."
          );
        }
      })
      .catch((error) =>
        console.error("Error al cargar los datos de genero:", error)
      );
  }

  // Torna mostrarRegistro global para ser chamado pelo HTML
  window.mostrarRegistro = mostrarRegistro;
});
