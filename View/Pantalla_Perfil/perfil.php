<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuarioID'])) {
    header("Location: ../Pantalla_de_Bloqueo/Pantalladebloqueo.html"); // Redirigir si no está autenticado
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - Unimatch</title>
    <link rel="icon" href="../../Imagenes/img1.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Menu/menu.css">
    <link rel="stylesheet" href="perfil.css">
</head>

<body>

    <!-- Icono de las tres rayas (hamburguesa) en la esquina superior izquierda -->
    <span class="menu-icon" onclick="openMenu()">&#9776;</span>

    <!-- Pestaña lateral -->
    <div id="sideMenu" class="side-menu">
        <span class="close-btn" onclick="closeMenu()">&times;</span>
        <a href="../Pantalla_Inicio/bienvenida.html">Inicio</a>
        <a href="../Pantalla_Perfil/perfil.php">Perfil</a>
        <a href="../Pantalla_Chat/chat.html">Chats</a>
        <a href="../Pantalla_Config/configuracion.html">Configuraciones</a>
        <a href="../Pantalla_Ayuda/ayuda.html">Ayuda</a>
<button
        class="logout-btn-side"
        onclick="window.location.href='../../Controller/logout.php'"
      >
        Cerrar sesión
      </button>        <button class="delete-account-btn" onclick="eliminarCuenta()" style="background-color: red; color: white; border: none; padding: 10px; width: 100%;">Eliminar cuenta</button>
    </div>

    <!-- Contenido principal del perfil -->
    <div class="perfil-container">
        <button class="edit-btn" onclick="window.location.href='editar_perfil.php'">Editar</button>
        <h2>Mi Perfil</h2>

        <!-- Foto de perfil -->
        <div class="avatar">
            <img id="profile-image" src="" alt="Foto de perfil predeterminada">
        </div>

        <!-- Nombre y descripción (solo texto) -->
        <div class="info-perfil">
            <h3 id="profile-name">Nombre de usuario</h3>
            <p class="descripcion" id="profile-description">Descripción del usuario</p>
        </div>
    </div>

    <script>

        fetch('../../Controller/get_session.php') // Cambia la ruta a la real
                .then(res => res.json())
                .then(data => {
                    if (data.usuarioID) {
                        userId = data.usuarioID;
                        console.log('Usuario logueado con ID:', userId);
                        // Aquí puedes llamar a cargarMatches(), cargarMensajes() o lo que sea, 
                        // para cargar la conversación con el userId ya disponible
                    } else {
                        console.warn('Usuario no logueado:', data.error);
                        window.location.href = "../Pantalla_de_Bloqueo/Pantalladebloqueo.html";

                    }
                })
                .catch(err => console.error('Error al obtener sesión:', err));

                
        // Función para abrir el menú
        function openMenu() {
            document.getElementById('sideMenu').style.width = '250px';
        }

        // Función para cerrar el menú
        function closeMenu() {
            document.getElementById('sideMenu').style.width = '0';
        }

        // Función para eliminar la cuenta
        function eliminarCuenta() {
            if (confirm("¿Estás seguro de que deseas eliminar tu cuenta? Esta acción no se puede deshacer.")) {
                fetch("../../Controller/usercontroller.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" }
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

        // Función para cargar los datos del perfil
        function cargarPerfil() {
                    fetch("../../Controller/usercontroller.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" }
        })
        .then(response => {
            // Verificamos si la respuesta es exitosa
            if (!response.ok) {
                throw new Error('Error en la solicitud: ' + response.statusText);
            }
            // Intentamos convertir la respuesta en JSON
            return response.json();
        })
        .then(data => {
            // Verificamos el estado de la respuesta
            if (data.status === "success") {
                // Si el estado es "success", actualizamos los datos del perfil
                document.querySelector('.info-perfil h3').textContent = data.usuario.nombre;
                document.querySelector('.descripcion').textContent = data.usuario.descripcion;
                document.querySelector('.avatar img').src = data.usuario.imagen;
            } else {
                // Si el estado no es "success", mostramos el mensaje de error
                alert('Error al cargar los datos: ' + data.message);
            }
        })
        .catch(error => {
            // Si ocurre un error en la solicitud o al analizar la respuesta
            console.error('Error al cargar los datos del perfil:', error);
            alert('Error al cargar los datos del perfil.');
        });

            }

    // Llamamos a la función para cargar los datos al cargar la página
    window.onload = cargarPerfil;

    </script>

</body>

</html>

