 let usuarioID = null;

 // 1. Obtener el ID del usuario logueado
 fetch("../../Controller/get_session.php")
     .then(res => res.json())
     .then(data => {
         if (data.usuarioID) {
             usuarioID = parseInt(data.usuarioID);
             cargarGrupos(); // Si hay sesión, cargar grupos
             cargarGruposUnidos();
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

             let tieneGrupos = false;

             data.forEach(grup => {
                 const esPropio = grup.propietari_id == usuarioID;

                 const div = document.createElement("div");
                 div.classList.add("grupo-item");

                 const esPublico = grup.visibilitat.toLowerCase() === "public";
                 let textoBoton;
                 let botonClaseExtra = "";
                 if (esPropio) {
                     textoBoton = "Gestionar grupo";
                     botonClaseExtra = "success"; // esto aplica el estilo verde
                 } else {
                     textoBoton = esPublico ? "Unirse" : "Solicitar unirse";
                 }


                 div.innerHTML = `
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <h2>${grup.nom}</h2>
                                <p>${grup.descripcio}</p>
                                <p><strong>Visibilidad:</strong> ${grup.visibilitat}</p>
                            </div>
                            <button class="join-button ${botonClaseExtra}">${textoBoton}</button>
                            </div>
                        `;


                 const boton = div.querySelector(".join-button");

                 if (esPropio) {
                     propios.appendChild(div);
                 } else {
                     disponibles.appendChild(div);
                 }

                 if (esPropio) {
                     boton.classList.add("success");
                     boton.disabled = false;
                     return; // aún no hacemos nada al clickear en "Gestionar grupo"
                 }

                 boton.addEventListener("click", () => {
                     if (boton.classList.contains("success")) return;

                     if (esPublico) {
                         // Llamar al backend para guardar la relación
                         fetch('../../Controller/unirse_grupo.php', {
                                 method: "POST",
                                 headers: { "Content-Type": "application/json" },
                                 body: JSON.stringify({ grup_id: grup.id, usuari_id: usuarioID })
                             })
                             .then(res => res.json())
                             .then(resp => {
                                 console.log(resp); // Para depurar en consola
                                 mostrarPopup(resp.message);
                                 if (resp.success) {
                                     boton.textContent = "Ya te has unido";
                                     boton.classList.add("success");
                                     boton.disabled = true;
                                 }
                             })
                             .catch(err => {
                                 console.error("Error en fetch:", err);
                                 mostrarPopup("Error inesperado al unirse al grupo.");
                             });

                     } else {
                         // Para grupos privados solo mostramos mensaje de solicitud enviada, sin guardar aún
                         mostrarPopup("¡Has enviado la solicitud correctamente!");
                         boton.textContent = "Solicitud enviada";
                         boton.classList.add("success");
                         boton.disabled = true;
                     }
                 });


                 tieneGrupos = true;
             });

             if (!tieneGrupos) vacio.style.display = "block";
         })
         .catch(error => {
             console.error("Error al cargar los grupos:", error);
             document.getElementById("sinGrupos").style.display = "block";
         });
 }


 function cargarGruposUnidos() {
     fetch("../../Controller/obtener_grupos_unidos.php", {
             method: "POST",
             headers: {
                 "Content-Type": "application/json"
             }
         })
         .then(res => res.json())
         .then(data => {
             const lista = document.getElementById("gruposUnidos");

             if (!Array.isArray(data) || data.length === 0) return;

             data.forEach(grup => {

                 const div = document.createElement("div");
                 div.classList.add("grupo-item");

                 div.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2>${grup.nom}</h2>
                        <p>${grup.descripcio}</p>
                        <p><strong>Visibilidad:</strong> ${grup.visibilitat}</p>
                    </div>
                    <button class="join-button success" disabled>Miembro</button>
                </div>
                
            `;

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
