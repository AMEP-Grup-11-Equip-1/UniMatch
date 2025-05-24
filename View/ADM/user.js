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

// Agregar navegación por teclado para el carrusel
document.addEventListener('keydown', (e) => {
  if (e.key === 'ArrowLeft') {
    rotateCarousel(-1);
  } else if (e.key === 'ArrowRight') {
    rotateCarousel(1);
  }
});