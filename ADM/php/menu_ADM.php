<link rel="stylesheet" href="/Unimatch/ADM/css/menu.css">

<!-- Ícone para abrir o menu -->
<span class="menu-icon" onclick="openMenu()">&#9776</span>

<!-- Menu lateral -->
<div id="sideMenu" class="side-menu">
    <span class="close-btn" onclick="closeMenu()">&times;</span>
    <a href="inicio.php">Inicio</a>
    <a href="perfil_ADM.php">Perfil</a>
    <button class="logout-btn-side" onclick="window.location.href='../php/logout_ADM.php'">Cerrar sesión</button>    </div>

<!-- Scripts para abrir/fechar o menu -->
<script>
function openMenu() {
    console.log('Menu aberto');
    document.getElementById('sideMenu').style.width = '250px';
}

function closeMenu() {
    console.log('Menu fechado');
    document.getElementById('sideMenu').style.width = '0';
}
</script>