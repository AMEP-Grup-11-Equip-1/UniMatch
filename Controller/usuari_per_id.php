<?php
header('Content-Type: application/json');
require_once("../Model/DataBase.php");
require_once("../Model/user.php");

// Validar que viene el parámetro ?id=123
if (!isset($_GET['id'])) {
    echo json_encode(["status" => "error", "message" => "Falta el parámetro id"]);
    exit;
}

$id = intval($_GET['id']);

// Conectar a la base de datos
$db = new ConexionBD();
$conn = $db->getConexion();

if (!$conn) {
    echo json_encode(["status" => "error", "message" => "Error de conexión"]);
    exit;
}

// Obtener datos del usuario
$usuarioModel = new Usuario($conn);
$resultado = $usuarioModel->obtenerUsuarioPorID($id);

if ($resultado['status'] === 'success') {
    echo json_encode([
        "nombre" => $resultado['usuario']['name'],
        "email" => $resultado['usuario']['mail']
    ]);
} else {
    echo json_encode(["status" => "error", "message" => $resultado['message']]);
}
