<?php
// Inicia la sesión para acceder a los datos del admin logueado
session_start();
require_once("../Model/DataBase.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['protocolo'])) {
    $protocolo = intval($_POST['protocolo']);
    $db = new ConexionBD();
    $conn = $db->getConexion();

    try {
        $sql = "UPDATE ayuda SET cerrado = 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->execute([$protocolo]);
            $stmt->close();
            echo json_encode(['success' => true]);
            exit();
        } else {
            throw new Exception("Database statement preparation failed");
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit();
    }
}

// If not POST or protocolo not set
http_response_code(400);
echo json_encode(['success' => false, 'error' => 'Invalid request']);
exit();