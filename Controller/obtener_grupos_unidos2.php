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
$modo = $_GET['modo'] ?? 'todos';

$db = new ConexionBD();
$conn = $db->getConexion();

if ($modo === 'unidos') {
    // Solo los grupos en los que está unido (grup_usuaris)
    $sql = "
        SELECT g.* 
        FROM grups g
        INNER JOIN grup_usuaris gu ON g.id = gu.grup_id
        WHERE gu.usuari_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Error preparando la consulta: " . $conn->error);
        echo json_encode(["success" => false, "message" => "Error en la consulta"]);
        exit;
    }
    $stmt->bind_param("i", $usuarioID);
} else {
    // Todos los grupos
    $sql = "SELECT * FROM grups";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Error preparando la consulta: " . $conn->error);
        echo json_encode(["success" => false, "message" => "Error en la consulta"]);
        exit;
    }
}

if (!$stmt->execute()) {
    error_log("Error ejecutando la consulta: " . $stmt->error);
    echo json_encode(["success" => false, "message" => "Error ejecutando la consulta"]);
    exit;
}

$result = $stmt->get_result();

$grupos = [];
while ($row = $result->fetch_assoc()) {
    $grupos[] = $row;
}

echo json_encode([
    "success" => true,
    "count" => count($grupos),
    "grupos" => $grupos,
    "debug" => [
        "usuarioID" => $usuarioID,
        "modo" => $modo,
        "sql" => $sql
    ]
]);
