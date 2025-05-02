<?php
session_start();
header('Content-Type: application/json');
include_once '../Model/DataBase.php';

if (!isset($_SESSION['usuarioID'])) {
    echo json_encode(["error" => "No hay sesión iniciada"]);
    exit();
}

$usuario_id = $_SESSION['usuarioID'];

$bd = new ConexionBD();
$conn = $bd->getConexion();

$sql = "SELECT id, mensaje, fecha, vista FROM notificaciones WHERE usuario_id = ? ORDER BY fecha DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$notificaciones = [];

while ($row = $result->fetch_assoc()) {
    $notificaciones[] = $row;
}

echo json_encode(["data" => $notificaciones]);
?>
