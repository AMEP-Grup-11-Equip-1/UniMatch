<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once '../Model/DataBase.php';

// Verificar se o protocolo foi passado via GET
if (!isset($_GET['protocolo'])) {
    echo json_encode(['error' => 'Protocolo não especificado']);
    exit;
}

$protocolo = $_GET['protocolo'];
$conexion = new ConexionBD();
$conn = $conexion->getConexion();

// Consulta para obter todas as mensagens do protocolo
$sql = "SELECT 
            ma.mensaje,
            ma.fecha,
            u.name AS nombre_usuario,
            CASE 
                WHEN ma.remetente = 'admin' THEN 'admin'
                ELSE 'usuario'
            END AS tipo_remetente
        FROM 
            mensajes_adm ma
        JOIN 
            ayuda a ON ma.protocolo = a.id
        JOIN 
            usuario u ON a.usuario_id = u.id
        WHERE 
            ma.protocolo = ?
        ORDER BY 
            ma.fecha ASC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $protocolo);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if (!$resultado) {
    echo json_encode(['error' => 'Erro na consulta: ' . mysqli_error($conn)]);
    exit;
}

$mensagens = [];
while ($linha = mysqli_fetch_assoc($resultado)) {
    $mensagens[] = [
        'mensaje' => htmlspecialchars($linha['mensaje']),
        'fecha' => htmlspecialchars($linha['fecha']),
        'nombre_usuario' => htmlspecialchars($linha['nombre_usuario']),
        'tipo_remetente' => $linha['tipo_remetente'] // 'admin' ou 'usuario'
    ];
}

echo json_encode($mensagens);
?>