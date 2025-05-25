<?php
require_once __DIR__ . '/../Model/DataBase.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$grupo_id = $data["grupo_id"] ?? null;
$nueva_descripcion = $data["nueva_descripcion"] ?? null;

// Validación
if (!$grupo_id || $nueva_descripcion === null) {
    echo json_encode([
        "success" => false,
        "message" => "Faltan datos para actualizar la descripción."
    ]);
    exit;
}

try {
    $db = new ConexionBD();
    $conn = $db->getConexion();

    $stmt = $conn->prepare("UPDATE grups SET descripcio = ? WHERE id = ?");
    $stmt->bind_param("si", $nueva_descripcion, $grupo_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode([
            "success" => true,
            "message" => "Descripción actualizada correctamente."
        ]);
    } else {
        echo json_encode([
            "success" => true,
            "message" => "La descripción no cambió o ya estaba actualizada."
        ]);
    }

    $stmt->close();
    exit;

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
    exit;
}
