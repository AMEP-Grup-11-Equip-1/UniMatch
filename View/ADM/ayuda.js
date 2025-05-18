// Variables globales para el control del polling
let currentProtocol = null;
let pollingInterval = null;
const POLLING_INTERVAL = 3000; // 3 segundos (ajusta según sea necesario)

// Función para cargar los datos de verificación y denuncias
function carregarDados() {
  const xhr = new XMLHttpRequest();
  xhr.open("GET", "../../Controller/gestion_ayuda.php", true);
  xhr.onload = function() {
    if (xhr.status === 200) {
      try {
        const response = JSON.parse(xhr.responseText);
        
        // Insertar la tabla de nuevos mensajes
        if (response.tabla_new) {
          document.getElementById("tabla-new").innerHTML = response.tabla_new;
        }
        
        // Insertar las consultas abiertas
        const chatList = document.querySelector(".chat-list");
        if (response.consultas_abiertas && chatList) {
          // Encontrar el elemento después del título "Consultas Abiertas"
          const title = chatList.querySelector(".section-title");
          if (title && title.nextSibling) {
            // Insertar después del título
            title.insertAdjacentHTML("afterend", response.consultas_abiertas);
          } else {
            // Alternativa: añadir al final del chat-list
            chatList.innerHTML += response.consultas_abiertas;
          }
        }
      } catch (e) {
        console.error("Error al analizar el JSON:", e);
      }
    } else {
      console.error("Error en la solicitud:", xhr.status, xhr.statusText);
    }
  };
  xhr.onerror = function() {
    console.error("Fallo en la conexión");
  };
  xhr.send();
}

// Función para cargar el chat con polling automático
function loadChat(protocolo, usuario) {
  // Detener cualquier polling anterior
  stopPolling();
  
  // Actualiza el título del chat con nombre y protocolo
  document.getElementById("chatUserInfo").textContent = `(${usuario} - Protocolo #${protocolo})`;
  document.getElementById("ProtocoloId").textContent = protocolo;

  // Almacenar el protocolo actual
  currentProtocol = protocolo;
  
  // Cargar mensajes inmediatamente
  fetchMessages();
  
  // Iniciar polling para actualización automática
  startPolling();
}

// Función para buscar mensajes del chat
function fetchMessages() {
  if (!currentProtocol) return;

  const xhr = new XMLHttpRequest();
  xhr.open("GET", "../../Controller/ayuda.php?protocolo=" + currentProtocol + "&timestamp=" + Date.now(), true);
  xhr.onload = function() {
    if (xhr.status === 200) {
      try {
        const response = JSON.parse(xhr.responseText);
        const messagesContainer = document.getElementById("messagesContainer");
        
        // Guardar la posición de scroll actual
        const currentScroll = messagesContainer.scrollTop;
        const wasAtBottom = messagesContainer.scrollHeight - messagesContainer.clientHeight <= currentScroll + 50;
        
        // Limpiar mensajes anteriores
        messagesContainer.innerHTML = "";
        
        // Añadir todos los mensajes al contenedor
        response.messages.forEach(msg => {
          const messageDiv = document.createElement("div");
          
          if (msg.isAdmin) {
            messageDiv.innerHTML = `<div class="sender-name">${msg.usuario}</div><div class="message-content">${msg.text}</div>`;
            messageDiv.classList.add("message-admin");
            
            if (msg.isCurrentUser) {
              messageDiv.classList.add("message-current-user");
            }
          } else {
            const imageHtml = msg.userImage ? `<img src="${msg.userImage}" alt="User Image">` : '';
            messageDiv.innerHTML = `${imageHtml}<div class="message-content">${msg.text}</div>`;
            messageDiv.classList.add("message-user");
          }
          
          messagesContainer.appendChild(messageDiv);
        });
        
        // Mantener la posición de scroll o ir al final si estaba al final
        if (wasAtBottom) {
          messagesContainer.scrollTop = messagesContainer.scrollHeight;
        } else {
          messagesContainer.scrollTop = currentScroll;
        }
      } catch (e) {
        console.error("Error al analizar el JSON de mensajes:", e);
      }
    }
  };
  xhr.onerror = function() {
    console.error("Error al buscar mensajes");
  };
  xhr.send();
}

// Función para iniciar el polling automático del chat
function startPolling() {
  // Verificar si ya existe un intervalo activo
  if (pollingInterval) {
    clearInterval(pollingInterval);
  }
  
  // Iniciar nuevo intervalo para actualizar mensajes
  pollingInterval = setInterval(fetchMessages, POLLING_INTERVAL);
}

// Función para detener el polling automático del chat
function stopPolling() {
  if (pollingInterval) {
    clearInterval(pollingInterval);
    pollingInterval = null;
  }
}

// Función para enviar mensaje desde el chat
function sendMessage() {
  const message = document.getElementById("message").value;
  const protocolo = document.getElementById("ProtocoloId").textContent;
  if (message.trim() === "") {
    document.getElementById("message").placeholder = "Por favor, escribe un mensaje.";
    return;
  }

  const xhr = new XMLHttpRequest();
  xhr.open("POST", "../../Controller/ayuda.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
  xhr.onload = function() {
    if (xhr.status === 200) {
      fetchMessages(); // Actualiza el chat después de enviar
    }
  };
  // Enviar protocolo y mensaje (el id_adm se recupera en PHP por la sesión)
  xhr.send("protocolo=" + encodeURIComponent(protocolo) + 
           "&mensaje=" + encodeURIComponent(message));

  document.getElementById("message").value = ""; // Limpiar el campo de entrada
  document.getElementById("message").placeholder = "Escribe un mensaje...";
}

// Detener polling cuando la página se cierre
window.addEventListener('beforeunload', stopPolling);

// Iniciar la carga de datos cuando la página cargue
window.onload = carregarDados;