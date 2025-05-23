<?php
require_once("../Model/Grup.php");
require_once("../Model/DataBase.php");

header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['usuarioID'])) {
    echo json_encode(["success" => false, "message" => "No hay sesión iniciada"]);
    exit;
}

$usuarioID = $_SESSION['usuarioID'];

$db = new ConexionBD();
$conn = $db->getConexion();

$sql = "SELECT g.* FROM grups g
        INNER JOIN grup_usuaris gu ON g.id = gu.grup_id
        WHERE gu.usuari_id = ? or g.propietari_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $usuarioID, $usuarioID);
$stmt->execute();
$result = $stmt->get_result();

$grupos = [];
while ($row = $result->fetch_assoc()) {
    $grupos[] = $row;
}

echo json_encode([
    "success" => true,
    "grupos" => $grupos
]);
