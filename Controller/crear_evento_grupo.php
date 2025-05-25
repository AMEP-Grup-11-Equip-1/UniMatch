<?php
require_once __DIR__ . '/../Model/DataBase.php';

header('Content-Type: application/json');

// Leer datos del JSON enviado desde el frontend
$data = json_decode(file_get_contents("php://input"), true);

$grup_id = $data["grup_id"] ?? null;
$activitat = $data["activitat"] ?? null;
$descripcio = $data["descripcio"] ?? null;
$data_event = $data["data_event"] ?? null;

if (!$grup_id || !$activitat || !$descripcio || !$data_event) {
    echo json_encode([
        "success" => false,
        "message" => "Faltan datos para crear el evento."
    ]);
    exit;
}

try {
    $db = new ConexionBD();
    $conn = $db->getConexion();

    $stmt = $conn->prepare("INSERT INTO grup_calendari (grup_id, activitat, descripcio, data_event) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $grup_id, $activitat, $descripcio, $data_event);
    $stmt->execute();

    echo json_encode([
        "success" => true,
        "message" => "Evento guardado correctamente."
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error al guardar evento: " . $e->getMessage()
    ]);
    exit;
}
