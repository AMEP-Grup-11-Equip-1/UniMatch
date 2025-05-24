// Variables globales para el control del polling (actualización periódica)
let currentProtocol = null; // Almacena el protocolo del chat actual
let pollingInterval = null; // Intervalo para actualización de mensajes
let dataPollingInterval = null; // Intervalo para actualización de datos generales
const POLLING_INTERVAL = 3000; // Intervalo de 3 segundos para mensajes del chat
const DATA_POLLING_INTERVAL = 5000; // Intervalo de 5 segundos para datos generales

// Función auxiliar para realizar peticiones AJAX (fetch)
// Permite enviar datos por GET o POST y devuelve la respuesta en JSON
async function makeRequest(url, method = "GET", data = null) {
  try {
    const options = { method };
    if (data) {
      options.body = new URLSearchParams(data);
      options.headers = { "Content-Type": "application/x-www-form-urlencoded" };
    }

    const response = await fetch(url, options);
    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
    return await response.json();
  } catch (error) {
    console.error("Request failed:", error);
    return null;
  }
}

// Inicia el polling para actualizar los datos generales (consultas, tablas)
function startDataPolling() {
  stopDataPolling();
  loadData(); // Carga inicial inmediata
  dataPollingInterval = setInterval(loadData, DATA_POLLING_INTERVAL);
}

// Detiene el polling de datos generales
function stopDataPolling() {
  if (dataPollingInterval) {
    clearInterval(dataPollingInterval);
    dataPollingInterval = null;
  }
}

// Carga los datos generales de la ayuda (consultas nuevas, abiertas, etc.)
async function loadData() {
  const response = await makeRequest("../../Controller/gestion_ayuda.php");
  if (!response) return;

  // Actualiza la tabla de nuevas consultas
  if (response.tabla_new) {
    document.getElementById("NewList").innerHTML = response.tabla_new;
  }

  // Limpia las listas antes de rellenarlas
  OpenListDiv.innerHTML = "";
  ClosedListDiv.innerHTML = "";

  // Rellena OpenListDiv y ClosedListDiv según el atributo data-cerrado
  if (response.consultas_abiertas) {
    const tempDiv = document.createElement("div");
    tempDiv.innerHTML = response.consultas_abiertas;

    Array.from(tempDiv.children).forEach((child) => {
      const cerrado = child.getAttribute("data-cerrado");
      if (cerrado === "0") {
        // Consulta abierta
        OpenListDiv.appendChild(child);
      } else if (cerrado === "1") {
        // Consulta cerrada
        ClosedListDiv.appendChild(child);
      }
    });
  }
}

// Función para cargar el chat de un protocolo específico
function loadChat(protocolo, usuario) {
  // Verifica si el protocolo es válido
  stopPolling(); // Detiene el polling anterior si existe

  // Muestra la información del usuario y protocolo en el encabezado del chat
  document.getElementById(
    "chatUserInfo"
  ).textContent = `(${usuario} - Protocolo #${protocolo})`;
  document.getElementById("ProtocoloId").textContent = protocolo;
  currentProtocol = protocolo;

  document.getElementById("chat-box").style.display = "flex"; // Muestra el cuadro de chat

  // Verificación de seguridad: habilita el botón cerrar chat solo si el protocolo está abierto
  if (closeBtn) {
    const chatItem = document.querySelector(`.chat-item[data-cerrado][onclick*="loadChat(${protocolo},"]`);
    const cerrado = chatItem ? chatItem.getAttribute("data-cerrado") : "1";
    closeBtn.disabled = cerrado !== "0";
  }

  fetchMessages(); // Carga los mensajes actuales
  startPolling(); // Inicia el polling para mensajes del chat
}

// Función para obtener los mensajes del chat actual
async function fetchMessages() {
  if (!currentProtocol) return;

  // Solicita los mensajes del protocolo actual, agregando timestamp para evitar caché
  const response = await makeRequest(
    `../../Controller/ayuda_ADM.php?protocolo=${currentProtocol}&timestamp=${Date.now()}`
  );
  if (!response?.messages) return;

  const messagesContainer = document.getElementById("messagesContainer");
  const currentScroll = messagesContainer.scrollTop;
  // Verifica si el usuario está al final del scroll para mantener la posición
  const wasAtBottom =
    messagesContainer.scrollHeight - messagesContainer.clientHeight <=
    currentScroll + 50;

  // Renderiza los mensajes en el contenedor
  messagesContainer.innerHTML = response.messages
    .map((msg) => {
      if (msg.isAdmin) {
        // Mensaje del administrador
        const classes = [
          "message-admin",
          ...(msg.isCurrentUser ? ["message-current-user"] : []),
        ].join(" ");
        return `<div class="${classes}">
                <div class="sender-name">${msg.usuario}</div>
                <div class="message-content">${msg.text}</div>
                <div class="message-time">${msg.fecha}</div>
              </div>`;
      } else {
        // Mensaje del usuario, puede incluir imagen
        const imageHtml = msg.userImage
          ? `<img src="${msg.userImage}" alt="User Image">`
          : "";
        return `<div class="message-user">
                ${imageHtml}
                <div class="message-content">${msg.text}</div>
                <div class="message-time">${msg.fecha}</div>
              </div>`;
      }
    })
    .join("");

  // Mantiene el scroll abajo si el usuario ya estaba al final
  messagesContainer.scrollTop = wasAtBottom
    ? messagesContainer.scrollHeight
    : currentScroll;
}

