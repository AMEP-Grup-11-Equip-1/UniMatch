<?php
// Iniciar la sesión
session_start();

// Destruir todas las variables de la sesión
session_unset();

// Destruir la sesión
session_destroy();

// Redirigir al usuario de nuevo a la página de inicio de sesión
header("Location: ../index.html");
exit();
?>