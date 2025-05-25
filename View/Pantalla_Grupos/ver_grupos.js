 let usuarioID = null;
 let gruposYaUnidos = new Set();

 // 1. Obtener el ID del usuario logueado
 fetch("../../Controller/get_session.php")
     .then(res => res.json())
     .then(data => {
         if (data.usuarioID) {
             usuarioID = parseInt(data.usuarioID);
            cargarGruposUnidos().then(() => {
                 cargarGrupos();
             });
         } else {
             document.getElementById("sinGrupos").textContent = "Debes iniciar sesión para ver los grupos.";
             document.getElementById("sinGrupos").style.display = "block";
         }
     })
     .catch(err => {
         console.error("Error al obtener la sesión:", err);
         window.location.href = "../Pantalla_de_Bloqueo/Pantalladebloqueo.html";

     });

 // 2. Obtener grupos y dividir en dos listas
function cargarGrupos() {
    fetch("../../Controller/obtener_grupos.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        }
    })
        .then(res => res.json())
        .then(data => {
            const disponibles = document.getElementById("listaGrupos");
            const propios = document.getElementById("misGrupos");
            const vacio = document.getElementById("sinGrupos");

            if (!Array.isArray(data) || data.length === 0) {
                vacio.style.display = "block";
                return;
            }

            data.forEach(grup => {
                const esPropio = grup.propietari_id == usuarioID;

                // ❌ Saltar grupos donde ya soy miembro (si no soy el propietario)
                if (!esPropio && gruposYaUnidos.has(grup.id)) {
                    return;
                }

                const div = document.createElement("div");
                div.classList.add("grupo-item");

                const esPublico = grup.visibilitat.toLowerCase() === "public";
                let textoBoton = "";
                let botonClaseExtra = "";

                if (esPropio) {
                    textoBoton = "Gestionar grupo";
                    botonClaseExtra = "success";
                } else {
                    textoBoton = esPublico ? "Unirse" : "Solicitar unirse";
                }

                div.innerHTML = `
                  <div style="display: flex; align-items: center; justify-content: space-between;">
                      <div style="display: flex; align-items: center; gap: 14px;">
                        <img src="${grup.imagen || '../../Imagenes/img2.png'}"
                             alt="Foto grupo"
                             style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;" />
                        <div>
                          <h2 style="margin: 0;">${grup.nom}</h2>
                          <p style="margin: 2px 0;">${grup.descripcio}</p>
                          <p style="margin: 2px 0;"><strong>Visibilidad:</strong> ${grup.visibilitat}</p>
                        </div>
                      </div>
                      <button class="join-button ${botonClaseExtra}">${textoBoton}</button>
                    </div>
                `;


                const boton = div.querySelector(".join-button");

                if (esPropio) {
                    propios.appendChild(div);
                    boton.addEventListener("click", () => {
                        window.location.href = `gestionar_grupo.html?grupo_id=${grup.id}`;
                    });
                    return;
                }

                disponibles.appendChild(div);

                // Unirse o solicitar
                boton.addEventListener("click", () => {
                    if (esPublico) {
                        fetch('../../Controller/unirse_grupo.php', {
                            method: "POST",
                            headers: { "Content-Type": "application/json" },
                            body: JSON.stringify({ grup_id: grup.id, usuari_id: usuarioID })
                        })
                            .then(res => res.json())
                            .then(resp => {
                                mostrarPopup(resp.message);
                                if (resp.success) {
                                    boton.textContent = "Miembro";
                                    boton.classList.add("success");
                                    boton.disabled = true;
                                }
                            });
                    } else {
                        fetch("../../Controller/solicitar_unirse_grupo.php", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json"
                            },
                            body: JSON.stringify({ grupo_id: grup.id })
                        })
                            .then(res => res.json())
                            .then(resp => {
                                mostrarPopup(resp.message);
                                if (resp.success) {
                                    boton.textContent = "Solicitud enviada";
                                    boton.classList.add("success");
                                    boton.disabled = true;
                                }
                            })
                            .catch(err => {
                                console.error("Error al solicitar unirse:", err);
                                mostrarPopup("Hubo un problema al enviar la solicitud.");
                            });
                    }
                });
            });
        })
        .catch(err => {
            console.error("Error al cargar grupos:", err);
        });
}


 function cargarGruposUnidos() {

    return fetch("../../Controller/obtener_grupos_unidos.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ usuari_id: usuarioID })
    })
    .then(res => res.json())
    .then(data => {
        const lista = document.getElementById("gruposUnidos");

        if (!Array.isArray(data) || data.length === 0) return;

        data.forEach(grup => {
            gruposYaUnidos.add(grup.id); // <- evita que aparezcan de nuevo

            const div = document.createElement("div");
            div.classList.add("grupo-item");
            div.innerHTML = `
              <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 14px;">
                  <img src="${grup.imagen || '../../Imagenes/img2.png'}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;" />
                  <div>
                    <h2>${grup.nom}</h2>
                    <p>${grup.descripcio}</p>
                    <p><strong>Visibilidad:</strong> ${grup.visibilitat}</p>
                  </div>
                </div>
                <button class="join-button success">Ver Grupo</button>
              </div>
            `;

            const boton = div.querySelector(".join-button");

            boton.addEventListener("click", () => {
                window.location.href = `ver_grupos_unidos.html?grupo_id=${grup.id}`;
            });

            lista.appendChild(div);
        });
    })
    .catch(err => {
        console.error("Error al cargar grupos unidos:", err);
    });
}


 // 3. Cambiar pestañas
 document.getElementById("tab-disponibles").addEventListener("click", () => {
     activarPestanya("listaGrupos", "tab-disponibles");
 });

 document.getElementById("tab-misgrupos").addEventListener("click", () => {
     activarPestanya("misGrupos", "tab-misgrupos");
 });

 document.getElementById("tab-unidos").addEventListener("click", () => {
     activarPestanya("gruposUnidos", "tab-unidos");
 });

 function activarPestanya(listaID, tabID) {
     document.querySelectorAll(".grupo-lista").forEach(div => div.style.display = "none");
     document.getElementById(listaID).style.display = "block";

     document.querySelectorAll(".tab").forEach(tab => tab.classList.remove("active"));
     document.getElementById(tabID).classList.add("active");
 }

 function mostrarPopup(mensaje) {
     const popup = document.getElementById("popupNotificacion");
     popup.textContent = mensaje;
     popup.style.display = "block";
     setTimeout(() => {
         popup.style.display = "none";
     }, 3000);
 }

