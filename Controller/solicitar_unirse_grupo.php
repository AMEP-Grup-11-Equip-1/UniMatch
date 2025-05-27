<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../Model/DataBase.php';

if (!isset($_SESSION['usuarioID'])) {
    echo json_encode(["success" => false, "message" => "No has iniciado sesión"]);
    exit;
}

$usuarioID = $_SESSION['usuarioID'];
$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['grupo_id'])) {
    echo json_encode(["success" => false, "message" => "Falta el ID del grupo"]);
    exit;
}

$grupoID = intval($input['grupo_id']);

try {
    $db = new ConexionBD();
    $conn = $db->getConexion();

    // Obtener el propietario del grupo
    $q = $conn->prepare("SELECT propietari_id, nom FROM grups WHERE id = ?");
    $q->bind_param("i", $grupoID);
    $q->execute();
    $r = $q->get_result();
    $grupo = $r->fetch_assoc();

    if (!$grupo) {
        echo json_encode(["success" => false, "message" => "Grupo no encontrado"]);
        exit;
    }

    $destinatari_id = intval($grupo['propietari_id']);
    $nom_grup = $grupo['nom'];

    // Comprobar si ya se ha enviado una invitación
    $check = $conn->prepare("SELECT id FROM invitacions_grups WHERE grup_id = ? AND usuari_id = ? AND estado IN('pendiente', 'aceptado')");
    $check->bind_param("ii", $grupoID, $usuarioID);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Ya has solicitado unirte a este grupo"]);
        exit;
    }

    // Insertar la solicitud
    $stmt = $conn->prepare("INSERT INTO invitacions_grups (grup_id, usuari_id, destinatari_id) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $grupoID, $usuarioID, $destinatari_id);
    $stmt->execute();

    // Obtener el nombre del solicitante
    $stmtUser = $conn->prepare("SELECT name FROM usuario WHERE id = ?");
    $stmtUser->bind_param("i", $usuarioID);
    $stmtUser->execute();
    $resUser = $stmtUser->get_result();
    $solicitante = $resUser->fetch_assoc();
    $nom_usuari = $solicitante['name'] ?? 'Un usuario';

    // Crear la notificación
    $mensaje = "$nom_usuari ha solicitado unirse al grupo \"$nom_grup\"";
    $stmtNoti = $conn->prepare("INSERT INTO notificaciones (usuario_id, mensaje, tipo, fecha, autorLikeId) VALUES (?, ?, 'grupo', NOW(), ?)");
    $stmtNoti->bind_param("isi", $destinatari_id, $mensaje, $usuarioID);
    $stmtNoti->execute();

    echo json_encode(["success" => true, "message" => "Solicitud y notificación enviadas"]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
}
