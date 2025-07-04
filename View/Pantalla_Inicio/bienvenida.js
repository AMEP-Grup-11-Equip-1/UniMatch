const carouselContainer = document.querySelector('.carousel');
let currentIndex = 0;
let isThrottled = false;
let islands = [];
let currentUserId = null;
let tipusFiltreActual = "todas"; // valor inicial per veure-ho tot

// Obtenir l'ID de l'usuari via AJAX
fetch('../../Controller/get_session.php')
    .then(res => res.json())
    .then(data => {
        if (data.usuarioID) {
            currentUserId = data.usuarioID;
            console.log("Sessió carregada: ID usuari =", currentUserId);
        } else {
            console.warn("Usuari no loguejat:", data.error);
            window.location.href = "../Pantalla_de_Bloqueo/Pantalladebloqueo.html";
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
            island.dataset.targetUserId = perfil.user_id; // 🔑 ID del creador de la història

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
                <button class="report-btn" onclick="openReportPopup(${perfil.user_id})">🚩</button>
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
    const crearGrupoPopup = document.getElementById('popupCrearGrupo');
    const notificationPopup = document.getElementById('notificationPopup');

    // Tancar altres popups
    crearGrupoPopup?.classList.remove('show');
    notificationPopup?.classList.remove('show');

    // Obre o tanca aquest
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
        fetch("../../Controller/eliminar_usuario.php", {
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
    const crearGrupoPopup = document.getElementById('popupCrearGrupo');
    const profilePopup = document.getElementById('profilePopup');

    // Tancar altres popups
    crearGrupoPopup?.classList.remove('show');
    profilePopup?.classList.remove('show');

    // Obre o tanca aquest
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

function filtrarNotificaciones(tipus) {
    tipusFiltreActual = tipus;
    cargarNotificaciones();

    const botoons = document.querySelectorAll('.notification-filters button');
    botoons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.getAttribute('data-tipo') === tipus) {
            btn.classList.add('active');
        }
    });
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

            // 👉 Aquest és el filtre correcte!
            const filtrades = tipusFiltreActual === "todas"
                ? data.data
                : data.data.filter(n => n.tipo === tipusFiltreActual);

            if (filtrades.length > 0) {
                filtrades.forEach(n => {
                    const notificationContainer = document.createElement('div');
                    notificationContainer.classList.add('notification-container');
                    notificationContainer.setAttribute('data-id', n.id);

                    const notificationContent = document.createElement('div');
                    notificationContent.classList.add('notification-content');
                    notificationContent.innerHTML = `
                        <p><strong>${n.mensaje}</strong></p>
                        <p style="color: #777;">${new Date(n.fecha).toLocaleString()}</p>
                    `;

                    const notificationActions = document.createElement('div');
                    notificationActions.classList.add('notification-actions');

                    // NOMÉS afegim botons si és de tipus 'match'
                    if (n.tipo === "match" || n.tipo === "grupo"){
                        notificationActions.innerHTML = `
                            <button class="accept-btn" onclick="aceptarNotificacion(${n.id}, '${n.tipo}')">Aceptar</button>
                            <button class="reject-btn" onclick="rechazarNotificacion(${n.id})">Rechazar</button>
                        `;
                    }

                    notificationContainer.appendChild(notificationContent);
                    notificationContainer.appendChild(notificationActions);
                    lista.appendChild(notificationContainer);
                });
            } else {
                lista.innerHTML = "<p>No tienes nuevas notificaciones.</p>";
            }
        })
        .catch(error => console.error("Error al cargar notificaciones:", error));
}

// Función para aceptar la notificación
function aceptarNotificacion(id, tipo) {
    console.log("Has fet clic a ACCEPTAR notificació amb ID:", id, "i tipus:", tipo);

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

                // Actuar segons el tipus
                if (tipo === "match") {
                    window.location.href = "../Pantalla_Chat/chat.php";
                } else if (tipo === "grupo") {
                    alert("✅ Usuari afegit al grup correctament!");
                    // No redirigeixes, simplement es queda on està
                }

            } else {
                alert('Error al aceptar la notificación: ' + data.message);
            }
        } catch (e) {
            console.error("Resposta NO JSON:", text);
            alert('Error inesperado del servidor. Mira la consola.');
        }
    })
    .catch(error => console.error('Error al aceptar la notificación (catch):', error));
}

function rechazarNotificacion(id) {
    console.log(" Has fet clic a RECHAZAR notificació amb ID:", id);

    fetch("../../Controller/rechazar_notificacion.php", {
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

                } else {
                    alert('Error al rechazar la notificación: ' + data.message);
                }
            } catch (e) {
                console.error(" Resposta NO JSON:", text);
                alert('Error inesperado del servidor al rechazar. Mira la consola.');
            }
        })
        .catch(error => console.error(' Error al rechazar la notificación (catch):', error));
}




