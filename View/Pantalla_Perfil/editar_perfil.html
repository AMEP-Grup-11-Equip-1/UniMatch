<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <link rel="icon" href="../Imagenes/img1.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Menu/menu.css">
    <link rel="stylesheet" href="editar_perfil.css">
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
        <button class="logout-btn-side" onclick="window.location.href='../Pantalla_de_Bloqueo/Pantalladebloqueo.html'">Cerrar sesión</button>
        <button class="delete-account-btn" onclick="eliminarCuenta()" style="background-color: red; color: white; border: none; padding: 10px; width: 100%;">Eliminar cuenta</button>
    </div>

    <!-- Contenido principal del perfil de edición -->
    <div class="edit-container">
        <h2>Editar Mi Perfil</h2>

        <form id="editProfileForm" method="POST" action="../../Controller/ActualitzarPerfil.php" enctype="multipart/form-data">
            <!-- Campo de Nombre de Usuario -->
            <label for="name">Nombre de Usuario</label>
            <input type="text" id="name" name="name" value="<?php echo $_SESSION['usuario']; ?>" placeholder="Nuevo nombre de usuario">

            <!-- Campo de Correo Electrónico -->
            <label for="email">Correo Electrónico</label>
            <input type="email" id="email" name="email" value="<?php echo $_SESSION['email']; ?>" placeholder="Nuevo correo electrónico">

            <!-- Campo de Descripción -->
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" placeholder="Escribe una nueva descripción"><?php echo isset($_SESSION['descripcion']) ? $_SESSION['descripcion'] : ''; ?></textarea>

            <!-- Campo de Contraseña -->
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" placeholder="Nueva contraseña">

            <!-- Campo de Foto de Perfil -->
            <label for="imagen">Foto de Perfil</label>
            <input type="file" id="imagen" name="imagen">

            <!-- Botón de Guardar -->
            <div class="save-button-container">
                <button type="submit" id="guardarBtn" onclick="return confirmChanges()">Guardar Cambios</button>
            </div>
        </form>
    </div>

    <script>
        // Función para confirmar si el usuario quiere guardar los cambios
        function confirmChanges() {
            return confirm("¿Estás seguro de que deseas guardar estos cambios?");
        }

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
    </script>
</body>

</html>
