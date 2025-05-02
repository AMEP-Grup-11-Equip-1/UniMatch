<?php
session_start();
include_once '../Model/DataBase.php';
include_once '../Model/Usuario.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['usuarioID'])) {
        $_SESSION['error'] = "No hay usuario autenticado";
        header("Location: ../View/Pantalla_Perfil/perfil.php");
        exit();
    }

    $bd = new ConexionBD();
    $conexion = $bd->getConexion();

    $usuarioModel = new Usuario($conexion);

    $nombre_usuario = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = trim($_POST['contraseña']);
    $descripcion = trim($_POST['descripcion']);


    $result = $usuarioModel->actualizarPerfil($_SESSION['usuarioID'], $nombre_usuario, $email, $password, $descripcion);

    if ($result["status"] === "success") {
        $_SESSION['usuario'] = $nombre_usuario;
        $_SESSION['email'] = $email;
        $_SESSION['descripcion'] = $descripcion;
        if (!empty($password)) {
            $_SESSION['contraseña'] = $password;
        }
        header("Location: ../View/Pantalla_Perfil/perfil.php");
        exit();
    } else {
        $_SESSION['error'] = $result["message"];
        header("Location: ../View/Pantalla_Perfil/perfil.php");
        exit();
    }
}
?>
