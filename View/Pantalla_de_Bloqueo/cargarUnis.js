function cargarUniversidades(
  selectId = "SelectUni",
  emailInputId = "txt_email",
  labelId = "lbl_mail",
  hiddenId1 = "hidden_email",
  hiddenId2 = "hidden_email_2"
) {
  fetch("../../Controller/registro.php?action=getUniversities")
    .then((response) => response.json())
    .then((data) => {
      if (data.error) {
        console.error("Error al cargar los datos de las universidades:", data.error);
        return;
      }

      if (data.data && Array.isArray(data.data)) {
        var datos = data.data;
        var select = document.getElementById(selectId);
        if (!select) {
          console.error("Elemento select no encontrado:", selectId);
          return;
        }
        select.innerHTML = "";

        datos.forEach(function (item) {
          var option = document.createElement("option");
          option.value = item.id;
          option.text = item.Uni_acronym;
          option.setAttribute("data-uni-mail", item.Uni_mail);
          select.appendChild(option);
        });

        // Só adiciona eventos se todos os elementos existem
        var emailInput = document.getElementById(emailInputId);
        var lblMail = document.getElementById(labelId);
        var hiddenEmail = document.getElementById(hiddenId1);
        var hiddenEmail2 = document.getElementById(hiddenId2);

        function updateLabel() {
          if (emailInput && lblMail && hiddenEmail) {
            var selectedOption = select.options[select.selectedIndex];
            var uniMail = selectedOption ? selectedOption.getAttribute("data-uni-mail") : "";
            var txtEmail = emailInput.value;
            lblMail.textContent = txtEmail + "@" + uniMail;
            hiddenEmail.value = txtEmail + "@" + uniMail;
            if (hiddenEmail2) hiddenEmail2.value = txtEmail + "@" + uniMail;
          }
        }

        if (emailInput && lblMail && hiddenEmail) {
          select.addEventListener("change", updateLabel);
          emailInput.addEventListener("input", updateLabel);
          updateLabel();

          // Bloqueia o caractere @ no campo de email
          emailInput.addEventListener("keypress", function (e) {
            if (e.key === "@") {
              e.preventDefault();
            }
          });
          // Também remove @ se colar
          emailInput.addEventListener("input", function (e) {
            if (this.value.includes("@")) {
              this.value = this.value.replace(/@/g, "");
            }
          });
        }
      } else {
        console.error("Datos de las universidades no encontrados o formato inválido.");
      }
    })
    .catch((error) =>
      console.error("Error al cargar los datos de las universidades:", error)
    );
}

// Para Registro.html (com email e label): usar padrão
// Para Login.html (apenas select): chame assim no script do Login.html:
cargarUniversidades("SelectUni");