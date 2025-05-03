
const carouselContainer = document.querySelector('.carousel');
let currentIndex = 0;
let isThrottled = false;
let islands = [];  // ara és dinàmic
let currentUserId = null;

// Obtenir l'ID de l'usuari via AJAX
fetch('../../Controller/get_session.php')
    .then(res => res.json())
    .then(data => {
        if (data.usuarioID) {
            currentUserId = data.usuarioID;
            console.log("Sessió carregada: ID usuari =", currentUserId);
        } else {
            console.warn("Usuari no loguejat:", data.error);
        }
    });

async function inicializarCarrusel() {
    try {
        const response = await fetch("../../Controller/imprimir_historia.php");
        const perfiles = await response.json();

        if (perfiles.error) {
            console.error(perfiles.error);
            return;
        }

        // Buida el carrusel
        carouselContainer.innerHTML = "";

        // Crea cada island
        perfiles.forEach((perfil) => {
            const island = document.createElement("div");
            island.className = "island";
            island.dataset.id = perfil.id;

            island.innerHTML = `
                <img src="${perfil.imagen}" alt="${perfil.nombre}">
                <div class="profile-info">
                    <div class="profile-name">${perfil.nombre}</div>
                    <div class="profile-university">${perfil.universidad}</div>
                    <div class="profile-description">${perfil.descripcion}</div>
                </div>
                <span class="like-btn" onclick="toggleLike(this)">
                    <span class="material-icons">favorite_border</span>
                </span>
            `;

            carouselContainer.appendChild(island);
        });

        // Reassignem la llista d'islands ja generades
        islands = document.querySelectorAll('.island');

        updateCarousel(true);
    } catch (error) {
        console.error('Error al carregar els perfils:', error);
    }
}


function updateCarousel(instant = false) {
    islands.forEach((island, i) => {
        let position = (i - currentIndex + islands.length) % islands.length;

        island.style.transition = instant ? 'none' : 'transform 1s ease-in-out, opacity 1s ease-in-out';
        island.classList.remove('center');

        if (position === 0) {
            island.style.opacity = '0';
            island.style.transform = 'translateX(-500px) scale(0.6)';
        } else if (position === 1) {
            island.style.opacity = '1';
            island.style.transform = 'translateX(-250px) scale(0.8)';
        } else if (position === 2) {
            island.style.opacity = '1';
            island.style.transform = 'translateX(0px) scale(1.2)';
            island.classList.add('center');
        } else if (position === 3) {
            island.style.opacity = '1';
            island.style.transform = 'translateX(250px) scale(0.8)';
        } else {
            island.style.opacity = '0';
            island.style.transform = 'translateX(500px) scale(0.6)';
        }
    });
}

async function cargarPerfil(id, island) {
    try {
        const response = await fetch(`../../Controller/imprimir_historia.php?id=${id}`);
        const perfil = await response.json();

        if (perfil.error) {
            console.error(perfil.error);
            return;
        }

        island.querySelector('img').src = perfil.imagen;
        island.querySelector('img').alt = perfil.nombre;
        island.querySelector('.profile-name').textContent = perfil.nombre;
        island.querySelector('.profile-university').textContent = perfil.universidad;
        island.querySelector('.profile-description').textContent = perfil.descripcion;
    } catch (error) {
        console.error('Error al cargar el perfil:', error);
    }
}

function rotateCarousel(direction) {
    if (isThrottled || islands.length === 0) return;

    isThrottled = true;
    setTimeout(() => isThrottled = false, 900);

    currentIndex = (currentIndex + direction + islands.length) % islands.length;
    updateCarousel();
}


function toggleProfilePopup() {
    const popup = document.getElementById('profilePopup');
    popup.classList.toggle('show');
}

function openMenu() {
    document.getElementById('sideMenu').style.width = '250px';
}

function closeMenu() {
    document.getElementById('sideMenu').style.width = '0';
}

function eliminarCuenta() {
    if (confirm("¿Estás seguro de que deseas eliminar tu cuenta? Esta acción no se puede deshacer.")) {
        fetch("../../Controller/usercontroller.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                }
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.status === "success") {
                    window.location.href = "../Pantalla_de_Bloqueo/Pantalladebloqueo.html";
                }
            })
            .catch(error => console.error("Error en la solicitud:", error));
    }
}

function toggleNotificationPopup() {
    const popup = document.getElementById('notificationPopup');
    popup.classList.toggle('show');
    if (popup.classList.contains('show')) {
        cargarNotificaciones();
    }
}


function toggleLike(element) {
    const icon = element.querySelector('.material-icons');
    const wasLiked = element.classList.contains('liked');
    element.classList.toggle('liked');

    const island = element.closest('.island');
    const historiaId = island.dataset.id; // Necessitaràs afegir això al HTML

    if (!wasLiked) {
        if (!currentUserId) {
            alert("Has d'iniciar sessió per donar like.");
            return;
        }
        
        icon.textContent = 'favorite';
        heartBeatLong(element);
        

        // ⬇️ Enviar like al servidor
        console.log("Enviant like per historia_id =", historiaId);

        
        fetch('../../Controller/dar_like.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `historia_id=${historiaId}`
        })
        .then(response => response.text())  // <--- Canvia json() per text()
        .then(text => {
            console.log("📄 Text rebut:", text);
            try {
                const data = JSON.parse(text);
                console.log("✅ Like enregistrat:", data);
            } catch (e) {
                console.error("❌ Error parsejant JSON:", e, "\nResposta original:", text);
            }
        
        })
    }

        
}


// Heartbeat llarg (1,7 segons)
function heartBeatLong(btn) {
    btn.classList.remove('heartbeat');
    void btn.offsetWidth;
    btn.classList.add('heartbeat');
    setTimeout(() => btn.classList.remove('heartbeat'), 1750);
}


function cargarNotificaciones() {
    fetch("../../Controller/get_notificaciones.php")
        .then(response => response.json())
        .then(data => {
            const lista = document.querySelector('.notification-list');
            lista.innerHTML = '';

            if (data.data && data.data.length > 0) {
                data.data.forEach(n => {
                    const p = document.createElement('p');
                    p.textContent = `${n.mensaje} - ${new Date(n.fecha).toLocaleString()}`;
                    lista.appendChild(p);
                });
            } else {
                lista.innerHTML = "<p>No tens noves notificacions.</p>";
            }
        })
        .catch(error => console.error("Error carregant notificacions:", error));
}

document.getElementById("openCreateGrup").addEventListener("click", () => {
    document.getElementById("popupCrearGrup").classList.remove("oculto");
  });
  
  function cerrarPopupGrup() {
    document.getElementById("popupCrearGrup").classList.add("oculto");
  }
  
  function guardarGrup() {
    const nom = document.getElementById("grup_nom").value;
    const visibilitat = document.getElementById("grup_visibilitat").value;
    const descripcio = document.getElementById("grup_descripcio").value;
  
    fetch("../../Controller/GrupController.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        accion: "crear_grup",
        nom,
        visibilitat,
        descripcio
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === "ok") {
        alert("Grupo creado correctamente");
        cerrarPopupGrup();
      } else {
        alert("Error al crear grupo");
      }
    });
  }

 // Mostrar u ocultar el popup de crear grupo
// Mostrar popup de grupo
document.getElementById("openCreateGrup").addEventListener("click", toggleCrearGrupo);

function toggleCrearGrupo() {
  const popup = document.getElementById("popupCrearGrupo");
  popup.classList.toggle("show");
}

  
// Llamamos al iniciar
inicializarCarrusel();
