// Função para obter um parâmetro da URL
function getQueryParam(param) {
  const params = new URLSearchParams(window.location.search);
  return params.get(param);
}

// Obtém o ID do usuário da URL
const userId = getQueryParam("id");
let currentSlide = 0;
let stories = [];
let matches = [];

// Cargar datos del usuario
if (!userId) {
  // Si no hay ID, muestra error y limpia campos
  alert("No se especificó el usuario.");
  document.getElementById("user-name").textContent = "Error";
  document.getElementById("user-email").textContent = "";
  document.getElementById("user-status").textContent = "";
} else {
  // Busca los datos del usuario por ID
  fetch(`../../Controller/usuari_per_id.php?id=${userId}`)
    .then((res) => {
      if (!res.ok) throw new Error("Usuario no encontrado");
      return res.json();
    })
    .then((data) => {
      // Ajuste para la nueva estructura del backend
      let usuario = data.data?.usuario || {};
      let verificacion = data.data?.verificacion || {};
      let contenido = data.data?.contenido || [];

      // Rellenar nombre, email y estado SIEMPRE
      document.getElementById("user-name").textContent =
        usuario.nombre || "Sin nombre";
      document.getElementById("user-email").textContent =
        usuario.email || "Sin email";
      if (verificacion.ok == 1) {
        document.getElementById("user-status").textContent = "Verificado";
      } else if (verificacion.ok == 0) {
        document.getElementById("user-status").textContent = "Bloqueado";
      } else {
        document.getElementById("user-status").textContent = "Nuevo usuario";
      }

      // Separar historias y matches
      stories = [];
      matches = [];
      if (Array.isArray(contenido)) {
        contenido.forEach((item) => {
          if (item.origem === "1") {
            stories.push(item);
          } else if (item.origem === "2") {
            matches.push(item);
          }
        });
      }

      // Inicializar carrusel y matches (no da error si está vacío)
      initCarousel();
      initMatches();

      cargarDenuncias();
    })
    .catch((err) => {
      // Si hay error, muestra mensaje y limpia campos
      console.error(err);
      document.getElementById("user-name").textContent =
        "Error al cargar usuario";
      document.getElementById("user-email").textContent = "";
      document.getElementById("user-status").textContent = "";
    });
}

// Inicializa el carrusel con las historias del usuario
function initCarousel() {
  const carousel = document.querySelector(".carousel");

  if (stories.length === 0) {
    // Si no hay historias, muestra mensaje
    carousel.innerHTML =
      '<div class="carousel-slide active"><p>No hay historias disponibles</p></div>';
    return;
  }

  carousel.innerHTML = "";

  stories.forEach((story, index) => {
    const slide = document.createElement("div");
    slide.className = `carousel-slide ${index === 0 ? "active" : ""}`;

    // Si hay imagen, agregarla
    if (story.imagen) {
      const img = document.createElement("img");
      img.src = story.imagen;
      img.alt = "Historia del usuario";
      slide.appendChild(img);
    }

    // Si hay descripción, agregarla
    if (story.descripcion) {
      const caption = document.createElement("div");
      caption.className = "carousel-caption";
      caption.textContent = story.descripcion;
      slide.appendChild(caption);
    }

    carousel.appendChild(slide);
  });
}

// Inicializa la lista de matches del usuario
function initMatches() {
  const matchList = document.querySelector(".match-list");

  if (matches.length === 0) {
    // Si no hay matches, muestra mensaje
    matchList.innerHTML =
      '<li class="match-item"><p>No hay matches disponibles</p></li>';
    return;
  }

  matchList.innerHTML = "";

  matches.forEach((match) => {
    const matchItem = document.createElement("li");
    matchItem.className = "match-item";

    // Personaliza la información del match
    matchItem.innerHTML = `
      <div class="match-info">
        <div class="match-name">${match.nombre_match}</div>
        <div class="match-date">${new Date(
          match.data_match
        ).toLocaleDateString()}</div>
      </div>
    `;

    matchList.appendChild(matchItem);
  });
}

// Rota el carrusel a la izquierda o derecha
function rotateCarousel(direction) {
  const slides = document.querySelectorAll(".carousel-slide");
  if (slides.length === 0) return;

  slides[currentSlide].classList.remove("active");

  currentSlide += direction;

  if (currentSlide >= slides.length) {
    currentSlide = 0;
  } else if (currentSlide < 0) {
    currentSlide = slides.length - 1;
  }

  slides[currentSlide].classList.add("active");
}

