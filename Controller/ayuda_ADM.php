<?php
// Inicia la sesión para acceder a los datos del admin logueado
session_start();
require_once("../Model/DataBase.php");

// --- Controlador para manejar mensajes de ayuda entre usuarios y administradores ---

// Si la petición es GET y viene con un protocolo, devuelve los mensajes del chat
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['protocolo'])) {
    $protocolo = intval($_GET['protocolo']); // Obtiene el protocolo recibido por GET
    $db = new ConexionBD();
    $conn = $db->getConexion();

    // Consulta para obtener los mensajes del chat, junto con información del usuario y del admin
    $sql = "SELECT
                  ma.mensaje,
                  ma.fecha,
                u.name AS usuario,
                ma.id_adm,
                adm.name AS admin_name
            FROM mensajes_adm ma
                JOIN ayuda a ON ma.protocolo = a.id
                JOIN usuario u ON a.usuario_id = u.id
                    LEFT JOIN ADM adm ON ma.id_adm = adm.id
                WHERE ma.protocolo = ?
            ORDER BY ma.fecha ASC";
    
    $stmt = $conn->prepare($sql); // Prepara la consulta SQL
    $stmt->bind_param("i", $protocolo); // Asocia el parámetro del protocolo
    $stmt->execute(); // Ejecuta la consulta
    $result = $stmt->get_result(); // Obtiene el resultado

    $messages = [];
    // Recorre todos los mensajes y arma el array de respuesta
    while ($row = $result->fetch_assoc()) {
        $isAdmin = !is_null($row['id_adm']); // Verifica si el mensaje es de un admin
        // Verifica si el mensaje es del admin actualmente logueado
        $isCurrentAdmin = $isAdmin && isset($_SESSION['admin']['id']) && intval($row['id_adm']) == intval($_SESSION['admin']['id']);
        // Determina el nombre del remitente
        $senderName = $isAdmin ? 
                     ($isCurrentAdmin ? "TU" : $row['admin_name']) : 
                     $row['usuario'];

        // Formatea la fecha para mostrar solo hora y minutos (HH:mm)
        $fechaFormatada = date('H:i', strtotime($row['fecha']));

        // Agrega el mensaje al array de respuesta
        $messages[] = [
            "text" => htmlspecialchars($row['mensaje']),
            "userImage" => $isAdmin ? "" : "https://ui-avatars.com/api/?name=" . urlencode($row['usuario']),
            "usuario" => $senderName,
            "fecha" => $fechaFormatada, // Solo muestra HH:mm
            "isAdmin" => $isAdmin,
            "isCurrentUser" => $isCurrentAdmin,  // Bandera para el frontend
            "original_sender" => $isAdmin ? "admin_".$row['id_adm'] : "user_".$row['usuario_id']
        ];
    }
    $stmt->close();
    // Devuelve los mensajes en formato JSON
    echo json_encode(["messages" => $messages]);
    exit;
}

// Si la petición es POST, intenta guardar un nuevo mensaje del admin
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica que lleguen los datos necesarios y que el admin esté logueado
    if (isset($_POST['protocolo'], $_POST['mensaje']) && isset($_SESSION['admin']['id'])) {
        $protocolo = intval($_POST['protocolo']); // Obtiene el protocolo
        $mensaje = trim($_POST['mensaje']); // Obtiene el mensaje
        $id_adm = intval($_SESSION['admin']['id']); // Obtiene el ID del admin

        $db = new ConexionBD();
        $conn = $db->getConexion();

        // Inserta el mensaje en la base de datos con la fecha actual
        $sql = "INSERT INTO mensajes_adm (protocolo, id_adm, mensaje, fecha) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("iis", $protocolo, $id_adm, $mensaje);
            $stmt->execute();
            $stmt->close();
            http_response_code(200); // Éxito
        } else {
            http_response_code(500); // Error en la base de datos
        }
        exit;
    } else {
        http_response_code(400); // Datos incompletos o sesión no válida
        exit;
    }
}

// Si la petición no es GET ni POST, devuelve método no permitido
http_response_code(405);
?>