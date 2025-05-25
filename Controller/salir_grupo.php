<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../Model/DataBase.php';

session_start();

$input = json_decode(file_get_contents("php://input"), true);
if (!isset($input['grup_id']) || !isset($_SESSION['usuarioID'])) {
    echo json_encode(["success" => false, "message" => "Faltan datos o sesión"]);
    exit;
}

$grup_id = intval($input['grup_id']);
$usuari_id = intval($_SESSION['usuarioID']);

try {
    $db = new ConexionBD();
    $conn = $db->getConexion();

    // Validación opcional
    $verifica = $conn->prepare("SELECT * FROM grup_usuaris WHERE grup_id = ? AND usuari_id = ?");
    $verifica->bind_param("ii", $grup_id, $usuari_id);
    $verifica->execute();
    $res = $verifica->get_result();
    if ($res->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "No se encontró relación en la base de datos"]);
        exit;
    }
    $verifica->close();

    // Eliminar relación
    $stmt = $conn->prepare("DELETE FROM grup_usuaris WHERE grup_id = ? AND usuari_id = ?");
    $stmt->bind_param("ii", $grup_id, $usuari_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "Has salido del grupo correctamente"]);
    } else {
        echo json_encode(["success" => false, "message" => "No se eliminó ningún registro"]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
