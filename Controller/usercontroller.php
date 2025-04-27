<?php
session_start();
include_once '../Model/DataBase.php';
include_once '../Model/user.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuarioID'])) {
    echo json_encode(["status" => "error", "message" => "No hay usuario autenticado"]);
    exit;
}

$bd = new ConexionBD();
$conexion = $bd->getConexion();

$usuario = new Usuario($conexion);
$resultado = $usuario->eliminar($_SESSION['usuarioID']);

if ($resultado["status"] === "success") {
    session_unset();
    session_destroy();
}

echo json_encode($resultado);
?>