// Envía el estado del usuario (verificado/bloqueado)
function enviarEstado(valor) {
  if (!userId) {
    alert("ID del usuario no encontrado.");
    return;
  }

  fetch("../../Controller/usuari_per_id.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `id=${encodeURIComponent(userId)}&estado=${encodeURIComponent(
      valor
    )}`,
  })
    .then((res) => res.json())
    .then((data) => {
      alert(data.message || "Operación realizada");
      window.location.href = "home.html";
    })
    .catch((err) => {
      alert("Error al enviar estado.");
      console.error(err);
    });
}

// Carga las denuncias recibidas y hechas por el usuario
function cargarDenuncias() {
  if (!userId) return;

  fetch(`../../Controller/denuncias_per_id.php?id=${userId}`)
    .then((res) => res.json())
    .then((denuncias) => {
      const recebidasList = document.getElementById("denuncias-rec");
      const feitasList = document.getElementById("denuncias-hechas");

      // Limpiar listas existentes
      recebidasList.innerHTML = "";
      feitasList.innerHTML = "";

      // Rellena denuncias recibidas
      if (denuncias.denuncias_rec.length > 0) {
        denuncias.denuncias_rec.forEach((denuncia) => {
          const item = document.createElement("li");
          item.className = "denuncia-item";
          item.innerHTML = `
                        <div class="denuncia-header">
                            <span class="denuncia-user">${
                              denuncia.denunciante
                            }</span>
                            <span class="denuncia-date">${new Date(
                              denuncia.data
                            ).toLocaleDateString()}</span>
                        </div>
                        <div class="denuncia-type">${
                          denuncia.tipo_denuncia
                        } -> ${denuncia.historia_id ? "História" : "Chat"}</div>
                    `;

          item.addEventListener("click", () => {
            ShowInfosDenun(denuncia.denuncia_id);
          });
          recebidasList.appendChild(item);
        });
      } else {
        recebidasList.innerHTML =
          '<li class="denuncia-item">No ha recebido ninguna denuncia.</li>';
      }

      // Rellena denuncias hechas
      if (denuncias.denuncias_hecha.length > 0) {
        denuncias.denuncias_hecha.forEach((denuncia) => {
          const item = document.createElement("li");
          item.className = "denuncia-item";
          item.innerHTML = `
                        <div class="denuncia-header">
                            <span class="denuncia-user">${
                              denuncia.denunciado
                            }</span>
                            <span class="denuncia-date">${new Date(
                              denuncia.data
                            ).toLocaleDateString()}</span>
                            
                        </div>
                        <div class="denuncia-type">${
                          denuncia.tipo_denuncia
                        } -> ${denuncia.historia_id ? "História" : "Chat"}</div>
                    `;

          item.addEventListener("click", () => {
            ShowInfosDenun(denuncia.denuncia_id);
          });
          feitasList.appendChild(item);
        });
      } else {
        feitasList.innerHTML =
          '<li class="denuncia-item">No ha realizado ninguna denuncia</li>';
      }
    })
    .catch((err) => {
      // Si hay error, muestra mensaje en la lista
      console.error("Erro ao carregar denúncias:", err);
      document.getElementById("denuncias-recebidas").innerHTML =
        '<li class="denuncia-item">Erro ao carregar denúncias</li>';
    });
}

// Muestra la pestaña de denuncias recibidas o hechas
function mostrarDenuncias(tipo) {
  document.getElementById("denuncias-rec").style.display = "none";
  document.getElementById("denuncias-hechas").style.display = "none";
  document
    .querySelectorAll(".tab-button")
    .forEach((btn) => btn.classList.remove("active"));

  if (tipo === "recebidas") {
    document.getElementById("denuncias-rec").style.display = "block";
    document.querySelector(".tab-button:first-child").classList.add("active");
  } else {
    document.getElementById("denuncias-hechas").style.display = "block";
    document.querySelector(".tab-button:last-child").classList.add("active");
  }
}

let selectedDenunciaId = null;


async function ShowInfosDenun(denuncia_id) {
  try {
    // Validação do ID
    if (!denuncia_id || isNaN(denuncia_id) || denuncia_id <= 0) {
      console.error("ID de denúncia inválido", denuncia_id);
      alert("ID de denúncia inválido");
      return;
    }

      selectedDenunciaId = denuncia_id;

  
    // Buscar dados da denúncia
    const response = await fetch(
      `../../Controller/denuncias_infos.php?id=${denuncia_id}`
    );

    if (!response.ok) {
      const errorData = await response.json().catch(() => null);
      const errorMsg = errorData?.message || `Erro HTTP: ${response.status}`;
      throw new Error(errorMsg);
    }

    const denuncia = await response.json();

          console.log(denuncia);

              // pega a div do painel (must exist no HTML)
    const detalhes = document.querySelector(".denuncia-detalhes");
    if (!detalhes) throw new Error("Falta .denuncia-detalhes no HTML");



        // === LIMPA O QUE TIVER SIDO CRIADO ANTES ===
    const chatOld = detalhes.querySelector("#chat-box");
    if (chatOld) chatOld.remove();
    const fotosOld = detalhes.querySelector("#caixa-fotos");
    if (fotosOld) fotosOld.remove();

const span = document.getElementById("status-denuncia");
if (denuncia.status === 1) {
  span.textContent = "Aprobado";
} else if (denuncia.status === 0) {
  span.textContent = "Rechazado";
} else {
  span.textContent = "Pendiente";
}


    // Função auxiliar para definir conteúdo seguro
    const setContent = (id, value, defaultValue = "-") => {
      const element = document.getElementById(id);
      if (element) {
        element.textContent = value !== null && value !== undefined ? value : defaultValue;
      }
    };

    /*
    if (!denuncia.status){
      document.getElementById(status-denuncia).text="pendiente"
    }
    else if (denuncia.status === 1) {
     document.getElementById(status-denuncia).text="aprobado"
    }
    else{
      document.getElementById(status-denuncia).text="denegado"
    }
      */

   if (denuncia.historia_id === null) {
      // ===== CHAT =====
      const msgRes = await fetch(
        `../../Controller/get_msg_adm.php` +
        `?denunciante_id=${denuncia.denunciante_id}` +
        `&receptor_id=${denuncia.denunciado_id}`
      );
      const data = await msgRes.json();
      console.log("mensagens recebidas:", data);

      // Cria container de chat
      const chatBox = document.createElement("div");
      chatBox.id = "chat-box";
      detalhes.appendChild(chatBox);

      if (data.success && data.mensajes.length > 0) {
        let i = 0;
        while (i < data.mensajes.length) {
          const msg = data.mensajes[i++];
          const msgDiv = document.createElement("div");
          msgDiv.className = msg.emisor === denuncia.denunciante_id
            ? "message-user"
            : "message-admin";

          const textDiv = document.createElement("div");
          textDiv.className = "message-text";
          textDiv.textContent = msg.mensaje;

          const timeDiv = document.createElement("div");
          timeDiv.className = "message-time";
          timeDiv.textContent = new Date(msg.fecha)
            .toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });

          msgDiv.append(textDiv, timeDiv);
          chatBox.appendChild(msgDiv);
        }
      } else {
        chatBox.textContent = "Não há mensagens entre estes usuários.";
      }

    } else {
      // ===== STORY =====
      const fotosContainer = document.createElement("div");
      fotosContainer.id = "caixa-fotos";
      detalhes.appendChild(fotosContainer);

      if (denuncia.imagem_historia) {
        const img = document.createElement("img");
        img.src = denuncia.imagem_historia;
        img.alt = "História do usuário";
        fotosContainer.appendChild(img);
      } else {
        fotosContainer.textContent = "Nenhuma foto disponível.";
      }
    }


       // fotosContainer.appendChild(box);

    // Mostrar painel de detalhes
    const detailsPanel = document.querySelector(".denuncia-detalhes");
    if (detailsPanel) {
      detailsPanel.style.display = "block";
    }

  } catch (error) {
    console.error("Erro ao carregar denúncia:", error);
    alert(`Não foi possível carregar os detalhes: ${error.message}`);
  } finally {
    // Esconder estado de carregamento
  }
}

function DenEstado(index) {
  if (!selectedDenunciaId) {
    alert("Nenhuma denúncia selecionada.");
    return;
  }

  fetch("../../Controller/denuncias_infos.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `id=${encodeURIComponent(selectedDenunciaId)}&status=${encodeURIComponent(index)}`
  })
    .then(res => res.json())
    .then(data => {
      if (data.status === "success") {
        alert(data.message || "Estado atualizado com sucesso.");
        // atualizar o texto do status na interface, se tiver um elemento <span id="status-denuncia">
        const span = document.getElementById("status-denuncia");
        if (span) {
          span.textContent = index === 1 ? "Aprobado" : "Rechazado";
        }
        // opcional: recarregar a lista ou fechar o painel de detalhes
        // window.location.reload();
      } else {
        alert(data.message || "Erro ao atualizar estado.");
      }
    })
    .catch(err => {
      console.error(err);
      alert("Erro na comunicação com o servidor.");
    });
}