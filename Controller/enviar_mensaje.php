<?php
session_start();
header('Content-Type: application/json');
require_once("../Model/DataBase.php");

if (!isset($_SESSION['usuarioID'])) {
    echo json_encode(["success" => false, "message" => "Usuario no autenticado"]);
    exit;
}

$emisor_id = $_SESSION['usuarioID'];
$receptor_id = $_POST['receptor_id'] ?? null;
$mensaje = trim($_POST['mensaje'] ?? '');

if (!$receptor_id || !$mensaje) {
    echo json_encode(["success" => false, "message" => "Datos incompletos"]);
    exit;
}

$db = new ConexionBD();
$conn = $db->getConexion();

$stmt = $conn->prepare("INSERT INTO mensajes (emisor, receptor, mensaje) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $emisor_id, $receptor_id, $mensaje);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Error al guardar mensaje"]);
}
