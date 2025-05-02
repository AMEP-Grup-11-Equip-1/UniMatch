<?php
session_start();
include_once '../Model/DataBase.php';
include_once '../Model/user.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bd = new ConexionBD();
    $conexion = $bd->getConexion();

    $usuarioModel = new Usuario($conexion);
    $email = trim($_POST['username']);
    $password = trim($_POST['password']);

    $resultado = $usuarioModel->autenticar($email, $password);

    if ($resultado["status"] === "success") {
        $_SESSION['usuario'] = $resultado["usuario"]["name"];
        $_SESSION['email'] = $resultado["usuario"]["mail"];
        $_SESSION['usuarioID'] = $resultado["usuario"]["id"];
        $_SESSION['descripcion'] = $resultado["usuario"]["descripcion"];
        $_SESSION['error'] = "";

        header("Location: ../View/Pantalla_Inicio/bienvenida.html");
        exit();
    } else {
        $_SESSION['error'] = $resultado["message"];
        $error = urlencode($_SESSION['error']);
        header("Location: ../View/Pantalla_de_Bloqueo/Login.html?error=" . $error);
        exit();
    }
}
?>
