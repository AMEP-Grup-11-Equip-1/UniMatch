window.addEventListener("DOMContentLoaded", () => {
  loadChat();
});

function loadChat() {
  const OpenListDiv = document.getElementById("OpenList");
  const CloseListDiv = document.getElementById("CloseList");
  const chatDetailContainer = document.getElementById("chatDetailContainer"); // container para mostrar o chat completo

  fetch("../../Controller/chat_ayuda.php?action=get_preguntas")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        OpenListDiv.innerHTML = "";
        CloseListDiv.innerHTML = "";

        data.data.forEach((pregunta) => {
          const div = document.createElement("div");
          div.classList.add("chat-item");

          const mensajeRecortado =
            pregunta.mensaje.length > 50
              ? pregunta.mensaje.substring(0, 50) + "..."
              : pregunta.mensaje;
          div.innerHTML = `<strong>${mensajeRecortado}</strong>`;
          div.dataset.id = pregunta.id;

          // Dentro do forEach onde você cria os itens do chat
          div.onclick = () => {
            const isFromOpenList = pregunta.cerrado !== "0"; // Verifica se não está fechado
            loadMessages(pregunta.id, isFromOpenList);
          };

          if (pregunta.cerrado === "1") {
            CloseListDiv.appendChild(div);
          } else {
            OpenListDiv.appendChild(div);
          }
        });
      }
    })
    .catch((error) => console.error("Error en la solicitud:", error));
}

function loadMessages(preguntaId, isFromOpenList) {
  fetch(
    `../../Controller/chat_ayuda.php?action=get_mensajes&pregunta_id=${preguntaId}`
  )
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        console.log("Mensajes cargados:", data.data);
        // Limpa o container do chat completo
        messagesContainer.innerHTML = "";

        if (isFromOpenList) {
          document.getElementById("btnCerrar").style.visibility = "hidden"; // Oculta o botão de fechar chat se for da lista abert
        }
        else {
          document.getElementById("btnCerrar").style.visibility = "visible"; // Mostra o botão de fechar chat se for da lista fechada
        }


        document.getElementById("chatId").value = preguntaId; // Atualiza o ID da pergunta

        data.data.forEach((mensaje) => {
          const div = document.createElement("div");

          // Define a classe baseada em admin_name
          if (mensaje.admin_name) {
            div.classList.add("message-admin");
          } else {
            div.classList.add("message-user");
          }

          // Cria os elementos internos
          const smallText = document.createElement("small");
          smallText.textContent = mensaje.mensaje;

          const strongName = document.createElement("strong");
          strongName.classList.add("senderName");
          strongName.textContent = mensaje.admin_name || ""; // vazio se null

          const messageTime = document.createElement("div");
          messageTime.classList.add("message-time");
          messageTime.textContent = mensaje.fecha;

          // Adiciona os elementos ao div pai
          div.appendChild(strongName);
          div.appendChild(smallText);
          div.appendChild(messageTime);

          // Adiciona o div ao container do chat
          messagesContainer.appendChild(div);
        });
      } else {
        console.error("No se pudieron cargar los mensajes:", data.message);
      }
    })
    .catch((error) => console.error("Error en la solicitud:", error));
}

function Show(tipo) {
  const OpenListDiv = document.getElementById("OpenList");
  const CloseListDiv = document.getElementById("CloseList");
  const tabOpen = document.getElementById("tab-open");
  const tabClosed = document.getElementById("tab-closed");
  const BtnSend = document.getElementById("btnEnviar");



  if (tipo === "open" && OpenListDiv.style.display === "none") {
    OpenListDiv.style.display = "block";
    CloseListDiv.style.display = "none";
    messagesContainer.innerHTML = "";
    BtnSend.disabled = false; // Mostrar el botón de enviar
    tabOpen.classList.add("active");
    tabClosed.classList.remove("active");

      document.getElementById("btnCerrar").style.visibility = "hidden";

  } else if (tipo === "close" && CloseListDiv.style.display === "none") {
    OpenListDiv.style.display = "none";
    CloseListDiv.style.display = "block";
    messagesContainer.innerHTML = "";
    BtnSend.disabled = true; // Ocultar el botón de enviar
    tabOpen.classList.remove("active");
    tabClosed.classList.add("active");

      document.getElementById("btnCerrar").style.visibility = "hidden";

  }
}

function sendMessage() {
  const messageInput = document.getElementById("message");
  const preguntaId = document.getElementById("chatId").value;

  if (messageInput.value.trim() === "") {
    messageInput.placeholder = "Por favor, escribe un mensaje.";
    return;
  }

  console.log(
    "Enviando mensaje:",
    messageInput.value,
    "para pregunta ID:",
    preguntaId
  );

  const formData = new FormData();
  formData.append("action", "enviar_mensaje");
  formData.append("pregunta_id", preguntaId);
  formData.append("mensaje", messageInput.value);

  fetch("../../Controller/chat_ayuda.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        messageInput.value = "";
        loadMessages(preguntaId);
      } else {
        console.error("Error al enviar el mensaje:", data.message);
      }
    })
    .catch((error) => console.error("Error en la solicitud:", error));
}

function closeChat() {
  const preguntaId = document.getElementById("chatId").value;

  if (!preguntaId) {
    console.error("No hay ID de pregunta para cerrar el chat.");
    return;
  }

  //aviso preguntando si realmente se quiere cerrar el chat
  if (!confirm("¿Estás seguro de que quieres cerrar este chat?")) {
    return;
  }

  const formData = new FormData();
  formData.append("action", "cerrar_chat");
  formData.append("pregunta_id", preguntaId);

  fetch("../../Controller/chat_ayuda.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        loadChat(); // Recargar la lista de chats
        Show("close"); // Mostrar la lista de chats abiertos
        loadMessages(preguntaId); // Limpiar los mensajes del chat actual
      } else {
        console.error("Error al cerrar el chat:", data.message);
      }
    })
    .catch((error) => console.error("Error en la solicitud:", error));
}

// Función para abrir el menú
function openMenu() {
  document.getElementById("sideMenu").style.width = "250px";
}

// Función para cerrar el menú
function closeMenu() {
  document.getElementById("sideMenu").style.width = "0";
}
