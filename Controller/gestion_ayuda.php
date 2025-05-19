<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once '../Model/DataBase.php';

$conexion = new ConexionBD();
$conn = $conexion->getConexion();

// Consulta para mensajes nuevos (protocolos con solo un mensaje)
$sql_nuevos = "SELECT 
                ma.protocolo AS numero_protocolo,
                u.name AS nombre_usuario,
                ma.mensaje,
                ma.fecha
            FROM 
                mensajes_adm ma
            JOIN 
                ayuda a ON ma.protocolo = a.id
            JOIN 
                usuario u ON a.usuario_id = u.id
            WHERE 
                ma.protocolo IN (
                    SELECT protocolo 
                    FROM mensajes_adm 
                    GROUP BY protocolo 
                    HAVING COUNT(*) = 1
                )
            ORDER BY 
                ma.protocolo;";

$resultado_nuevos = mysqli_query($conn, $sql_nuevos);

// Consulta para protocolos abiertos (protocolos con más de un mensaje)
/*
    Esta consulta selecciona los protocolos que tienen más de un mensaje en la tabla mensajes_adm.

    1. Se hace un JOIN entre mensajes_adm (ma) y ayuda (a) usando ma.protocolo = a.id
       Esto es posible porque la columna "protocolo" en mensajes_adm es una clave foránea que referencia 
       la columna "id" de la tabla ayuda. Así, cada mensaje administrativo está asociado a una solicitud de ayuda.

    2. Luego, se hace otro JOIN con la tabla usuario (u) usando a.usuario_id = u.id
       Esto permite obtener el nombre del usuario que creó la solicitud.

    3. El filtro WHERE asegura que solo se incluyan los protocolos que tienen más de un mensaje
       (usando una subconsulta con GROUP BY y HAVING COUNT(*) > 1).

    4. SELECT DISTINCT evita resultados duplicados, mostrando un protocolo y su respectivo usuario una sola vez.

    5. Finalmente, los resultados se ordenan por número de protocolo.

    Resultado: una lista de protocolos que tienen múltiples mensajes, junto con el nombre del usuario que los creó.
*/
$sql_abiertos = "SELECT 
                    DISTINCT ma.protocolo AS numero_protocolo,
                    u.name AS nombre_usuario
                FROM 
                    mensajes_adm ma
                JOIN 
                    ayuda a ON ma.protocolo = a.id
                JOIN 
                    usuario u ON a.usuario_id = u.id
                WHERE 
                    ma.protocolo IN (
                        SELECT protocolo 
                        FROM mensajes_adm 
                        GROUP BY protocolo 
                        HAVING COUNT(*) > 1
                    )
                ORDER BY 
                    ma.protocolo;";

$resultado_abiertos = mysqli_query($conn, $sql_abiertos);

// Iniciar el output buffer para capturar ambos resultados
ob_start();

// Mostrar los mensajes nuevos en la tabla
if (!$resultado_nuevos) {
    echo "<tr><td colspan='4'>Error en la consulta de nuevos mensajes</td></tr>";
} else {
    while ($linha = mysqli_fetch_assoc($resultado_nuevos)) {
        echo "<tr onclick='loadChat(" 
            . htmlspecialchars($linha['numero_protocolo']) . ", \"" 
            . htmlspecialchars(addslashes($linha['nombre_usuario'])) . "\")' style='cursor:pointer'>";
        echo "<td>" . htmlspecialchars($linha['numero_protocolo']) . "</td>";
        echo "<td>" . htmlspecialchars($linha['nombre_usuario']) . "</td>";
        echo "<td>" . htmlspecialchars($linha['mensaje']) . "</td>";
        echo "<td>" . htmlspecialchars($linha['fecha']) . "</td>";
        echo "</tr>";
    }
}

// Guardar el output de la tabla
$output_tabla = ob_get_clean();

// Iniciar nuevo output buffer para las consultas abiertas
ob_start();

// Mostrar las consultas abiertas en la lista
if (!$resultado_abiertos) {
    echo "<div class='error'>Error al cargar consultas abiertas</div>";
} else {
    while ($linha = mysqli_fetch_assoc($resultado_abiertos)) {
        echo "<div class='chat-item' onclick='loadChat(" 
            . htmlspecialchars($linha['numero_protocolo']) . ", \"" 
            . htmlspecialchars(addslashes($linha['nombre_usuario'])) . "\")'>";
        echo "<span class='protocolo'>" . htmlspecialchars($linha['numero_protocolo']) . "</span>";
        echo "<span class='usuario'>" . htmlspecialchars($linha['nombre_usuario']) . "</span>";
        echo "</div>";
    }
}

// Guardar el output de las consultas abiertas
$output_abiertos = ob_get_clean();

// Devolver ambos outputs en un formato JSON
echo json_encode([
    'tabla_new' => $output_tabla,
    'consultas_abiertas' => $output_abiertos
]);
?>