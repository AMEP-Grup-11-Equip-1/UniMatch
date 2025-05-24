<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../Model/DataBase.php';

$input = json_decode(file_get_contents("php://input"), true);
if (!isset($input['grup_id']) || !isset($input['usuari_id'])) {
    echo json_encode(["success" => false, "message" => "Faltan datos"]);
    exit;
}

$grup_id = intval($input['grup_id']);
$usuari_id = intval($input['usuari_id']);

try {
    $db = new ConexionBD();
    $conn = $db->getConexion();

    $stmt = $conn->prepare("DELETE FROM grup_usuaris WHERE grup_id = ? AND usuari_id = ?");
    $stmt->bind_param("ii", $grup_id, $usuari_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(["success" => true, "message" => "Has salido del grupo correctamente"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
