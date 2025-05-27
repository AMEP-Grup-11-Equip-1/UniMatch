<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

require_once("../Model/DataBase.php");

header('Content-Type: application/json');

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuarioID'])) {
    echo json_encode(["success" => false, "message" => "No hay sesión iniciada"]);
    exit;
}

$usuarioID = $_SESSION['usuarioID'];

if (!isset($_GET['grupo_id'])) {
    echo json_encode(["success" => false, "message" => "Falta grupo_id"]);
    exit;
}

$grupo_id = intval($_GET['grupo_id']);

$db = new ConexionBD();
$conn = $db->getConexion();

if (!$conn) {
    echo json_encode(["success" => false, "message" => "Error en la conexión a la base de datos"]);
    exit;
}

$sql = "SELECT mg.*, u.name AS emisor_nombre
        FROM mensajes_grupo mg
        JOIN usuario u ON mg.emisor_id = u.id
        WHERE mg.grupo_id = ?
        ORDER BY mg.fecha ASC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["success" => false, "message" => "Error en la consulta SQL"]);
    exit;
}

$stmt->bind_param("i", $grupo_id);

if (!$stmt->execute()) {
    echo json_encode(["success" => false, "message" => "Error al ejecutar la consulta"]);
    $stmt->close();
    $conn->close();
    exit;
}

$result = $stmt->get_result();

$mensajes = [];
while ($row = $result->fetch_assoc()) {
    $mensajes[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode([
    "success" => true,
    "mensajes" => $mensajes
]);
