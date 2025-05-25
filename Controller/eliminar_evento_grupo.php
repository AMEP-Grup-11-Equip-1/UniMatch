<?php
require_once __DIR__ . '/../Model/DataBase.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$evento_id = $data["evento_id"] ?? null;

if (!$evento_id) {
    echo json_encode(["success" => false, "message" => "Falta el ID del evento."]);
    exit;
}

try {
    $db = new ConexionBD();
    $conn = $db->getConexion();

    $stmt = $conn->prepare("DELETE FROM grup_calendari WHERE id = ?");
    $stmt->bind_param("i", $evento_id);
    $stmt->execute();

    echo json_encode(["success" => true, "message" => "Evento eliminado correctamente."]);
    exit;
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    exit;
}
