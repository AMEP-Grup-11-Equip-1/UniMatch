<?php
header('Content-Type: application/json');

// Verificar parámetro action
if (!isset($_GET['action']) && !isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Falta el parámetro action']);
    exit;
}

// Configuración de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión
session_start();

// Verificar autenticación
if (!isset($_SESSION['usuarioID'])) {
    echo json_encode(["success" => false, "message" => "Usuario no autenticado"]);
    exit;
}

// Incluir conexión a BD
require_once("../Model/DataBase.php");

// Obtener datos del usuario
$usuario_id = $_SESSION['usuarioID'];

// Manejar solicitudes GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $db = new ConexionBD();
        $conn = $db->getConexion();

        $action = $_GET['action'] ?? null;

        if ($action === 'get_preguntas') {
            $sql_consulta = "
                SELECT id, mensaje, fecha, cerrado
                FROM ayuda
                WHERE tipo = 'pregunta' AND usuario_id = $usuario_id;";

            $resultado = mysqli_query($conn, $sql_consulta);

            if (!$resultado) {
                echo json_encode([
                    "success" => false,
                    "message" => "Error al ejecutar la consulta: " . mysqli_error($conn)
                ]);
                exit;
            } else {
                $preguntas = [];
                while ($row = mysqli_fetch_assoc($resultado)) {
                    $preguntas[] = [
                        "id" => $row['id'],
                        "mensaje" => $row['mensaje'],
                        "fecha" => $row['fecha'],
                        "cerrado" => $row['cerrado']
                    ];
                }

                echo json_encode([
                    "success" => true,
                    "data" => $preguntas
                ]);
                exit;
            }
        }

        if ($action === 'get_mensajes') {
            $pregunta_id = $_GET['pregunta_id'] ?? null;
            if (!$pregunta_id) {
                throw new Exception("ID de pregunta no proporcionado");
            }

            $sql_consulta = "
        SELECT
            ma.mensaje,
            ma.fecha,
            adm.name AS admin_name
        FROM mensajes_adm ma
        JOIN ayuda a ON ma.protocolo = a.id
        LEFT JOIN ADM adm ON ma.id_adm = adm.id
        WHERE ma.protocolo = ?
        ORDER BY ma.fecha ASC";

            $stmt = mysqli_prepare($conn, $sql_consulta);
            mysqli_stmt_bind_param($stmt, "i", $pregunta_id);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);

            $mensajes = [];
            while ($row = mysqli_fetch_assoc($resultado)) {
                $mensajes[] = [
                    "mensaje" => $row['mensaje'],
                    "fecha" => $row['fecha'],
                    "admin_name" => $row['admin_name']
                ];
            }

            echo json_encode([
                "success" => true,
                "data" => $mensajes
            ]);
            exit;
        } else {
            throw new Exception("Acción GET no válida");
            exit;
        }
        echo json_encode([
            "success" => true,
        ]);
    } catch (Exception $e) {
        echo json_encode([
            "success" => false,
            "message" => $e->getMessage(),
            "file" => $e->getFile(),
            "line" => $e->getLine()
        ]);
    }
}

// Manejar solicitudes POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = new ConexionBD();
        $conn = $db->getConexion();

        $action = $_POST['action'] ?? null;

        if ($action === 'enviar_mensaje') {
            $pregunta_id = $_POST['pregunta_id'] ?? null;
            $mensaje = $_POST['mensaje'] ?? null;

            if (!$pregunta_id || !$mensaje) {
                throw new Exception("Datos incompletos para enviar mensaje");
            }

            $stmt = $conn->prepare("
                INSERT INTO mensajes_adm (protocolo, mensaje, fecha) 
                VALUES (?, ?, NOW())
            ");
            $stmt->bind_param("is", $pregunta_id, $mensaje);
            $stmt->execute();

            echo json_encode([
                "success" => true,
                "message" => "Mensaje enviado correctamente"
            ]);
            exit;
        }

        if ($action === 'cerrar_chat') {
            $pregunta_id = $_POST['pregunta_id'] ?? null;
            if (!$pregunta_id) {
                throw new Exception("ID de pregunta no proporcionado");
            }

            $stmt = $conn->prepare("
                UPDATE ayuda SET cerrado = 1 WHERE id = ?
            ");
            $stmt->bind_param("i", $pregunta_id);
            $stmt->execute();

            echo json_encode([
                "success" => true,
                "message" => "Chat cerrado correctamente"
            ]);
            exit;
        }

        throw new Exception("Acción POST no válida");
    } catch (Exception $e) {
        echo json_encode([
            "success" => false,
            "message" => $e->getMessage(),
            "file" => $e->getFile(),
            "line" => $e->getLine()
        ]);
    }
}

// Si no es GET ni POST
http_response_code(405);
echo json_encode([
    "success" => false,
    "message" => "Método no permitido"
]);
