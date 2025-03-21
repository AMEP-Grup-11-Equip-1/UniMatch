<?php
include '../Gestion Usuario/login.php';

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - Unimatch</title>
    <link rel="icon" href="../Imagenes/img1.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="perfil.css">

</head>

<body>

    <!-- Icono de las tres rayas (hamburguesa) en la esquina superior izquierda -->
    <span class="menu-icon" onclick="openMenu()">&#9776;</span>
    <!-- Tres rayas para el menú -->

    <!-- Pestaña lateral -->
    <div id="sideMenu" class="side-menu">
        <!-- Botón de cierre en la parte superior derecha -->
        <span class="close-btn" onclick="closeMenu()">&times;</span>

        <!-- Enlaces del menú -->
        <a href="../Pantalla%20Inicio/bienvenida.html">Inicio</a>
        <a href="#">Perfil</a>
        <a href="../Pantalla%20Chat/chat.html">Chats</a>
        <!-- Enlace a la página de chats -->
        <a href="../Pantalla%20Config/configuracion.html">Configuraciones</a>
        <a href="../Pantalla%20Ayuda/ayuda.html">Ayuda</a>

        <!-- Agregar la opción de cerrar sesión -->
        <button class="logout-btn-side" onclick="window.location.href='../Pantalla%20de%20Bloqueo/Pantalladebloqueo.html'">Cerrar sesión</button>
        <button class="delete-account-btn" onclick="eliminarCuenta()" style="background-color: red; color: white; border: none; padding: 10px; width: 100%;">
            Eliminar cuenta
        </button>
    </div>

    

    <!-- Contenido principal de la página de perfil -->
    <div class="perfil-container">
        <h2>Mi Perfil</h2>

        <!-- Mostrar foto de perfil o avatar gris -->

        <!-- Formulario para modificar el perfil -->
        <?echo $_SESSION['usuarioID']; ?>
        <form action="../Gestion%20Usuario/modificar.php" method="POST" enctype="multipart/form-data">
            <input type="text" name="nombre" value="<?php echo $_SESSION['usuario']; ?>" placeholder="Nombre" required>
            <input type="text" name="contraseña" value="********" placeholder="Contraseña Nueva">
            <input type="email" name="email" value="<?php echo $_SESSION['email']; ?>" placeholder="Correo electrónico" required>
            <button type="submit">Actualizar Perfil</button>
        </form>

        <div class="back-btn">
            <a href="../Pantalla%20Inicio/bienvenida.html">Volver al inicio</a>
        </div>
    </div>

    <script>
        // Función para abrir el menú lateral
        function openMenu() {
            document.getElementById('sideMenu').style.width = '250px';
        }

        // Función para cerrar el menú lateral
        function closeMenu() {
            document.getElementById('sideMenu').style.width = '0';
        }
    </script>

<script>
    function eliminarCuenta() {
        if (confirm("¿Estás seguro de que deseas eliminar tu cuenta? Esta acción no se puede deshacer.")) {
            fetch("../Gestion%20Usuario/user.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" }
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.status === "success") {
                    window.location.href = "../Pantalla%20de%20Bloqueo/Pantalladebloqueo.html";
                }
            })
            .catch(error => console.error("Error en la solicitud:", error));
        }
    }
</script>


</body>

</html>
