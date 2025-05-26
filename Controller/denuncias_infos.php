<?php
header('Content-Type: application/json');
require_once("../Model/DataBase.php");
require_once("../Model/user.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$db = new ConexionBD();
$conn = $db->getConexion();

if (!$conn) {
    error_log("Error de conexión a la base de datos");
    http_response_code(500);
    echo json_encode(["status" => "error"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['id'])) {
        error_log("Parámetro 'id' requerido");
        http_response_code(400);
        echo json_encode(["status" => "error"]);
        exit;
    }

    $id = intval($_GET['id']);
    if ($id <= 0) {
        error_log("ID inválido");
        http_response_code(400);
        echo json_encode(["status" => "error"]);
        exit;
    }

    $sql = "
    SELECT 
        r.id AS denuncia_id,
        u2.name AS denunciante,
        u1.name AS denunciado,
        rt.name AS tipo_denuncia,
        r.description AS motivo,
        rt.description AS descricao, 
        r.created_at AS data,
        r.history_id AS historia_id,
        r.chat_id AS chat_id,
        r.status AS status
    FROM 
        reports r
    JOIN 
        usuario u1 ON r.target_user_id = u1.id  
    JOIN 
        usuario u2 ON r.reporting_user_id = u2.id  
    JOIN 
        report_types rt ON r.report_type_id = rt.id 
    WHERE 
        r.id = ?
    LIMIT 1;
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Error al preparar la consulta: " . $conn->error);
        http_response_code(500);
        echo json_encode(["status" => "error"]);
        exit;
    }

    $stmt->bind_param("i", $id);
    if (!$stmt->execute()) {
        error_log("Error al ejecutar la consulta: " . $stmt->error);
        http_response_code(500);
        echo json_encode(["status" => "error"]);
        exit;
    }

    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        error_log("Denúncia no encontrada para id: $id");
        http_response_code(404);
        echo json_encode(["status" => "error"]);
        exit;
    }

    $denuncia = $result->fetch_assoc();
    $stmt->close();
    $conn->close();

    // Formatear la fecha para el frontend
    $denuncia['data'] = date('Y-m-d H:i:s', strtotime($denuncia['data']));

    echo json_encode($denuncia);
    exit;
}

http_response_code(405);
echo json_encode(["status" => "error"]);
?>