// Inicia el polling para actualizar los mensajes del chat
function startPolling() {
  stopPolling();
  pollingInterval = setInterval(fetchMessages, POLLING_INTERVAL);
}

// Detiene el polling de mensajes del chat
function stopPolling() {
  if (pollingInterval) {
    clearInterval(pollingInterval);
    pollingInterval = null;
  }
}

// Envía un mensaje en el chat actual
let sending = false;

async function sendMessage() {
  if (sending) return; 
  sending = true;

  const message = document.getElementById("message").value.trim();
  const protocolo = document.getElementById("ProtocoloId").textContent;

  if (!message) {
    document.getElementById("message").placeholder = "Por favor, escribe un mensaje.";
    sending = false;
    return;
  }

  await makeRequest("../../Controller/ayuda_ADM.php", "POST", {
    protocolo,
    mensaje: message,
  });

  document.getElementById("message").value = "";
  document.getElementById("message").placeholder = "Escribe un mensaje...";
  fetchMessages();
  loadData();

  sending = false;
}

// Listeners de eventos para cargar datos y limpiar intervalos al salir
window.addEventListener("load", () => {
  loadData();
  startDataPolling();
});

window.addEventListener("beforeunload", () => {
  stopPolling();
  stopDataPolling();
});

// Función para cerrar el chat actual
function closeChat() {
  const opcion = confirm("¿Estás seguro de que deseas cerrar el chat?");
  if (opcion) {
    makeRequest("../../Controller/cerrar_chat.php", "POST", {
      protocolo: currentProtocol,
    })
      .then((data) => {
        if (data && data.success) {
          console.log("Chat cerrado exitosamente.");
          // Actualiza la interfaz según sea necesario
          loadData(); // Refresca las listas
          stopPolling(); // Detiene el polling para este chat
          document.getElementById("chat-box").style.display = "none"; // Oculta el cuadro de chat
        } else {
          console.error(
            "Error al cerrar el chat:",
            data?.error || "Unknown error"
          );
        }
      })
      .catch((error) => {
        console.error("Request failed:", error);
      });
  }
}

// Elementos de la interfaz para las pestañas
const tabNew = document.getElementById("tab-new");
const tabOpen = document.getElementById("tab-open");
const tabClosed = document.getElementById("tab-closed");

const NewListDiv = document.getElementById("NewList");
const OpenListDiv = document.getElementById("OpenList");
const ClosedListDiv = document.getElementById("ClosedList");

const closeBtn = document.getElementById("closeChatBtn");

// Desabilita o botão de fechar chat por padrão
if (closeBtn) {
  closeBtn.disabled = true;
}


// Cambia la pestaña activa y carga los datos correspondientes
tabNew.addEventListener("click", () => {
  tabNew.classList.add("active");
  tabOpen.classList.remove("active");
  tabClosed.classList.remove("active");
  NewListDiv.style.display = "block";
  OpenListDiv.style.display = "none";
  ClosedListDiv.style.display = "none";
  loadData();
});

tabOpen.addEventListener("click", () => {
  tabNew.classList.remove("active");
  tabOpen.classList.add("active");
  tabClosed.classList.remove("active");
  NewListDiv.style.display = "none";
  OpenListDiv.style.display = "block";
  ClosedListDiv.style.display = "none";
  loadData();
});

tabClosed.addEventListener("click", () => {
  tabNew.classList.remove("active");
  tabOpen.classList.remove("active");
  tabClosed.classList.add("active");
  NewListDiv.style.display = "none";
  OpenListDiv.style.display = "none";
  ClosedListDiv.style.display = "block";
  loadData();
});

// Hace la función sendMessage accesible globalmente (por ejemplo, desde el HTML)
window.sendMessage = sendMessage;