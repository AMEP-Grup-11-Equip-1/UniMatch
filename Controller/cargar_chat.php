<?php
// Mostrar todos los errores de PHP (útil para depuración)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir la clase de conexión a la base de datos
include_once '../Model/DataBase.php';

// Verificar si el protocolo fue pasado por GET
if (!isset($_GET['protocolo'])) {
    echo json_encode(['error' => 'Protocolo no especificado']);
    exit;
}

$protocolo = $_GET['protocolo'];
$conexion = new ConexionBD();
$conn = $conexion->getConexion();

// Consulta para obtener todos los mensajes asociados al protocolo
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

// Preparar la consulta SQL
$stmt = mysqli_prepare($conn, $sql);
// Asociar el parámetro del protocolo
mysqli_stmt_bind_param($stmt, "i", $protocolo);
// Ejecutar la consulta
mysqli_stmt_execute($stmt);
// Obtener el resultado
$resultado = mysqli_stmt_get_result($stmt);

// Verificar si hubo error en la consulta
if (!$resultado) {
    echo json_encode(['error' => 'Error en la consulta: ' . mysqli_error($conn)]);
    exit;
}

$mensagens = [];
// Recorrer los resultados y almacenarlos en un array
while ($linha = mysqli_fetch_assoc($resultado)) {
    $mensagens[] = [
        'mensaje' => htmlspecialchars($linha['mensaje']),
        'fecha' => htmlspecialchars($linha['fecha']),
        'nombre_usuario' => htmlspecialchars($linha['nombre_usuario']),
        'tipo_remetente' => $linha['tipo_remetente'] // 'admin' o 'usuario'
    ];
}

// Devolver los mensajes en formato JSON
echo json_encode($mensagens);
?>