// Función para abrir/cerrar el popup de Crear Grupo
function toggleCrearGrupo() {
    const popup = document.getElementById('popupCrearGrupo');
    const profilePopup = document.getElementById('profilePopup');
    const notificationPopup = document.getElementById('notificationPopup');

    // Tancar altres popups
    profilePopup?.classList.remove('show');
    notificationPopup?.classList.remove('show');

    // Obre o tanca aquest
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


document.getElementById('historiaForm').addEventListener('submit', function(e) {
    const form = e.target;
    const nombre = form.nombre.value.trim();
    const universidad = form.universidad.value.trim();
    const descripcion = form.descripcion.value.trim();
    const imagen = form.imagen.files[0];
    const errorDiv = document.getElementById('errorMensajeHistoria');

    errorDiv.textContent = ''; // Limpiar errores previos

    if (!imagen || !nombre || !universidad || !descripcion) {
        e.preventDefault(); // Prevenir envío
        errorDiv.textContent = 'Todos los campos son obligatorios.';
        return;
    }

    if (descripcion.length < 10) {
        e.preventDefault();
        errorDiv.textContent = 'La descripción debe tener al menos 10 caracteres.';
        return;
    }

    // Aquí puedes hacer más validaciones si quieres
});


document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('historiaForm');
    const errorDiv = document.getElementById('errorMensajeHistoria');

    form.addEventListener('submit', function(e) {
        errorDiv.textContent = '';
        errorDiv.style.opacity = '0';
        errorDiv.style.display = 'none';

        const nombre = form.nombre.value.trim();
        const universidad = form.universidad.value.trim();
        const descripcion = form.descripcion.value.trim();
        const imagen = form.imagen.files[0];

        console.log('Validando formulario historia...');

        // Validaciones
        if (!imagen || !nombre || !universidad || !descripcion) {
            e.preventDefault();
            mostrarError(errorDiv, 'Todos los campos son obligatorios.');
            return;
        }

        if (nombre.length < 2) {
            e.preventDefault();
            mostrarError(errorDiv, 'El nombre debe tener al menos 2 caracteres.');
            return;
        }

        if (universidad.length < 2) {
            e.preventDefault();
            mostrarError(errorDiv, 'La universidad debe tener al menos 2 caracteres.');
            return;
        }

        if (descripcion.length < 10) {
            e.preventDefault();
            mostrarError(errorDiv, 'La descripción debe tener al menos 10 caracteres.');
            return;
        }

        // Si llegas aquí, el formulario se envía porque no hubo preventDefault
        console.log('Formulario validado, enviando...');
    });
});

function mostrarError(element, mensaje) {
    element.textContent = mensaje;
    element.style.display = 'flex';
    setTimeout(() => {
        element.style.opacity = '1';
    }, 10);

    setTimeout(() => {
        element.style.opacity = '0';
        setTimeout(() => {
            element.style.display = 'none';
        }, 1000);
    }, 5000);
}

function openReportPopup(targetUserId) {
    closeAllPopups();
    document.getElementById('reportHistoriaTargetUserId').value = targetUserId;
    document.getElementById('reportPopup').classList.add('show');
    cargarMotivosDenuncia();
}

function closeReportPopup() {
    document.getElementById('reportPopup').classList.remove('show');
}

function handleReportChange() {
    const motivo = document.getElementById('reportReason').value;
    document.getElementById('otherReason').style.display = motivo === '9' ? 'block' : 'none';
}

async function cargarMotivosDenuncia() {
    const select = document.getElementById('reportReason');
    if (select.options.length > 1) return;

    try {
        const response = await fetch('../../Controller/get_report_types.php');
        const motivos = await response.json();
        motivos.forEach(m => {
            const option = document.createElement('option');
            option.value = m.id;
            option.textContent = m.name;
            select.appendChild(option);
        });
    } catch (e) {
        console.error("Error carregant motius denúncia:", e);
    }
}

document.getElementById('reportForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const target_user_id = document.getElementById('reportHistoriaTargetUserId').value;
    const report_type_id = document.getElementById('reportReason').value;
    const descripcio = document.getElementById('otherReason').value.trim();
    const errorDiv = document.getElementById('errorMensajeDenuncia');

    errorDiv.textContent = "";

    if (!report_type_id) {
        errorDiv.textContent = "Has de seleccionar un motiu.";
        return;
    }

    if (report_type_id === "9" && descripcio.length < 5) {
        errorDiv.textContent = "Has d'explicar el motiu si selecciones 'Altres'.";
        return;
    }

    fetch('../../Controller/denunciar_historia.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
            target_user_id,
            reporting_user_id: currentUserId,
            report_type_id
        })
    })
        .then(res => res.json())
        .then(data => {
            if (data.status === "success") {
                alert("✅ Denúncia enviada correctament.");
                closeReportPopup();
            } else {
                errorDiv.textContent = data.message || "Error desconegut";
            }
        })
        .catch(err => {
            console.error("Error enviant denúncia:", err);
            errorDiv.textContent = "Error del servidor. Torna-ho a provar.";
        });
});

function closeAllPopups() {
    document.querySelectorAll('.profile-popup').forEach(p => p.classList.remove('show'));
}
