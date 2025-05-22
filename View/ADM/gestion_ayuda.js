// Variables globales para el control del polling (actualización periódica)
let currentProtocol = null;
let pollingInterval = null;
let dataPollingInterval = null;
const POLLING_INTERVAL = 3000; // 3 segundos para mensajes del chat
const DATA_POLLING_INTERVAL = 5000; // 5 segundos para datos generales

// Función auxiliar para realizar peticiones AJAX (fetch)
// Permite enviar datos por GET o POST y devuelve la respuesta en JSON
async function makeRequest(url, method = 'GET', data = null) {
  try {
    const options = { method };
    if (data) {
      options.body = new URLSearchParams(data);
      options.headers = { 'Content-Type': 'application/x-www-form-urlencoded' };
    }
    
    const response = await fetch(url, options);
    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
    return await response.json();
  } catch (error) {
    console.error('Request failed:', error);
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

  // Actualiza la tabla de nuevos mensajes si existe en la respuesta
  if (response.tabla_new) {
    document.getElementById("tabla-new").innerHTML = response.tabla_new;
  }
  
  // Actualiza la lista de consultas abiertas en el panel lateral
  const chatList = document.querySelector(".chat-list");
  if (response.consultas_abiertas && chatList) {
    const title = chatList.querySelector(".section-title");
    if (title) {
      // Elimina elementos antiguos (excepto el título)
      const oldElements = [...chatList.children].filter(child => child !== title);
      oldElements.forEach(el => el.remove());
      
      // Inserta las nuevas consultas abiertas después del título
      title.insertAdjacentHTML("afterend", response.consultas_abiertas);
    }
  }
}

// Función para cargar el chat de un protocolo específico
function loadChat(protocolo, usuario) {
  stopPolling(); // Detiene el polling anterior si existe
  
  // Muestra la información del usuario y protocolo en el encabezado del chat
  document.getElementById("chatUserInfo").textContent = `(${usuario} - Protocolo #${protocolo})`;
  document.getElementById("ProtocoloId").textContent = protocolo;
  currentProtocol = protocolo;
  
  fetchMessages(); // Carga los mensajes actuales
  startPolling();  // Inicia el polling para mensajes del chat
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
  const wasAtBottom = messagesContainer.scrollHeight - messagesContainer.clientHeight <= currentScroll + 50;
  
  // Renderiza los mensajes en el contenedor
  messagesContainer.innerHTML = response.messages.map(msg => {
    if (msg.isAdmin) {
      // Mensaje del administrador
      const classes = ["message-admin", ...(msg.isCurrentUser ? ["message-current-user"] : [])].join(" ");
      return `<div class="${classes}">
                <div class="sender-name">${msg.usuario}</div>
                <div class="message-content">${msg.text}</div>
                <div class="message-time">${msg.fecha}</div>
              </div>`;
    } else {
      // Mensaje del usuario, puede incluir imagen
      const imageHtml = msg.userImage ? `<img src="${msg.userImage}" alt="User Image">` : '';
      return `<div class="message-user">
                ${imageHtml}
                <div class="message-content">${msg.text}</div>
                <div class="message-time">${msg.fecha}</div>
              </div>`;
    }
  }).join("");
  
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
async function sendMessage() {
  const message = document.getElementById("message").value.trim();
  const protocolo = document.getElementById("ProtocoloId").textContent;
  
  // Valida que el mensaje no esté vacío
  if (!message) {
    document.getElementById("message").placeholder = "Por favor, escribe un mensaje.";
    return;
  }

  // Envía el mensaje al servidor
  await makeRequest("../../Controller/ayuda_ADM.php", "POST", {
    protocolo,
    mensaje: message
  });
  
  // Limpia el campo de texto y actualiza los mensajes y datos
  document.getElementById("message").value = "";
  document.getElementById("message").placeholder = "Escribe un mensaje...";
  fetchMessages();
  loadData();
}

// Listeners de eventos para cargar datos y limpiar intervalos al salir
window.addEventListener('load', () => {
  loadData();
  startDataPolling();
});

window.addEventListener('beforeunload', () => {
  stopPolling();
  stopDataPolling();
});

// Hace la función sendMessage accesible globalmente (por ejemplo, desde el HTML)
window.sendMessage = sendMessage;