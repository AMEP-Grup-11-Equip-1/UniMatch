<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../Model/DataBase.php';

if (!isset($_SESSION['usuarioID'])) {
    echo json_encode(["status" => "error", "message" => "No estás logueado."]);
    exit;
}

$usuarioID = $_SESSION['usuarioID'];

try {
    $db = new ConexionBD();
    $conn = $db->getConexion();

    $conn->begin_transaction();

    // 🔥 Eliminar verificaciones antes que el usuario
    $stmt = $conn->prepare("DELETE FROM verifications WHERE user = ?");
    $stmt->bind_param("i", $usuarioID);
    $stmt->execute();
    $stmt->close();

    $queries = [
        "DELETE FROM grup_usuaris WHERE grup_id IN (SELECT id FROM grups WHERE propietari_id = ?)",
        "DELETE FROM mensajes_adm WHERE protocolo IN (SELECT id FROM ayuda WHERE usuario_id = ?)",
        "DELETE FROM ayuda WHERE usuario_id = ?",
        "DELETE FROM matches WHERE usuario1_id = ? OR usuario2_id = ?",
        "DELETE FROM mensajes WHERE emisor = ? OR receptor = ?",
        "DELETE FROM mensajes_grupo WHERE emisor_id = ?",
        "DELETE FROM notificaciones WHERE usuario_id = ? OR autorLikeId = ?",
        "DELETE FROM likes WHERE usuario_id = ?",
        "DELETE FROM grup_usuaris WHERE usuari_id = ?",
        "DELETE FROM invitacions_grups WHERE usuari_id = ? OR destinatari_id = ?",
        "DELETE FROM historias WHERE usuario_id = ?",
        "DELETE FROM reports WHERE target_user_id = ? OR reporting_user_id = ?"
    ];

    foreach ($queries as $sql) {
        $stmt = $conn->prepare($sql);
        if (strpos($sql, 'OR') !== false) {
            $stmt->bind_param("ii", $usuarioID, $usuarioID);
        } else {
            $stmt->bind_param("i", $usuarioID);
        }
        $stmt->execute();
        $stmt->close();
    }

    // Eliminar al usuario
    $stmt = $conn->prepare("DELETE FROM usuario WHERE id = ?");
    $stmt->bind_param("i", $usuarioID);
    $stmt->execute();
    $stmt->close();

    // 🧹 Limpiar grupos cuyos propietarios ya no existen
    $conn->query("DELETE FROM grups WHERE propietari_id NOT IN (SELECT id FROM usuario)");

    // 🧹 Limpiar historias cuyos usuarios ya no existen
    $conn->query("DELETE FROM historias WHERE usuario_id NOT IN (SELECT id FROM usuario)");

    $conn->commit();
    session_destroy();

    echo json_encode(["status" => "success", "message" => "Cuenta eliminada correctamente."]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => "Error al eliminar la cuenta: " . $e->getMessage()]);
}


