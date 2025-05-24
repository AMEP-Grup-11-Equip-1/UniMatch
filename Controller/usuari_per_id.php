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

    $id_verf = intval($_GET['id']);
    
    // 1. Primero verificamos si existe la verificación
    $sql = "SELECT user, ok FROM verifications WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["status" => "error", "message" => "Error al preparar la consulta de verificación"]);
        exit;
    }
    
    $stmt->bind_param("i", $id_verf);
    if (!$stmt->execute()) {
        echo json_encode(["status" => "error", "message" => "Error al ejecutar la consulta de verificación"]);
        exit;
    }
    
    $stmt->bind_result($id, $okResult);
    if (!$stmt->fetch()) {
        $stmt->close();
        echo json_encode(["status" => "error", "message" => "ID de verificación no encontrado"]);
        exit;
    }
    $stmt->close();

    // 2. Ahora obtenemos el usuario
    $usuarioModel = new Usuario($conn);
    $resultado = $usuarioModel->obtenerUsuarioPorID($id);
    
    if ($resultado['status'] !== 'success' || empty($resultado['usuario'])) {
        echo json_encode(["status" => "error", "message" => "Usuario no encontrado"]);
        exit;
    }

    // 3. Consulta combinada (historias y matches)
    $sql_union = "SELECT 
                    '1' AS origem,
                    h.descripcion,
                    h.imagen,
                    NULL AS id_match,
                    NULL AS nombre_match,
                    NULL AS data_match
                  FROM historias h
                  WHERE h.usuario_id = ?
                  
                  UNION ALL
                  
                  SELECT 
                    '2' AS origem,
                    NULL AS descripcion,
                    NULL AS imagen,
                    m.id AS id_match,
                    CASE 
                        WHEN m.usuario1_id = ? THEN u2.name
                        WHEN m.usuario2_id = ? THEN u1.name
                    END AS nombre_match,
                    m.fecha AS data_match
                  FROM matches m
                  LEFT JOIN usuario u1 ON m.usuario1_id = u1.id
                  LEFT JOIN usuario u2 ON m.usuario2_id = u2.id
                  WHERE m.usuario1_id = ? OR m.usuario2_id = ?";
    
    $stmt_union = $conn->prepare($sql_union);
    if (!$stmt_union) {
        echo json_encode(["status" => "error", "message" => "Error al preparar la consulta combinada"]);
        exit;
    }
    
    $stmt_union->bind_param("iiiii", $id, $id, $id, $id, $id);
    if (!$stmt_union->execute()) {
        echo json_encode(["status" => "error", "message" => "Error al ejecutar la consulta combinada"]);
        exit;
    }
    
    $result_union = $stmt_union->get_result();
    $dados_union = [];
    while ($row = $result_union->fetch_assoc()) {
        $dados_union[] = $row;
    }
    $stmt_union->close();

    // Respuesta exitosa
    echo json_encode([
        "status" => "success",
        "data" => [
            "usuario" => [
                "nombre" => $resultado['usuario']['name'],
                "email" => $resultado['usuario']['mail']
            ],
            "verificacion" => [
                "ok" => isset($okResult) ? (is_null($okResult) ? null : intval($okResult)) : null
            ],
            "contenido" => $dados_union
        ]
    ]);
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
