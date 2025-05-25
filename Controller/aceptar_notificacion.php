<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
include_once '../Model/DataBase.php';

if (!isset($_SESSION['usuarioID']) || !isset($_POST['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Falta información']);
    exit();
}

$usuario_id = intval($_SESSION['usuarioID']);
$notificacion_id = intval($_POST['id']);

$bd = new ConexionBD();
$conn = $bd->getConexion();

// Obtenir dades de la notificació
$sql = "SELECT autorLikeId, tipo FROM notificaciones WHERE id = ? AND usuario_id = ?";
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
$tipo = $row['tipo'];

if (!$autor_id) {
    echo json_encode(['status' => 'error', 'message' => 'autorLikeId no disponible']);
    exit();
}

if ($tipo === 'match') {
    if ($autor_id === $usuario_id) {
        echo json_encode(['status' => 'error', 'message' => 'No pots fer match amb tu mateix']);
        exit();
    }

    $u1 = min($usuario_id, $autor_id);
    $u2 = max($usuario_id, $autor_id);

    $check = $conn->prepare("SELECT id FROM matches WHERE usuario1_id = ? AND usuario2_id = ?");
    $check->bind_param("ii", $u1, $u2);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows === 0) {
        $insert = $conn->prepare("INSERT INTO matches (usuario1_id, usuario2_id, aceptado) VALUES (?, ?, 1)");
        $insert->bind_param("ii", $u1, $u2);
        $insert->execute();
    }
} elseif ($tipo === 'grupo') {
    // Afegir usuari al grup i actualitzar estat de la invitació
    $grupo_id = null;

    $stmtInv = $conn->prepare("SELECT grup_id FROM invitacions_grups WHERE usuari_id = ? AND destinatari_id = ? AND estado = 'pendiente' LIMIT 1");
    $stmtInv->bind_param("ii", $autor_id, $usuario_id);
    $stmtInv->execute();
    $resInv = $stmtInv->get_result();

    if ($resInv && $resInv->num_rows > 0) {
        $inv = $resInv->fetch_assoc();
        $grupo_id = $inv['grup_id'];

        // Afegir l'usuari al grup
        $stmtAdd = $conn->prepare("INSERT INTO grup_usuari (grup_id, usuari_id, rol) VALUES (?, ?, 'integrant')");
        $stmtAdd->bind_param("ii", $grupo_id, $autor_id);
        $stmtAdd->execute();

        // Marcar com acceptada la invitació
        $stmtUpdate = $conn->prepare("UPDATE invitacions_grups SET estado = 'aceptado' WHERE grup_id = ? AND usuari_id = ?");
        $stmtUpdate->bind_param("ii", $grupo_id, $autor_id);
        $stmtUpdate->execute();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se encontró la invitación correspondiente']);
        exit();
    }
}

// Eliminar notificació
$delete = $conn->prepare("DELETE FROM notificaciones WHERE id = ?");
$delete->bind_param("i", $notificacion_id);
$delete->execute();

echo json_encode(['status' => 'success', 'message' => 'Notificación aceptada i eliminada']);
