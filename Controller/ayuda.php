<?php
session_start();
require_once("../Model/DataBase.php");

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['protocolo'])) {
    $protocolo = intval($_GET['protocolo']);
    $db = new ConexionBD();
    $conn = $db->getConexion();

    $sql = "SELECT ma.mensaje, ma.fecha, u.name AS usuario, ma.id_adm, adm.name AS admin_name
            FROM mensajes_adm ma
            JOIN ayuda a ON ma.protocolo = a.id
            JOIN usuario u ON a.usuario_id = u.id
            LEFT JOIN ADM adm ON ma.id_adm = adm.id
            WHERE ma.protocolo = ?
            ORDER BY ma.fecha ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $protocolo);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $isAdmin = !is_null($row['id_adm']);
        
        // Check if the message is from the currently logged-in admin
        $isCurrentAdmin = $isAdmin && isset($_SESSION['admin']['id']) && intval($row['id_adm']) == intval($_SESSION['admin']['id']);
        
        // Determine sender name
        $senderName = $isAdmin ? 
                     ($isCurrentAdmin ? "TU" : $row['admin_name']) : 
                     $row['usuario'];
        
        $messages[] = [
            "text" => htmlspecialchars($row['mensaje']),
            "userImage" => $isAdmin ? "" : "https://ui-avatars.com/api/?name=" . urlencode($row['usuario']),
            "usuario" => $senderName,
            "fecha" => $row['fecha'],
            "isAdmin" => $isAdmin,
            "isCurrentUser" => $isCurrentAdmin,  // Add this flag if you need it in your frontend
            "original_sender" => $isAdmin ? "admin_".$row['id_adm'] : "user_".$row['usuario_id']
        ];
    }
    $stmt->close();
    echo json_encode(["messages" => $messages]);
    exit;
}

$response["status"] = "error";
$response["message"] = "Método no permitido ou dados incompletos.";
echo json_encode($response);
?>