<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../Model/DataBase.php';

$raw = file_get_contents("php://input");
$data = json_decode($raw ?: '{}', true);

if (!isset($data['usuari_id'])) {
    echo json_encode(["success" => false, "message" => "Falta el ID del usuario"]);
    exit;
}

$usuari_id = (int)$data['usuari_id'];

try {
    $db = new ConexionBD();
    $conn = $db->getConexion(); // ✅ ESTE ES TU MÉTODO REAL

    $stmt = $conn->prepare("
        SELECT g.id, g.nom, g.descripcio, g.visibilitat
        FROM grups g
        INNER JOIN grup_usuaris gu ON gu.grup_id = g.id
        WHERE gu.usuari_id = ?
          AND g.propietari_id != ?
    ");

    if (!$stmt) {
        throw new Exception("Error al preparar la consulta SQL: " . $conn->error);
    }

    $stmt->bind_param("ii", $usuari_id, $usuari_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $grupos = [];
    while ($row = $result->fetch_assoc()) {
        $grupos[] = $row;
    }

    echo json_encode($grupos);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error al obtener los grupos unidos",
        "detalle" => $e->getMessage()
    ]);
}
