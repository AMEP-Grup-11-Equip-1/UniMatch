<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

include_once '../Model/DataBase.php';
include_once '../Model/Grup.php';

$bd = new ConexionBD();
$conn = $bd->getConexion();

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Error al conectar a la base de datos']);
    exit;
}

$grupModel = new Grup($conn);

$rawData = file_get_contents('php://input');
if (!$rawData) {
    echo json_encode(['success' => false, 'message' => 'No se recibieron datos']);
    exit;
}

$data = json_decode($rawData, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'JSON inválido: ' . json_last_error_msg()]);
    exit;
}

if (empty($data['grup_id']) || empty($data['usuari_id'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan parámetros grup_id o usuari_id']);
    exit;
}

$grup_id = intval($data['grup_id']);
$usuari_id = intval($data['usuari_id']);

$resultado = $grupModel->afegirUsuariAlGrup($grup_id, $usuari_id);

if (is_array($resultado) && isset($resultado['success']) && !$resultado['success']) {
    echo json_encode(['success' => false, 'message' => $resultado['error']]);
} elseif ($resultado === true || (is_array($resultado) && $resultado['success'] === true)) {
    echo json_encode(['success' => true, 'message' => 'Te has unido correctamente al grupo.']);
} else {
    echo json_encode(['success' => false, 'message' => 'No se pudo unir al grupo.']);
}
