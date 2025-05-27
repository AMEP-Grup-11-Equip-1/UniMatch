<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../Model/DataBase.php';

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['grup_id']) || !isset($input['usuari_id'])) {
    echo json_encode(["success" => false, "message" => "Faltan parámetros"]);
    exit;
}

$grup_id = intval($input['grup_id']);
$usuari_id = intval($input['usuari_id']);

try {
    $db = new ConexionBD();
    $conn = $db->getConexion();

    // Verificamos que el miembro existe en ese grupo
    $stmtCheck = $conn->prepare("SELECT * FROM grup_usuaris WHERE grup_id = ? AND usuari_id = ?");
    $stmtCheck->bind_param("ii", $grup_id, $usuari_id);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();
    if ($resCheck->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "El usuario no pertenece a este grupo"]);
        exit;
    }
    $stmtCheck->close();

    // Eliminamos el miembro
    $stmt = $conn->prepare("DELETE FROM grup_usuaris WHERE grup_id = ? AND usuari_id = ?");
    $stmt->bind_param("ii", $grup_id, $usuari_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(["success" => true, "message" => "Miembro expulsado correctamente"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
