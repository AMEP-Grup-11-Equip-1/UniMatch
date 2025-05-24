function getQueryParam(param) {
  const params = new URLSearchParams(window.location.search);
  return params.get(param);
}

const userId = getQueryParam("id");
let currentSlide = 0;
let stories = [];
let matches = [];

// Cargar datos del usuario
if (!userId) {
  alert("No se especificó el usuario.");
  document.getElementById("user-name").textContent = "Error";
  document.getElementById("user-email").textContent = "";
  document.getElementById("user-status").textContent = "";
} else {
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
      document.getElementById("user-name").textContent = usuario.nombre || "Sin nombre";
      document.getElementById("user-email").textContent = usuario.email || "Sin email";
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
      console.error(err);
      // Rellenar nombre/email/estado como vacío solo si no vino nada del backend
      document.getElementById("user-name").textContent = "Error al cargar usuario";
      document.getElementById("user-email").textContent = "";
      document.getElementById("user-status").textContent = "";
    });
}

// Inicializar carrusel con historias
function initCarousel() {
  const carousel = document.querySelector('.carousel');
  
  if (stories.length === 0) {
    carousel.innerHTML = '<div class="carousel-slide active"><p>No hay historias disponibles</p></div>';
    return;
  }
  
  carousel.innerHTML = '';
  
  stories.forEach((story, index) => {
    const slide = document.createElement('div');
    slide.className = `carousel-slide ${index === 0 ? 'active' : ''}`;
    
    // Si hay imagen, agregarla
    if (story.imagen) {
      const img = document.createElement('img');
      img.src = story.imagen;
      img.alt = "Historia del usuario";
      slide.appendChild(img);
    }
    
    // Si hay descripción, agregarla
    if (story.descripcion) {
      const caption = document.createElement('div');
      caption.className = 'carousel-caption';
      caption.textContent = story.descripcion;
      slide.appendChild(caption);
    }
    
    carousel.appendChild(slide);
  });
}

// Inicializar lista de matches
function initMatches() {
  const matchList = document.querySelector('.match-list');
  
  if (matches.length === 0) {
    matchList.innerHTML = '<li class="match-item"><p>No hay matches disponibles</p></li>';
    return;
  }
  
  matchList.innerHTML = '';
  
  matches.forEach(match => {
    const matchItem = document.createElement('li');
    matchItem.className = 'match-item';
    
    // Puedes personalizar esto con los datos reales del match
    matchItem.innerHTML = `
      <div class="match-info">
        <div class="match-name">${match.nombre_match}</div>
        <div class="match-date">${new Date(match.data_match).toLocaleDateString()}</div>
      </div>
    `;
    
    matchList.appendChild(matchItem);
  });
}

// Rotar carrusel
function rotateCarousel(direction) {
  const slides = document.querySelectorAll('.carousel-slide');
  if (slides.length === 0) return;
  
  slides[currentSlide].classList.remove('active');
  
  currentSlide += direction;
  
  if (currentSlide >= slides.length) {
    currentSlide = 0;
  } else if (currentSlide < 0) {
    currentSlide = slides.length - 1;
  }
  
  slides[currentSlide].classList.add('active');
}

// Función para enviar el estado del usuario
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
    body: `id=${encodeURIComponent(userId)}&estado=${encodeURIComponent(valor)}`,
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


// Adicione esta função no seu user.js
function cargarDenuncias() {
    if (!userId) return;

    fetch(`../../Controller/denuncias_per_id.php?id=${userId}`)
        .then(res => res.json())
        .then(denuncias => {
            const recebidasList = document.getElementById('denuncias-recebidas');
            const feitasList = document.getElementById('denuncias-feitas');

            // Limpar listas existentes
            recebidasList.innerHTML = '';
            feitasList.innerHTML = '';

            // Preencher denúncias recebidas
            if (denuncias.denuncias_recebidas.length > 0) {
                denuncias.denuncias_recebidas.forEach(denuncia => {
                    const item = document.createElement('li');
                    item.className = 'denuncia-item';
                    item.innerHTML = `
                        <div class="denuncia-header">
                            <span class="denuncia-user">${denuncia.denunciante}</span>
                            <span class="denuncia-date">${new Date(denuncia.data).toLocaleDateString()}</span>
                        </div>
                        <div class="denuncia-type">${denuncia.tipo_denuncia}</div>
                    `;
                    recebidasList.appendChild(item);
                });
            } else {
                recebidasList.innerHTML = '<li class="denuncia-item">Nenhuma denúncia recebida</li>';
            }

            // Preencher denúncias feitas
            if (denuncias.denuncias_feitas.length > 0) {
                denuncias.denuncias_feitas.forEach(denuncia => {
                    const item = document.createElement('li');
                    item.className = 'denuncia-item';
                    item.innerHTML = `
                        <div class="denuncia-header">
                            <span class="denuncia-user">${denuncia.denunciado}</span>
                            <span class="denuncia-date">${new Date(denuncia.data).toLocaleDateString()}</span>
                        </div>
                        <div class="denuncia-type">${denuncia.tipo_denuncia}</div>
                    `;
                    feitasList.appendChild(item);
                });
            } else {
                feitasList.innerHTML = '<li class="denuncia-item">Nenhuma denúncia feita</li>';
            }
        })
        .catch(err => {
            console.error('Erro ao carregar denúncias:', err);
            document.getElementById('denuncias-recebidas').innerHTML = 
                '<li class="denuncia-item">Erro ao carregar denúncias</li>';
        });
}

// Adicione esta função para alternar entre as abas
function mostrarDenuncias(tipo) {
    document.getElementById('denuncias-recebidas').style.display = 'none';
    document.getElementById('denuncias-feitas').style.display = 'none';
    document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
    
    if (tipo === 'recebidas') {
        document.getElementById('denuncias-recebidas').style.display = 'block';
        document.querySelector('.tab-button:first-child').classList.add('active');
    } else {
        document.getElementById('denuncias-feitas').style.display = 'block';
        document.querySelector('.tab-button:last-child').classList.add('active');
    }
}

// Agregar navegación por teclado para el carrusel
document.addEventListener('keydown', (e) => {
  if (e.key === 'ArrowLeft') {
    rotateCarousel(-1);
  } else if (e.key === 'ArrowRight') {
    rotateCarousel(1);
  }
});