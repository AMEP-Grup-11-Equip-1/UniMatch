<?php
header('Content-Type: application/json');
// Incluye los archivos necesarios para la conexión y el modelo de usuario
require_once("../Model/DataBase.php");
require_once("../Model/user.php");

// Crea una nueva instancia de la base de datos y obtiene la conexión
$db = new ConexionBD();
$conn = $db->getConexion();

// Verifica si la conexión fue exitosa
if (!$conn) {
    echo json_encode(["status" => "error", "message" => "Error de conexión"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['id'])) {
        echo json_encode(["status" => "error", "message" => "Falta el parámetro id"]);
        exit;
    }

    $user_id = intval($_GET['id']);
    $response = [
        "status" => "success",
        "user_id" => $user_id,
        "denuncias_recebidas" => [],
        "denuncias_feitas" => []
    ];

    // Verifica denúncias RECEBIDAS pelo usuário (quando outros denunciaram ele)
    $sql_recebidas = "
        SELECT 
            u2.name AS denunciante,
            rt.name AS tipo_denuncia,
            r.created_at AS data
        FROM 
            reports r
        JOIN 
            usuario u1 ON r.target_user_id = u1.id
        JOIN 
            usuario u2 ON r.reporting_user_id = u2.id
        JOIN 
            report_types rt ON r.report_type_id = rt.id
        WHERE 
            r.target_user_id = ?
        ORDER BY 
            r.created_at DESC";
    
    $stmt = $conn->prepare($sql_recebidas);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result_recebidas = $stmt->get_result();
    
    while ($row = $result_recebidas->fetch_assoc()) {
        $response["denuncias_recebidas"][] = $row;
    }
    $stmt->close();

    // Verifica denúncias FEITAS pelo usuário (quando ele denunciou outros)
    $sql_feitas = "
        SELECT 
            u1.name AS denunciado,
            rt.name AS tipo_denuncia,
            r.created_at AS data
        FROM 
            reports r
        JOIN 
            usuario u1 ON r.target_user_id = u1.id
        JOIN 
            usuario u2 ON r.reporting_user_id = u2.id
        JOIN 
            report_types rt ON r.report_type_id = rt.id
        WHERE 
            r.reporting_user_id = ?
        ORDER BY 
            r.created_at DESC";
    
    $stmt = $conn->prepare($sql_feitas);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result_feitas = $stmt->get_result();
    
    while ($row = $result_feitas->fetch_assoc()) {
        $response["denuncias_feitas"][] = $row;
    }
    $stmt->close();

    // Verifica se há denúncias e adiciona mensagens apropriadas
    if (empty($response["denuncias_recebidas"]) && empty($response["denuncias_feitas"])) {
        $response["message"] = "O usuário não está envolvido em nenhuma denúncia";
    } else {
        if (!empty($response["denuncias_recebidas"])) {
            $response["message_recebidas"] = "O usuário foi denunciado por outros usuários";
        }
        if (!empty($response["denuncias_feitas"])) {
            $response["message_feitas"] = "O usuário fez denúncias contra outros usuários";
        }
    }

    echo json_encode($response);
    exit;
}

echo json_encode(["status" => "error", "message" => "Método no permitido"]);
?>