<?php
session_start();
require_once("../Model/DataBase.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['action']) || !isset($_GET['pregunta_id'])) {
        echo json_encode(['success' => false, 'error' => 'Missing parameters']);
        exit();
    }

    if ($_GET['action'] !== 'get_valoracion') {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        exit();
    }

    // Rest of your existing GET handling code...


    $db = new ConexionBD();
    $conn = $db->getConexion();

    $pregunta_id = intval($_GET['pregunta_id']);
    try {
        $sql = "SELECT puntuacion FROM ayuda WHERE id = ?";
        // Preparar la consulta SQL
        $stmt = mysqli_prepare($conn, $sql);
        // Asociar el parámetro del protocolo
        mysqli_stmt_bind_param($stmt, "i", $pregunta_id);
        // Ejecutar la consulta
        mysqli_stmt_execute($stmt);
        // Obtener el resultado
        $resultado = mysqli_stmt_get_result($stmt);

        // Verificar si hubo error en la consulta
        if (!$resultado) {
            echo json_encode(['error' => 'Error en la consulta: ' . mysqli_error($conn)]);
            exit;
        }

        if ($fila = mysqli_fetch_assoc($resultado)) {
            echo json_encode(['success' => true, 'valoracion' => intval($fila['puntuacion'])]);
        } else {
            echo json_encode(['success' => false, 'valoracion' => null]);
        }

        exit();
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit();
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'enviar_valoracion') {
    // Verificar que los parámetros necesarios estén presentes
    if (!isset($_POST['pregunta_id']) || !isset($_POST['valoracion'])) {
        echo json_encode(['success' => false, 'error' => 'Missing parameters']);
        exit();
    }

    $pregunta_id = intval($_POST['pregunta_id']);
    $valoracion = intval($_POST['valoracion']);

    // Validar que la valoración esté entre 1 y 5
    if ($valoracion < 1 || $valoracion > 5) {
        echo json_encode(['success' => false, 'error' => 'Invalid rating value']);
        exit();
    }

    $db = new ConexionBD();
    $conn = $db->getConexion();

    try {
        $sql = "UPDATE ayuda SET puntuacion = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->execute([$valoracion, $pregunta_id]);
            $stmt->close();
            echo json_encode(['success' => true]);
            exit();
        } else {
            throw new Exception("Error al preparar la consulta");
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit();
    }
}

// Si la solicitud no es válida
http_response_code(400);
echo json_encode(['success' => false, 'error' => 'Invalid request']);
exit();
