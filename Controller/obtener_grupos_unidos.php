<?php
require_once __DIR__ . '/../Model/DataBase.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents("php://input"), true);
$usuari_id = $input["usuari_id"] ?? null;

if (!$usuari_id) {
    echo json_encode(["error" => "No se recibió el ID de usuario"]);
    exit;
}

try {
    $db = new ConexionBD();
    $conn = $db->getConexion();

    $stmt = $conn->prepare("
        SELECT g.id, g.nom, g.descripcio, g.visibilitat, g.propietari_id, g.imagen
        FROM grups g
        INNER JOIN grup_usuaris gu ON gu.grup_id = g.id
        WHERE gu.usuari_id = ?
    ");
    $stmt->bind_param("i", $usuari_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $grupos = [];

    while ($row = $result->fetch_assoc()) {
        $grupos[] = [
            "id" => $row["id"],
            "nom" => $row["nom"],
            "descripcio" => $row["descripcio"],
            "visibilitat" => $row["visibilitat"],
            "propietari_id" => $row["propietari_id"],
            "imagen" => $row["imagen"] ?? null  // ✅ Ahora se incluye la imagen
        ];
    }

    echo json_encode($grupos);
} catch (Exception $e) {
    echo json_encode(["error" => "Error al obtener grupos unidos: " . $e->getMessage()]);
}
