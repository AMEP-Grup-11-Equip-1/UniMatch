<?php
session_start();



require_once("../Model/DataBase.php");
require_once("../Model/Grup.php");

header('Content-Type: application/json');

if (!isset($_SESSION['usuarioID'])) {
    echo json_encode(["success" => false, "message" => "No hay sesión iniciada"]);
    exit;
}

$usuarioID = $_SESSION['usuarioID'];

// Verificamos que los datos necesarios se hayan enviado
if (!isset($_POST['grupo_id']) || !isset($_POST['mensaje'])) {
    echo json_encode(["success" => false, "message" => "Faltan datos requeridos"]);
    exit;
}

$grupo_id = intval($_POST['grupo_id']);
$mensaje = trim($_POST['mensaje']);

if ($mensaje === "") {
    echo json_encode(["success" => false, "message" => "El mensaje está vacío"]);
    exit;
}

// Conexión a base de datos
$db = new ConexionBD();
$conn = $db->getConexion();

if (!$conn) {
    echo json_encode(["success" => false, "message" => "Error en la conexión a la base de datos", "error" => mysqli_connect_error()]);
    exit;
}

// Instanciamos la clase Grup
$grup = new Grup($conn);

// Insertamos el mensaje en el grupo
$inserted = $grup->insertarMensajeGrupo($grupo_id, $usuarioID, $mensaje);

if ($inserted) {
    echo json_encode(["success" => true, "message" => "Mensaje enviado correctamente"]);
} else {
    // Para obtener errores desde la clase Grup, necesitaríamos un método para obtener el último error
    // Si no lo tienes, captura el error directo aquí para debug
    $error = $conn->error;
    echo json_encode(["success" => false, "message" => "Error al enviar el mensaje", "error" => $error]);
}
?>
