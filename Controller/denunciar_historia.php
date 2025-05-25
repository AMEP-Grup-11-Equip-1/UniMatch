<?php
session_start();
require_once("../Model/DataBase.php");

header('Content-Type: application/json');

if (!isset($_SESSION['usuarioID'])) {
    echo json_encode([
        "success" => false,
        "message" => "Usuario no autenticado"
    ]);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['story_id']) || !isset($_SESSION['usuarioID']) || !isset($_POST['reprtType'])) {
        echo json_encode(['success' => false, 'error' => 'Missing parameters']);
        exit();
    }

    $sql = "
            INSERT INTO `reports` (`target_user_id`, `reporting_user_id`, `report_type_id`, `history_id`)
            VALUES (
            (SELECT usuario_id FROM historias WHERE id = ?),
            ?, ?, ?
            );
    ";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("is", $story_id, $usuarioID, $reprtType, $storyId);

        echo json_encode(["success" => true]);
        http_response_code(200);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "error" => "DB error"]);
    }
    exit;
}
