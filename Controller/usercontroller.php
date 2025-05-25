<?php
// Habilitar la visualización de errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuarioID'])) {
    echo json_encode(["status" => "error", "message" => "No estás autenticado"]);
    exit();
}

// Obtener el ID del usuario desde la sesión
$usuarioID = $_SESSION['usuarioID'];

require_once("../Model/DataBase.php");
require_once("../Model/user.php");

try {
    // Conexión a la base de datos y creación del modelo
    $db = new ConexionBD();
    $conn = $db->getConexion();

    // Verificar si la conexión fue exitosa
    if (!$conn) {
        throw new Exception("Error de conexión a la base de datos: " . $db->getError());
    }

    // Crear el objeto Usuario
    $usuarioModel = new Usuario($conn);

    // Llamar al método para obtener los datos del usuario
    $resultado = $usuarioModel->obtenerUsuarioPorId($usuarioID);

    // Verificar si la consulta fue exitosa
    if ($resultado['status'] === 'success') {
        echo json_encode([
            "status" => "success",
            "usuario" => [
                "nombre" => $resultado['usuario']['name'],
                "descripcion" => $resultado['usuario']['descripcion'],
                "imagen" => $resultado['usuario']['imagen'] ? $resultado['usuario']['imagen'] : "../../Imagenes/img2.png" // Imagen por defecto
            ]
        ]);
    } else {
        throw new Exception("Error al obtener los datos del usuario: " . $resultado['message']);
    }

} catch (Exception $e) {
    // Capturar cualquier excepción y devolverla como un error JSON
    echo json_encode([
        "status" => "error",
        "message" => "Excepción capturada: " . $e->getMessage()
    ]);
    // Loguear el error detallado en el servidor
    error_log("Error: " . $e->getMessage());
}
?>
