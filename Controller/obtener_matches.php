<?php
session_start();
header('Content-Type: application/json');
include_once '../Model/DataBase.php';

$response = ['success' => false, 'matches' => []];

if (!isset($_SESSION['usuarioID'])) {
    $response['message'] = 'No loguejat';
    echo json_encode($response);
    exit();
}

$usuario_id = $_SESSION['usuarioID'];

try {
    $bd = new ConexionBD();
    $conn = $bd->getConexion();

    $sql = "SELECT u.id, u.name AS nombre
            FROM matches m
            JOIN usuario u ON u.id = IF(m.usuario1_id = ?, m.usuario2_id, m.usuario1_id)
            WHERE (m.usuario1_id = ? OR m.usuario2_id = ?) AND m.aceptado = 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $usuario_id, $usuario_id, $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $response['matches'][] = $row;
    }

    $response['success'] = true;
    echo json_encode($response);

} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    echo json_encode($response);
}
