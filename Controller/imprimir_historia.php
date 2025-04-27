<?php
header('Content-Type: application/json');
require_once("../Model/DataBase.php");
require_once("../Model/Historia.php");

// Crear una instancia de la conexión a la base de datos
$db = new ConexionBD();
$conn = $db->getConexion();
$historiaModel = new Historia($conn);

// Comprovar si hi ha ID per a un perfil concret
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);
    $perfil = $historiaModel->obtenerPorId($id);
    echo json_encode($perfil);
} else {
    // Si no hi ha ID, tornem diversos perfils
    $perfiles = $historiaModel->obtenerMultiples(5);
    echo json_encode($perfiles);
}
?>
