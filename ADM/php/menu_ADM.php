<link rel="stylesheet" href="/Unimatch/ADM/css/menu.css">

<!-- Ícono para abrir el menú -->
<span class="menu-icon" onclick="openMenu()">&#9776</span>

<!-- Menú lateral -->
<div id="sideMenu" class="side-menu">
    <span class="close-btn" onclick="closeMenu()">&times;</span>
    <a href="inicio.php">Inicio</a> <!-- Enlace a la página de inicio -->
    <a href="perfil_ADM.php">Perfil</a> <!-- Enlace al perfil del administrador -->
    <button class="logout-btn-side" onclick="window.location.href='../php/logout_ADM.php'">Cerrar sesión</button> <!-- Botón para cerrar sesión -->
</div>

<!-- Scripts para abrir/cerrar el menú -->
<script>
function openMenu() {
    console.log('Menú abierto'); // Mensaje en la consola al abrir el menú
    document.getElementById('sideMenu').style.width = '250px'; // Establece el ancho del menú lateral
}

function closeMenu() {
    console.log('Menú cerrado'); // Mensaje en la consola al cerrar el menú
    document.getElementById('sideMenu').style.width = '0'; // Reduce el ancho del menú lateral a 0
}
</script>