<?php
session_start();
header('Content-Type: application/json');
require_once("../Model/DataBase.php");

if (!isset($_SESSION['usuarioID'])) {
    echo json_encode([
        "success" => false,
        "message" => "Usuario no autenticado"
    ]);
    exit;
}

$idUsuario = $_SESSION['usuarioID'];

// Crear instancia y obtener conexión MySQLi
$db = new ConexionBD();
$conn = $db->getConexion();

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Error de conexión: " . $conn->connect_error
    ]);
    exit;
}

$sql = "
    SELECT u.id, u.name, u.imagen
    FROM matches m
    JOIN usuario u ON (
        (m.usuario1_id = ? AND m.usuario2_id = u.id) OR 
        (m.usuario2_id = ? AND m.usuario1_id = u.id)
    )
    WHERE m.aceptado = 1
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Error al preparar la consulta: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param("ii", $idUsuario, $idUsuario);
$stmt->execute();

$result = $stmt->get_result();

$matches = [];
while ($row = $result->fetch_assoc()) {
    $matches[] = $row;
}

$stmt->close();

echo json_encode([
    "success" => true,
    "matches" => $matches
]);
