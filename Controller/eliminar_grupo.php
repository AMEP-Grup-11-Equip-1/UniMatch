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

    // 1. Verificar si el usuario es el propietario
    $stmtCheck = $conn->prepare("SELECT propietari_id FROM grups WHERE id = ?");
    $stmtCheck->bind_param("i", $grup_id);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();

    if ($resCheck->num_rows === 0) {
        echo json_encode(["success" => false, "message" => "Grupo no encontrado"]);
        exit;
    }

    $grupo = $resCheck->fetch_assoc();
    if ($grupo['propietari_id'] !== $usuari_id) {
        echo json_encode(["success" => false, "message" => "No tienes permisos para eliminar este grupo"]);
        exit;
    }
    $stmtCheck->close();

    // 2. Eliminar relaciones de usuarios
    $stmtDelUsers = $conn->prepare("DELETE FROM grup_usuaris WHERE grup_id = ?");
    $stmtDelUsers->bind_param("i", $grup_id);
    $stmtDelUsers->execute();
    $stmtDelUsers->close();

    // 3. Eliminar el grupo
    $stmtDelGroup = $conn->prepare("DELETE FROM grups WHERE id = ?");
    $stmtDelGroup->bind_param("i", $grup_id);
    $stmtDelGroup->execute();
    $stmtDelGroup->close();

    echo json_encode(["success" => true, "message" => "Grupo eliminado con éxito"]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
