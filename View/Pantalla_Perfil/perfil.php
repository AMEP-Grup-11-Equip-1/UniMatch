<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - Unimatch</title>
    <link rel="icon" href="../Imagenes/img1.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Menu/menu.css">
    <link rel="stylesheet" href="perfil.css">
</head>

<body>

    <!-- Icono de las tres rayas (hamburguesa) en la esquina superior izquierda -->
    <span class="menu-icon" onclick="openMenu()">&#9776;</span>

    <!-- Pestaña lateral -->
    <span class="menu-icon" onclick="openMenu()">&#9776;</span>

    <div id="sideMenu" class="side-menu">
        <span class="close-btn" onclick="closeMenu()">&times;</span>
        <a href="../Pantalla_Perfil/perfil.php">Perfil</a>
        <a href="../Pantalla_Chat/chat.html">Chats</a>
        <a href="../Pantalla_Config/configuracion.html">Configuraciones</a>
        <a href="../Pantalla_Ayuda/ayuda.html">Ayuda</a>
        <button class="logout-btn-side" onclick="window.location.href='../Pantalla_de_Bloqueo/Pantalladebloqueo.html'">Cerrar sesión</button>
        <button class="delete-account-btn" onclick="eliminarCuenta()" style="background-color: red; color: white; border: none; padding: 10px; width: 100%;">Eliminar cuenta</button>
    </div>
    <!-- Contenido principal del perfil -->
    <div class="perfil-container">

    <button class="edit-btn" onclick="window.location.href='editar_perfil.html'">Editar</button>


        <h2>Mi Perfil</h2>

        <!-- Foto de perfil -->
        <div class="avatar">
            <img src="../Imagenes/foto_perfil.png" alt="Foto de perfil">
        </div>

        <!-- Nombre y descripción (solo texto) -->
        <div class="info-perfil">
            <h3><?php echo $_SESSION['usuario']; ?></h3>
            <p class="descripcion">Esta es la descripción del usuario. Aquí puedes mostrar más información personal si lo necesitas.</p>
        </div>

    </div>

    <script>
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
    </script>

</body>

</html>
