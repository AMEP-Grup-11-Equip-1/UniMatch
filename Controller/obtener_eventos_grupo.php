<?php
require_once __DIR__ . '/../Model/DataBase.php';
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$grupo_id = $data["grupo_id"] ?? null;

if (!$grupo_id) {
    echo json_encode(["success" => false, "message" => "Falta el ID del grupo."]);
    exit;
}

try {
    $db = new ConexionBD();
    $conn = $db->getConexion();

    $stmt = $conn->prepare("SELECT id, activitat, descripcio, data_event FROM grup_calendari WHERE grup_id = ? ORDER BY data_event DESC");
    $stmt->bind_param("i", $grupo_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $eventos = [];

    while ($row = $result->fetch_assoc()) {
        $eventos[] = [
            "id" => $row["id"],
            "activitat" => $row["activitat"],
            "descripcio" => $row["descripcio"],
            "data_event" => $row["data_event"]
        ];
    }

    echo json_encode(["success" => true, "eventos" => $eventos]);
    exit;
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    exit;
}
