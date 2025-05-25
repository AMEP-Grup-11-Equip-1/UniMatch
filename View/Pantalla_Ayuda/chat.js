// Evento que se ejecuta cuando el DOM está completamente cargado
window.addEventListener("DOMContentLoaded", () => {
  loadChat();
});

// Función para cargar la lista de chats abiertos y cerrados
function loadChat() {
  const OpenListDiv = document.getElementById("OpenList");
  const CloseListDiv = document.getElementById("CloseList");
  const chatDetailContainer = document.getElementById("chatDetailContainer"); // Contenedor para mostrar el chat completo

  fetch("../../Controller/chat_ayuda.php?action=get_preguntas")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        OpenListDiv.innerHTML = "";
        CloseListDiv.innerHTML = "";

        // Recorre cada pregunta (chat) y la agrega a la lista correspondiente
        data.data.forEach((pregunta) => {
          const div = document.createElement("div");
          div.classList.add("chat-item");

          // Recorta el mensaje si es muy largo
          const mensajeRecortado =
            pregunta.mensaje.length > 50
              ? pregunta.mensaje.substring(0, 50) + "..."
              : pregunta.mensaje;
          div.innerHTML = `<strong>${mensajeRecortado}</strong>`;
          div.dataset.id = pregunta.id;

          // Al hacer clic en el chat, carga los mensajes de ese chat
          div.onclick = () => {
            const isFromOpenList = pregunta.cerrado !== "0"; // Verifica si no está cerrado
            loadMessages(pregunta.id, isFromOpenList);
          };

          // Agrega el chat a la lista de abiertos o cerrados según corresponda
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

// Función para cargar los mensajes de un chat específico
function loadMessages(preguntaId, isFromOpenList) {
  fetch(
    `../../Controller/chat_ayuda.php?action=get_mensajes&pregunta_id=${preguntaId}`
  )
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        console.log("Mensajes cargados:", data.data);
        // Limpia el contenedor de mensajes
        messagesContainer.innerHTML = "";

        // Muestra u oculta el botón de cerrar chat según la lista
        if (isFromOpenList) {
          document.getElementById("btnCerrar").style.visibility = "hidden";
        }
        else {
          document.getElementById("btnCerrar").style.visibility = "visible";
        }

        // Actualiza el ID de la pregunta seleccionada
        document.getElementById("chatId").value = preguntaId;

        // Recorre y muestra cada mensaje
        data.data.forEach((mensaje) => {
          const div = document.createElement("div");

          // Asigna la clase según si el mensaje es del admin o del usuario
          if (mensaje.admin_name) {
            div.classList.add("message-admin");
          } else {
            div.classList.add("message-user");
          }

          // Crea los elementos internos del mensaje
          const smallText = document.createElement("small");
          smallText.textContent = mensaje.mensaje;

          const strongName = document.createElement("strong");
          strongName.classList.add("senderName");
          strongName.textContent = mensaje.admin_name || ""; // Vacío si es null

          const messageTime = document.createElement("div");
          messageTime.classList.add("message-time");
          messageTime.textContent = mensaje.fecha;

          // Añade los elementos al div del mensaje
          div.appendChild(strongName);
          div.appendChild(smallText);
          div.appendChild(messageTime);

          // Añade el mensaje al contenedor de mensajes
          messagesContainer.appendChild(div);
        });
      } else {
        console.error("No se pudieron cargar los mensajes:", data.message);
      }
    })
    .catch((error) => console.error("Error en la solicitud:", error));
}

// Función para mostrar la lista de chats abiertos o cerrados
function Show(tipo) {
  const OpenListDiv = document.getElementById("OpenList");
  const CloseListDiv = document.getElementById("CloseList");
  const tabOpen = document.getElementById("tab-open");
  const tabClosed = document.getElementById("tab-closed");
  const BtnSend = document.getElementById("btnEnviar");

  // Si se selecciona "abiertos"
  if (tipo === "open" && OpenListDiv.style.display === "none") {
    OpenListDiv.style.display = "block";
    CloseListDiv.style.display = "none";
    messagesContainer.innerHTML = "";
    BtnSend.disabled = false; // Habilita el botón de enviar
    tabOpen.classList.add("active");
    tabClosed.classList.remove("active");
    document.getElementById("btnCerrar").style.visibility = "hidden";

  // Si se selecciona "cerrados"
  } else if (tipo === "close" && CloseListDiv.style.display === "none") {
    OpenListDiv.style.display = "none";
    CloseListDiv.style.display = "block";
    messagesContainer.innerHTML = "";
    BtnSend.disabled = true; // Deshabilita el botón de enviar
    tabOpen.classList.remove("active");
    tabClosed.classList.add("active");
    document.getElementById("btnCerrar").style.visibility = "hidden";
  }
}

// Función para enviar un mensaje en el chat actual
function sendMessage() {
  const messageInput = document.getElementById("message");
  const preguntaId = document.getElementById("chatId").value;

  // Valida que el mensaje no esté vacío
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

  // Prepara los datos a enviar
  const formData = new FormData();
  formData.append("action", "enviar_mensaje");
  formData.append("pregunta_id", preguntaId);
  formData.append("mensaje", messageInput.value);

  // Realiza la petición POST para enviar el mensaje
  fetch("../../Controller/chat_ayuda.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        messageInput.value = ""; // Limpia el campo de mensaje
        loadMessages(preguntaId); // Recarga los mensajes
      } else {
        console.error("Error al enviar el mensaje:", data.message);
      }
    })
    .catch((error) => console.error("Error en la solicitud:", error));
}

// Función para cerrar el chat actual
function closeChat() {
  const preguntaId = document.getElementById("chatId").value;

  if (!preguntaId) {
    console.error("No hay ID de pregunta para cerrar el chat.");
    return;
  }

  // Confirma si el usuario realmente quiere cerrar el chat
  if (!confirm("¿Estás seguro de que quieres cerrar este chat?")) {
    return;
  }

  // Prepara los datos para cerrar el chat
  const formData = new FormData();
  formData.append("action", "cerrar_chat");
  formData.append("pregunta_id", preguntaId);

  // Realiza la petición POST para cerrar el chat
  fetch("../../Controller/chat_ayuda.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        loadChat(); // Recarga la lista de chats
        Show("close"); // Muestra la lista de chats cerrados
        loadMessages(preguntaId, 1); // Limpia los mensajes del chat actual
      } else {
        console.error("Error al cerrar el chat:", data.message);
      }
    })
    .catch((error) => console.error("Error en la solicitud:", error));
}

// Función para abrir el menú lateral
function openMenu() {
  document.getElementById("sideMenu").style.width = "250px";
}

// Función para cerrar el menú lateral
function closeMenu() {
  document.getElementById("sideMenu").style.width = "0";
}