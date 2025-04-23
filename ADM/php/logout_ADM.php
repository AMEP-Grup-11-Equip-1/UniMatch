<?php
session_start(); // Inicia la sesión actual

session_unset(); // Limpia todas las variables de sesión

session_destroy(); // Destruye la sesión actual

header("Location: ../html/login_ADM.html"); // Redirige al usuario a la página de inicio de sesión del administrador
exit(); // Finaliza la ejecución del script
?>