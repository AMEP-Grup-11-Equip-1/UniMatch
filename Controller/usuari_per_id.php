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

// Si la solicitud es GET, obtiene los datos del usuario por ID
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Verifica que el parámetro 'id' esté presente en la URL
    if (!isset($_GET['id'])) {
        echo json_encode(["status" => "error", "message" => "Falta el parámetro id"]);
        exit;
    }

    $id = intval($_GET['id']);
    $usuarioModel = new Usuario($conn);
    $resultado = $usuarioModel->obtenerUsuarioPorID($id);

    // Si la consulta fue exitosa, obtiene también el estado de verificación
    if ($resultado['status'] === 'success') {
        $sqlOk = "SELECT ok FROM verifications WHERE user = ?";
        $stmtOk = $conn->prepare($sqlOk);
        $okValue = null;

        if ($stmtOk) {
            $stmtOk->bind_param("i", $id);
            $stmtOk->execute();
            $stmtOk->bind_result($okResult);
            $stmtOk->fetch();
            $stmtOk->close();
        }

        // Devuelve los datos del usuario y el estado de verificación
        echo json_encode([
            "nombre" => $resultado['usuario']['name'],
            "email" => $resultado['usuario']['mail'],
            "ok" => isset($okResult) ? (is_null($okResult) ? null : intval($okResult)) : null
        ]);
    } else {
        // Si hubo un error al obtener el usuario
        echo json_encode(["status" => "error", "message" => $resultado['message']]);
    }
    exit;
}

// Si la solicitud es POST, actualiza el estado de verificación del usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica que los parámetros 'id' y 'estado' estén presentes
    if (!isset($_POST['estado']) || !isset($_POST['id'])) {
        echo json_encode(["status" => "error", "message" => "Faltan parámetros id o estado"]);
        exit;
    }

    $id = intval($_POST['id']);
    $estado = intval($_POST['estado']);

    // Prepara y ejecuta la consulta para actualizar el estado
    $sql = "UPDATE verifications SET ok = ? WHERE user = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ii", $estado, $id);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Estado actualizado"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al actualizar el estado"]);
        }
        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Error al preparar la consulta"]);
    }
    exit;
}

// Si el método no es GET ni POST, devuelve un error
echo json_encode(["status" => "error", "message" => "Método no soportado"]);
