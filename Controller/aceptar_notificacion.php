<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
include_once '../Model/DataBase.php';

// Log útil
error_log("🧪 POST ID: " . ($_POST['id'] ?? 'NO ID'));
error_log("🧪 SESSION ID: " . ($_SESSION['usuarioID'] ?? 'NO SESSION'));
file_put_contents("debug.log", "POST: " . print_r($_POST, true) . "\nSESSION: " . print_r($_SESSION, true), FILE_APPEND);

// Comprovació de dades
if (!isset($_SESSION['usuarioID']) || !isset($_POST['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Falta información']);
    exit();
}

$usuario_id = intval($_SESSION['usuarioID']);
$notificacion_id = intval($_POST['id']);

$bd = new ConexionBD();
$conn = $bd->getConexion();

// Agafem autorLikeId directament
$sql = "SELECT autorLikeId FROM notificaciones WHERE id = ? AND usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $notificacion_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();


if (!$result || $result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Notificación no encontrada']);
    exit();
}

$row = $result->fetch_assoc();
$autor_id = intval($row['autorLikeId']);

if (!$autor_id) {
    echo json_encode(['status' => 'error', 'message' => 'autorLikeId no disponible']);
    exit();
}

if ($autor_id === $usuario_id) {
    echo json_encode(['status' => 'error', 'message' => 'No pots fer match amb tu mateix']);
    exit();
}

// Preparar el match
$u1 = min($usuario_id, $autor_id);
$u2 = max($usuario_id, $autor_id);

// Comprovar si ja existeix
$check = $conn->prepare("SELECT id FROM matches WHERE usuario1_id = ? AND usuario2_id = ?");
$check->bind_param("ii", $u1, $u2);
$check->execute();
$res = $check->get_result();

// Inserir si no existeix
if ($res->num_rows === 0) {
    $insert = $conn->prepare("INSERT INTO matches (usuario1_id, usuario2_id, aceptado) VALUES (?, ?, ?)");
    $aceptado = 1;
    $insert->bind_param("iii", $u1, $u2, $aceptado);
    $insert->execute();
    error_log("✅ Match creat entre $u1 i $u2");
}

// Eliminar notificació
$delete = $conn->prepare("DELETE FROM notificaciones WHERE id = ?");
$delete->bind_param("i", $notificacion_id);
$delete->execute();
error_log("🗑 Notificació $notificacion_id eliminada");

// Èxit
echo json_encode(['status' => 'success', 'message' => 'Match creat i notificació eliminada']);
