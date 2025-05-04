const carouselContainer = document.querySelector('.carousel');
let currentIndex = 0;
let isThrottled = false;
let islands = [];
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

        carouselContainer.innerHTML = "";

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
    const historiaId = island.dataset.id;

    if (!wasLiked) {
        if (!currentUserId) {
            alert("Has d'iniciar sessió per donar like.");
            return;
        }

        icon.textContent = 'favorite';
        heartBeatLong(element);

        fetch('../../Controller/dar_like.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `historia_id=${historiaId}`
        })
        .then(response => response.text())
        .then(text => {
            console.log("📄 Text rebut:", text);
            try {
                const data = JSON.parse(text);
                console.log("✅ Like enregistrat:", data);
            } catch (e) {
                console.error("❌ Error parsejant JSON:", e, "\nResposta original:", text);
            }
        });
    }
}

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
            lista.innerHTML = ''; // Limpiamos cualquier contenido previo.

            if (data.data && data.data.length > 0) {
                data.data.forEach(n => {
                    // Crear el contenedor de la notificación
                    const notificationContainer = document.createElement('div');
                    notificationContainer.classList.add('notification-container');
                    notificationContainer.setAttribute('data-id', n.id);  // Agregar el ID de la notificación

                    // Contenido de la notificación (mensaje y fecha en el mismo contenedor)
                    const notificationContent = document.createElement('div');
                    notificationContent.classList.add('notification-content');
                    notificationContent.innerHTML = `
                    <p><strong>${n.mensaje}</strong></p>
                    <p style="color: #777;">${new Date(n.fecha).toLocaleString()}</p>
                `;
                


                    // Botones para aceptar o rechazar (centrados y ocupando toda la isla)
                    const notificationActions = document.createElement('div');
                    notificationActions.classList.add('notification-actions');
                    notificationActions.innerHTML = `
                        <button class="accept-btn" onclick="aceptarNotificacion(${n.id})">Aceptar</button>
                        <button class="reject-btn" onclick="rechazarNotificacion(${n.id})">Rechazar</button>
                    `;

                    // Agregar el contenido y los botones al contenedor de la notificación
                    notificationContainer.appendChild(notificationContent);
                    notificationContainer.appendChild(notificationActions);

                    // Añadir la notificación al listado
                    lista.appendChild(notificationContainer);
                });
            } else {
                lista.innerHTML = "<p>No tienes nuevas notificaciones.</p>";
            }
        })
        .catch(error => console.error("Error al cargar notificaciones:", error));
}

// Función para aceptar la notificación
function aceptarNotificacion(id) {
    console.log(" Has fet clic a ACCEPTAR notificació amb ID:", id);

    fetch("../../Controller/aceptar_notificacion.php", {
        method: 'POST',
        body: new URLSearchParams({ id: id }),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        }
    })
    .then(async response => {
        const text = await response.text();
        try {
            const data = JSON.parse(text);
            if (data.status === 'success') {
                cargarNotificaciones();
                if (window.location.href.includes("chat.php")) {
                    fetch("../../Controller/obtener_matches.php")
                        .then(r => r.json())
                        .then(m => console.log(" Matches refrescats:", m));
                }
            } else {
                alert('Error al aceptar la notificación: ' + data.message);
            }
        } catch (e) {
            console.error(" Resposta NO JSON:", text);
            alert('Error inesperado del servidor. Mira la consola.');
        }
    })
    .catch(error => console.error(' Error al aceptar la notificación (catch):', error));
}

// Función para rechazar la notificación
function rechazarNotificacion(id) {
    fetch("../../Controller/rechazar_notificacion.php", {
        method: 'POST',
        body: new URLSearchParams({ id: id }),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Notificación rechazada');
                cargarNotificaciones();  // Recargar las notificaciones
            } else {
                alert('Error al rechazar la notificación');
            }
        })
        .catch(error => console.error('Error al rechazar la notificación:', error));
}


// Función para abrir/cerrar el popup de Crear Grupo
function toggleCrearGrupo() {
    const popup = document.getElementById('popupCrearGrupo');
    popup.classList.toggle('show');
}

// Asignar el evento al botón "Crear Grupo" al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    const btnCrearGrupo = document.getElementById('openCreateGrup');
    if (btnCrearGrupo) {
        btnCrearGrupo.addEventListener('click', toggleCrearGrupo);
    }
});


// Inicializar carrusel al cargar
inicializarCarrusel();
