<?php
session_start();
require_once("../Model/DataBase.php");
require_once("../Model/Grup.php");

header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$db = new ConexionBD();
$conn = $db->getConexion();

if ($conn->connect_error) {
    echo json_encode(["error" => "Conexión fallida: " . $conn->connect_error]);
    exit();
}

$grupModel = new Grup($conn);
$grupos = $grupModel->obtenirTotsElsGrups();

if ($grupos === null) {
    echo json_encode(["error" => "No se encontraron grupos."]);
    exit();
}

//  Consultar la imagen de cada grupo por separado
foreach ($grupos as &$grupo) {
    $stmt = $conn->prepare("SELECT imagen FROM grups WHERE id = ?");
    $stmt->bind_param("i", $grupo["id"]);
    $stmt->execute();
    $stmt->bind_result($imagen);
    $stmt->fetch();
    $stmt->close();

    $grupo["imagen"] = $imagen ?? null;
}

echo json_encode($grupos);
