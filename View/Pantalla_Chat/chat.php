<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chats - Unimatch</title>
    <link rel="icon" href="img1.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="chats.css">
    <link rel="stylesheet" href="../Menu/menu.css">
</head>

<body>
    <span class="menu-icon" onclick="openMenu()">&#9776;</span>
    <div id="sideMenu" class="side-menu">
        <span class="close-btn" onclick="closeMenu()">&times;</span>
        <a href="../Pantalla_Inicio/bienvenida.html">Inicio</a>
        <a href="../Pantalla_Perfil/perfil.php">Perfil</a>
        <a href="../Pantalla_Chat/chat.php">Chats</a>
        <a href="../Pantalla_Config/configuracion.html">Configuraciones</a>
        <a href="../Pantalla_Ayuda/ayuda.html">Ayuda</a>
        <button class="logout-btn-side" onclick="window.location.href='../Pantalla_de_Bloqueo/Pantalladebloqueo.html'">Cerrar sesión</button>
    </div>

    <div class="chat-container">
        <div class="chat-list">
            <div class="section-title">Matchs</div>
            <div id="matchList"></div>
        </div>

        <div class="chat-box">
            <h2>Conversación</h2>
            <div class="messages" id="messagesContainer"></div>
            <div class="message-input">
                <input type="text" id="message" placeholder="Escribe un mensaje...">
                <button onclick="enviarMissatge()">Enviar</button>
            </div>
        </div>
    </div>

    <script>
        function openMenu() {
            document.getElementById('sideMenu').style.width = '250px';
        }

        function closeMenu() {
            document.getElementById('sideMenu').style.width = '0';
        }

        let receptor_id = null;
        const messagesContainer = document.getElementById("messagesContainer");
        const messageInput = document.getElementById("message");
        const matchList = document.getElementById("matchList");
        const userId = <?php echo $_SESSION['usuarioID']; ?>;

        function carregarMatches() {
        fetch("../../Controller/obtener_matches.php")
            .then(res => res.text())  // Rebem com a text
            .then(text => {
                console.log("Resposta crua:", text);  // Mostra a la consola

                const data = JSON.parse(text);  // Intentem parsejar-ho

                if (data.success) {
                    matchList.innerHTML = "";
                    data.matches.forEach(match => {
                        const div = document.createElement("div");
                        div.innerHTML = `
                            <div class="chat-item" style="display: flex; align-items: center;">
                                <img src="../Imatges/default-user.png" style="width: 40px; height: 40px; border-radius: 50%; margin-right: 10px;" />
                                <div>
                                    <strong>${match.nombre}</strong><br>
                                    <small>Haz clic para chatear</small>
                                </div>
                            </div>
                        `;
                        div.onclick = () => obrirXat(match.id, match.nombre);
                        matchList.appendChild(div);
                    });
                } else {
                    console.warn("⚠️ Error obtinguent matches:", data.message || "Resposta no esperada");
                }
            })
            .catch(err => console.error("❌ Error carregant matches:", err));
    }


        function carregarMissatges() {
            if (!receptor_id) return;
            fetch(`../../Controller/obtener_mensajes.php?receptor_id=${receptor_id}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        messagesContainer.innerHTML = "";
                        data.mensajes.forEach(msg => {
                            const p = document.createElement("p");
                            p.textContent = msg.emisor == userId ? "Tu: " + msg.mensaje : "Ell: " + msg.mensaje;
                            messagesContainer.appendChild(p);
                        });
                        messagesContainer.scrollTop = messagesContainer.scrollHeight;
                    }
                });
        }

        function enviarMissatge() {
            const text = messageInput.value.trim();
            if (!text || !receptor_id) return;

            const formData = new FormData();
            formData.append("receptor_id", receptor_id);
            formData.append("mensaje", text);

            fetch("../../Controller/enviar_mensaje.php", {
                method: "POST",
                body: formData
            }).then(() => {
                messageInput.value = "";
                carregarMissatges();
            });
        }

        function obrirXat(id, nom) {
            receptor_id = id;
            document.querySelector(".chat-box h2").textContent = "Conversación amb " + nom;
            carregarMissatges();
        }

        setInterval(carregarMissatges, 3000);
        window.onload = () => {
            carregarMatches();
        };
    </script>
</body>

</html>
