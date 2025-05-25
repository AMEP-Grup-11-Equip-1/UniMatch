<?php
session_start();
header('Content-Type: application/json');
include_once '../Model/DataBase.php';

$response = ['data' => [], 'status' => 'error'];

if (!isset($_SESSION['usuarioID'])) {
    $response['message'] = "No hay sesión iniciada";
    echo json_encode($response);
    exit();
}

$usuario_id = $_SESSION['usuarioID'];

try {
    $bd = new ConexionBD();
    $conn = $bd->getConexion();

   $sql = "SELECT id, mensaje, fecha, leida, autorLike, autorLikeId, tipo
        FROM notificaciones 
        WHERE usuario_id = ? 
        ORDER BY fecha DESC";

    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $notificaciones = [];

    while ($row = $result->fetch_assoc()) {
        // Format opcional de la data si vols enviar-la en string
        $row['fecha'] = date("Y-m-d H:i:s", strtotime($row['fecha']));
        $notificaciones[] = $row;
    }

    $response['data'] = $notificaciones;
    $response['status'] = 'success';

} catch (Exception $e) {
    $response['message'] = "Error al obtener notificaciones: " . $e->getMessage();
}

echo json_encode($response);
?>
