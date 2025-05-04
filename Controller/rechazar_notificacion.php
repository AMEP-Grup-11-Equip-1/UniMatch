<?php
session_start();
header('Content-Type: application/json');
include_once '../Model/DataBase.php';

if (!isset($_POST['id']) || !isset($_SESSION['usuarioID'])) {
    echo json_encode(['status' => 'error', 'message' => 'Falta información']);
    exit();
}

$noti_id = intval($_POST['id']);
$usuario_id = $_SESSION['usuarioID'];

$bd = new ConexionBD();
$conn = $bd->getConexion();

// Comprovem que la notificació sigui d’aquest usuari
$stmt = $conn->prepare("DELETE FROM notificaciones WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $noti_id, $usuario_id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Notificación eliminada']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No se pudo eliminar']);
}
