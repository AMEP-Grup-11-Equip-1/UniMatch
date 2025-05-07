<?php
session_start();
require_once("../Model/DataBase.php");
require_once("../Model/Grup.php");

// Configuración para enviar cabeceras JSON
header('Content-Type: application/json');

// Mostrar errores (solo en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conectar a la base de datos
$db = new ConexionBD();
$conn = $db->getConexion();

// Verificar si la conexión fue exitosa
if ($conn->connect_error) {
    echo json_encode(["error" => "Conexión fallida: " . $conn->connect_error]);
    exit();
}

// Crear el modelo de grupo
$grupModel = new Grup($conn);
$grupos = $grupModel->obtenirTotsElsGrups();  // Obtienes todos los grupos

// Verificar si hay grupos
if ($grupos === null) {
    echo json_encode(["error" => "No se encontraron grupos."]);
    exit();
}

// Devolver los datos de los grupos en formato JSON
echo json_encode($grupos);
?>
