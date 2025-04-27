const islands = document.querySelectorAll('.island');
let currentIndex = 0;
let isThrottled = false;

let currentId = 1;
const maxId = 9;

async function inicializarCarrusel() {
    try {
        const response = await fetch("../../Controller/imprimir_historia.php");
        const perfiles = await response.json();
        
        if (perfiles.error) {
            console.error(perfiles.error);
            return;
        }

        perfiles.forEach((perfil, index) => {
            if (index < islands.length) {
                const island = islands[index];
                island.querySelector('img').src = perfil.imagen;
                island.querySelector('img').alt = perfil.nombre;
                island.querySelector('.profile-name').textContent = perfil.nombre;
                island.querySelector('.profile-university').textContent = perfil.universidad;
                island.querySelector('.profile-description').textContent = perfil.descripcion;
            }
        });

        updateCarousel(true);
    } catch (error) {
        console.error('Error al cargar los perfiles iniciales:', error);
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
    if (isThrottled) return;

    isThrottled = true;
    setTimeout(() => isThrottled = false, 900);

    if (direction === 1) {
        // Cargar el perfil de la isla que va a entrar
        const islaOculta = islands[(currentIndex - 4 + islands.length) % islands.length];

        // Si llegamos al final, reiniciamos el contador de IDs
        if (currentId >= maxId) {
            currentId = 1; // Reiniciar al primer ID
        } else {
            currentId = (currentId % maxId) + 1; // Incrementar el ID
        }
        cargarPerfil(currentId, islaOculta);
    } else {
        // Cargar el perfil de la isla que va a entrar
        const islaOculta = islands[(currentIndex + 4) % islands.length];

        // Si llegamos al principio, reiniciamos el contador de IDs
        if (currentId <= 1) {
            currentId = maxId; // Reiniciar al último ID
        } else {
            currentId = (currentId - 2 + maxId) % maxId + 1; // Decrementar el ID
        }
        cargarPerfil(currentId, islaOculta);
    }

    // Actualizamos el índice
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
}

function toggleLike(element) {
    const icon = element.querySelector('.material-icons');
    const wasLiked = element.classList.contains('liked');
    element.classList.toggle('liked');
    if (!wasLiked) {
        icon.textContent = 'favorite';
        heartBeatLong(element);
        launchMiniHearts(element.closest('.island'), element);
    } else {
        icon.textContent = 'favorite_border';
    }
}

// Heartbeat llarg (1,7 segons)
function heartBeatLong(btn) {
    btn.classList.remove('heartbeat');
    void btn.offsetWidth;
    btn.classList.add('heartbeat');
    setTimeout(() => btn.classList.remove('heartbeat'), 1750);
}

// Llamamos al iniciar
inicializarCarrusel();
