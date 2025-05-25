<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inclou la connexió
require_once '../Model/connexio.php';



// Consulta
$sql = "SELECT id, nom AS name FROM report_types";
$result = mysqli_query($connexio, $sql);

// Control d'error en la consulta
if (!$result) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error en la consulta SQL",
        "error" => mysqli_error($connexio)
    ]);
    exit;
}

// Recollida de resultats
$tipus = [];
while ($row = mysqli_fetch_assoc($result)) {
    $tipus[] = $row;
}

//Retorn net JSON
echo json_encode($tipus);
exit;
*/