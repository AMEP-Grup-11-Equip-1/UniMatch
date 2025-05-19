// Global variables for polling control
let currentProtocol = null;
let pollingInterval = null;
let dataPollingInterval = null;
const POLLING_INTERVAL = 3000; // 3 seconds
const DATA_POLLING_INTERVAL = 5000; // 5 seconds for data updates

// Helper function for AJAX requests
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

// Load data with polling
function startDataPolling() {
  stopDataPolling();
  loadData(); // Load immediately
  dataPollingInterval = setInterval(loadData, DATA_POLLING_INTERVAL);
}

function stopDataPolling() {
  if (dataPollingInterval) {
    clearInterval(dataPollingInterval);
    dataPollingInterval = null;
  }
}

async function loadData() {
  const response = await makeRequest("../../Controller/gestion_ayuda.php");
  if (!response) return;

  // Update new messages table
  if (response.tabla_new) {
    document.getElementById("tabla-new").innerHTML = response.tabla_new;
  }
  
  // Update open consultations
  const chatList = document.querySelector(".chat-list");
  if (response.consultas_abiertas && chatList) {
    const title = chatList.querySelector(".section-title");
    if (title) {
      // Remove old consultations (except title)
      const oldElements = [...chatList.children].filter(child => child !== title);
      oldElements.forEach(el => el.remove());
      
      // Add new consultations
      title.insertAdjacentHTML("afterend", response.consultas_abiertas);
    }
  }
}

// Chat functions
function loadChat(protocolo, usuario) {
  stopPolling();
  
  document.getElementById("chatUserInfo").textContent = `(${usuario} - Protocolo #${protocolo})`;
  document.getElementById("ProtocoloId").textContent = protocolo;
  currentProtocol = protocolo;
  
  fetchMessages();
  startPolling();
}

async function fetchMessages() {
  if (!currentProtocol) return;

  const response = await makeRequest(
    `../../Controller/ayuda_ADM.php?protocolo=${currentProtocol}&timestamp=${Date.now()}`
  );
  if (!response?.messages) return;

  const messagesContainer = document.getElementById("messagesContainer");
  const currentScroll = messagesContainer.scrollTop;
  const wasAtBottom = messagesContainer.scrollHeight - messagesContainer.clientHeight <= currentScroll + 50;
  
  messagesContainer.innerHTML = response.messages.map(msg => {
    if (msg.isAdmin) {
      const classes = ["message-admin", ...(msg.isCurrentUser ? ["message-current-user"] : [])].join(" ");
      return `<div class="${classes}">
                <div class="sender-name">${msg.usuario}</div>
                <div class="message-content">${msg.text}</div>
              </div>`;
    } else {
      const imageHtml = msg.userImage ? `<img src="${msg.userImage}" alt="User Image">` : '';
      return `<div class="message-user">
                ${imageHtml}
                <div class="message-content">${msg.text}</div>
              </div>`;
    }
  }).join("");
  
  messagesContainer.scrollTop = wasAtBottom 
    ? messagesContainer.scrollHeight 
    : currentScroll;
}

function startPolling() {
  stopPolling();
  pollingInterval = setInterval(fetchMessages, POLLING_INTERVAL);
}

function stopPolling() {
  if (pollingInterval) {
    clearInterval(pollingInterval);
    pollingInterval = null;
  }
}

async function sendMessage() {
  const message = document.getElementById("message").value.trim();
  const protocolo = document.getElementById("ProtocoloId").textContent;
  
  if (!message) {
    document.getElementById("message").placeholder = "Por favor, escribe un mensaje.";
    return;
  }

  await makeRequest("../../Controller/ayuda_ADM.php", "POST", {
    protocolo,
    mensaje: message
  });
  
  document.getElementById("message").value = "";
  document.getElementById("message").placeholder = "Escribe un mensaje...";
  fetchMessages();
}

// Event listeners
window.addEventListener('load', () => {
  loadData();
  startDataPolling();
});

window.addEventListener('beforeunload', () => {
  stopPolling();
  stopDataPolling();
});

// Make sendMessage available globally if needed
window.sendMessage = sendMessage;