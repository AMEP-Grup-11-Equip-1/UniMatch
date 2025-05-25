<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

/*
<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once '../Model/DataBase.php';

try {
    $bd = new ConexionBD();
    $conn = $bd->getConexion();

    $sql = "SELECT id, nom AS name FROM report_types ORDER BY id ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();

    $motivos = [];

    while ($row = $result->fetch_assoc()) {
        $motivos[] = $row;
    }

    echo json_encode($motivos, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al obtenir els tipus de denúncia: " . $e->getMessage()]);
}

*/

echo json_encode([
    ["id" => 1, "name" => "Comportamiento inapropiado"],
    ["id" => 2, "name" => "Spam o publicidad"],
    ["id" => 3, "name" => "Perfil falso"],
    ["id" => 4, "name" => "Contenido ofensivo"],
    ["id" => 5, "name" => "Acoso o bullying"],
    ["id" => 6, "name" => "Desnudos o contenido sexual"],
    ["id" => 7, "name" => "Violencia o amenazas"],
    ["id" => 8, "name" => "Discriminacion o odio"],
    ["id" => 9, "name" => "Otro motivo"]
]);
