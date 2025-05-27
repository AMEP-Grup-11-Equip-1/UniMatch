<?php
session_start();
header('Content-Type: application/json');
require_once("../Model/DataBase.php");

if (!isset($_SESSION['usuarioID'])) {
    echo json_encode(["success" => false, "message" => "Usuario no autenticado"]);
    exit;
}

$emisor_id = $_SESSION['usuarioID'];
$receptor_id = $_POST['receptor_id'] ?? null;
$mensaje = trim($_POST['mensaje'] ?? '');

if (!$receptor_id || !$mensaje) {
    echo json_encode(["success" => false, "message" => "Datos incompletos"]);
    exit;
}

$db = new ConexionBD();
$conn = $db->getConexion();

// 1. Guardar el mensaje
$stmt = $conn->prepare("INSERT INTO mensajes (emisor, receptor, mensaje) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $emisor_id, $receptor_id, $mensaje);

if ($stmt->execute()) {
    // 2. Obtener el nombre del emisor para la notificación
    $stmtNombre = $conn->prepare("SELECT name FROM usuario WHERE id = ?");
    $stmtNombre->bind_param("i", $emisor_id);
    
    if ($stmtNombre->execute()) {
        $result = $stmtNombre->get_result();
        $emisor = $result->fetch_assoc();
        $nom = $emisor['name'] ?? 'Alguien';

        // 3. Crear el texto de la notificación y guardarla
        $texto = $nom . " te ha enviado un mensaje";
        $stmtNoti = $conn->prepare("INSERT INTO notificaciones (usuario_id, mensaje, tipo, fecha) VALUES (?, ?, 'chat', NOW())");
        $stmtNoti->bind_param("is", $receptor_id, $texto);
        
        if (!$stmtNoti->execute()) {
            echo json_encode([
                "success" => false,
                "message" => "Error al guardar la notificación: " . $stmtNoti->error
            ]);
            exit;
        }

    } else {
        echo json_encode([
            "success" => false,
            "message" => "Error obteniendo nombre del emisor: " . $stmtNombre->error
        ]);
        exit;
    }

    echo json_encode(["success" => true]);

} else {
    echo json_encode([
        "success" => false,
        "message" => "Error al guardar mensaje: " . $stmt->error
    ]);
}

