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

    $id_verf = intval($_GET['id']);
    // Consulta para obtener el ID de usuario asociado a la verificación
    $sql = "SELECT user FROM verifications WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_verf);
    $stmt->execute();
    $stmt->bind_result($id);
    $stmt->fetch();
    $stmt->close();

    // Crea una instancia del modelo de usuario y obtiene los datos por ID
    $usuarioModel = new Usuario($conn);
    $resultado = $usuarioModel->obtenerUsuarioPorID($id);

    // Si la consulta fue exitosa, obtiene también el estado de verificación
    if ($resultado['status'] === 'success') {
        // Consulta para obtener el estado de verificación (ok)
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
    // Verifica que los parámetros necesarios estén presentes
    if (!isset($_POST['estado']) || !isset($_POST['id'])) {
        echo json_encode(["status" => "error", "message" => "Faltam parâmetros id ou estado"]);
        exit;
    }

    $id_verf = intval($_POST['id']);
    $estado = intval($_POST['estado']);

    // Primero obtiene el user asociado al id de verificación
    $sql_get_user = "SELECT user FROM verifications WHERE id = ?";
    $stmt_get = $conn->prepare($sql_get_user);
    $stmt_get->bind_param("i", $id_verf);
    $stmt_get->execute();
    $stmt_get->bind_result($user_id);
    $stmt_get->fetch();
    $stmt_get->close();

    // Si no se encuentra el usuario, devuelve error
    if (!$user_id) {
        echo json_encode(["status" => "error", "message" => "ID de verificação não encontrado"]);
        exit;
    }

    // Ahora actualiza usando el user_id correcto
    $sql = "UPDATE verifications SET ok = ? WHERE user = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ii", $estado, $user_id);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Estado atualizado"]);
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
