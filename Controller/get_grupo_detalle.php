<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../Model/DataBase.php';

$input = json_decode(file_get_contents("php://input"), true);
if (!isset($input['grup_id'])) {
    echo json_encode(["success" => false, "message" => "Falta el ID del grupo"]);
    exit;
}

$grup_id = intval($input['grup_id']);

try {
    $db = new ConexionBD();
    $conn = $db->getConexion();

    // 1. Obtener datos del grupo (incluye propietari_id)
    $stmt = $conn->prepare("SELECT nom, descripcio, visibilitat, propietari_id FROM grups WHERE id = ?");
    $stmt->bind_param("i", $grup_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $grupo = $result->fetch_assoc();
    $stmt->close();

    // 2. Obtener nombre del propietario
    $propietarioNombre = "";
    $propietario_id = $grupo['propietari_id'];

    $stmtProp = $conn->prepare("SELECT name FROM usuario WHERE id = ?");
    $stmtProp->bind_param("i", $propietario_id);
    $stmtProp->execute();
    $resProp = $stmtProp->get_result();
    if ($row = $resProp->fetch_assoc()) {
        $propietarioNombre = $row['name'];
    }
    $stmtProp->close();

    // 3. Obtener IDs y nombres de los miembros
    $stmt2 = $conn->prepare("
        SELECT u.id, u.name 
        FROM grup_usuaris gu 
        INNER JOIN usuario u ON gu.usuari_id = u.id
        WHERE gu.grup_id = ?
    ");
    $stmt2->bind_param("i", $grup_id);
    $stmt2->execute();
    $res2 = $stmt2->get_result();

    $miembros = [];
    while ($row = $res2->fetch_assoc()) {
        $miembros[] = [
            "id" => $row['id'],
            "nombre" => $row['name']
        ];
    }
    $stmt2->close();

    // 4. Respuesta final
    echo json_encode([
        "nombre" => $grupo['nom'],
        "descripcion" => $grupo['descripcio'],
        "visibilidad" => $grupo['visibilitat'],
        "propietario_id" => $propietario_id,
        "propietario_nombre" => $propietarioNombre,
        "miembros" => $miembros
    ]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
