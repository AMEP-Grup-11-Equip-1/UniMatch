<?php
session_start();
require_once("../Model/DataBase.php");

$response = [];

if (!isset($_SESSION['usuarioID'])) {
    $response["status"] = "error";
    $response["message"] = "No hay usuario autenticado.";
    echo json_encode($response);
    exit;
}

$usuario_id = $_SESSION['usuarioID'];

// Obtener datos del cuerpo JSON
$data = json_decode(file_get_contents("php://input"), true);

$tipo = $data["tipo"] ?? null;
$mensaje = $data["mensaje"] ?? null;
$puntuacion = $data["puntuacion"] ?? null;

if (!$tipo || ($tipo === 'pregunta' || $tipo === 'sugerencia') && !$mensaje) {
    $response["status"] = "error";
    $response["message"] = "Datos incompletos.";
    echo json_encode($response);
    exit;
}

// Crear instancia de la conexión
$db = new ConexionBD();
$conn = $db->getConexion();

$sql = "INSERT INTO ayuda (usuario_id, tipo, mensaje, puntuacion) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("issi", $usuario_id, $tipo, $mensaje, $puntuacion);
    if ($stmt->execute()) {
        $response["status"] = "success";
    } else {
        $response["status"] = "error";
        $response["message"] = "Error al ejecutar: " . $stmt->error;
    }
    $stmt->close();
} else {
    $response["status"] = "error";
    $response["message"] = "Error al preparar: " . $conn->error;
}

echo json_encode($response);
?>