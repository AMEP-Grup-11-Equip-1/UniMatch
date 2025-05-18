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
            // Fallback: añadir al final del chat-list
            chatList.innerHTML += response.consultas_abiertas;
          }
        }
      } catch (e) {
        console.error("Error parsing JSON:", e);
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

// Función para cargar el chat (a implementar)
function loadChat(protocolo) {
//  console.log("Cargando chat para protocolo:", protocolo);
  const xhr = new XMLHttpRequest();
  xhr.open("GET", "../../Controller/ayuda.php?protocolo=" + protocolo, true);
  xhr.onload = function() {
    if (xhr.status === 200) {
      const response = JSON.parse(xhr.responseText);
      const messagesContainer = document.getElementById("messagesContainer");
      messagesContainer.innerHTML = ""; // Limpiar mensajes anteriores
      response.messages.forEach(msg => {
        const messageDiv = document.createElement("div");
       // messageDiv.classList.add("message");

        // Adiciona classe diferente para admin e usuário
        if (msg.isAdmin) {
          messageDiv.classList.add("message-admin");
        } else {
          messageDiv.classList.add("message-user");
        }
// Só mostra a imagem se não for admin
        const imageHtml = msg.isAdmin ? '' : `<img src="${msg.userImage}" alt="User Image">`;
        messageDiv.innerHTML = `${imageHtml}<div class="message-content">${msg.text}</div>`;
        
        messagesContainer.appendChild(messageDiv);
      });
    }
  };
  xhr.send();
}

window.onload = carregarDados;