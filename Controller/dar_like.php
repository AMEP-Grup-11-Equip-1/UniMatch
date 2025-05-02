<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include_once '../Model/DataBase.php';

$log = function($msg) { error_log("🔔 " . $msg); };

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['historia_id']) && isset($_SESSION['usuarioID'])) {
    $bd = new ConexionBD();
    $conn = $bd->getConexion();

    $historia_id = intval($_POST['historia_id']);
    $usuario_id = intval($_SESSION['usuarioID']);

    $log("LIKE fet per usuari $usuario_id a història $historia_id");

    // Comprova si el like ja existeix
    $check = $conn->prepare("SELECT * FROM likes WHERE historia_id = ? AND usuario_id = ?");
    $check->bind_param("ii", $historia_id, $usuario_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        // Desa el like
        $stmt = $conn->prepare("INSERT INTO likes (historia_id, usuario_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $historia_id, $usuario_id);
        $stmt->execute();

        // 🔍 Busquem l'autor de la història
        $qAutor = $conn->prepare("SELECT usuario_id FROM historias WHERE id = ?");
        $qAutor->bind_param("i", $historia_id);
        $qAutor->execute();
        $rAutor = $qAutor->get_result();
        $autor = $rAutor->fetch_assoc();

        if ($autor && $autor['usuario_id'] != $usuario_id) {
            $receptor_id = $autor['usuario_id'];
            $mensaje = "T'han donat like a una de les teves històries!";

            $noti = $conn->prepare("INSERT INTO notificaciones (usuario_id, mensaje, leida) VALUES (?, ?, 0)");
            $noti->bind_param("is", $receptor_id, $mensaje);

            $noti->execute();

            $log("Notificació enviada a l'usuari $receptor_id");
        }

        echo json_encode(["success" => true, "message" => "Like guardado i notificació enviada"]);
    } else {
        echo json_encode(["success" => false, "message" => "Ya habías dado like"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Faltan datos"]);
}
?>
