<?php
session_start();
header('Content-Type: application/json');
require_once("../Model/DataBase.php");

if (!isset($_SESSION['usuarioID'])) {
    echo json_encode(["success" => false, "message" => "Usuario no autenticado"]);
    exit;
}

$emisor_id = $_SESSION['usuarioID'];
$receptor_id = $_GET['receptor_id'] ?? null;

if (!$receptor_id) {
    echo json_encode(["success" => false, "message" => "Falta receptor_id"]);
    exit;
}

$db = new ConexionBD();
$conn = $db->getConexion();

$stmt = $conn->prepare("
    SELECT * FROM mensajes 
    WHERE (emisor = ? AND receptor = ?) OR (emisor = ? AND receptor = ?)
    ORDER BY fecha ASC
");
$stmt->bind_param("iiii", $emisor_id, $receptor_id, $receptor_id, $emisor_id);
$stmt->execute();
$result = $stmt->get_result();

$mensajes = [];
while ($row = $result->fetch_assoc()) {
    $mensajes[] = $row;
}

echo json_encode(["success" => true, "mensajes" => $mensajes]);
