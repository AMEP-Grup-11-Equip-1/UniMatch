<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['usuarioID'])) {
    echo json_encode(['usuarioID' => $_SESSION['usuarioID']]);
} else {
    echo json_encode(['error' => 'Usuari no loguejat']);
   // echo "<script>window.location.href = '../Pantalla_de_Bloqueo/Login.html';</script>";
}
