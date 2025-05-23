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
    Esta consulta selecciona los protocolos que tienen más de un mensaje en la tabla mensajes_adm
    y los ordena según la fecha más reciente de sus mensajes.

    1. Se crea una subconsulta (llamada "ultima") que agrupa los registros de la tabla mensajes_adm por protocolo,
       seleccionando solo aquellos que tienen más de un mensaje (HAVING COUNT(*) > 1).
       Además, se obtiene la fecha más reciente (MAX(fecha)) para cada protocolo.

    2. Luego, se hace un JOIN entre la subconsulta "ultima" y la tabla mensajes_adm (ma),
       para mantener el enlace con las otras tablas necesarias.

    3. Se hace un JOIN con la tabla ayuda (a) usando ma.protocolo = a.id,
       ya que "protocolo" en mensajes_adm es una clave foránea que referencia "id" en ayuda.
       Esto permite conectar cada mensaje administrativo con la solicitud de ayuda correspondiente.

    4. Después, se hace otro JOIN con la tabla usuario (u) usando a.usuario_id = u.id,
       lo que permite obtener el nombre del usuario que creó la solicitud.

    5. Se usa GROUP BY para agrupar por protocolo y nombre de usuario (y también por fecha_mais_recente),
       asegurando que cada fila del resultado sea única por protocolo.

    6. Finalmente, se ordena el resultado por la fecha más reciente de mensaje para cada protocolo,
       de forma descendente (es decir, del más nuevo al más antiguo).

    Resultado: una lista de protocolos que tienen múltiples mensajes, junto con el nombre del usuario que los creó,
    ordenada por la fecha del mensaje más reciente de cada protocolo.
*/
$sql_abiertos = "SELECT 
                    ma.protocolo AS numero_protocolo,
                    u.name AS nombre_usuario,
                    ultima.fecha_mais_recente,
                    cerrado
                FROM 
                    (
                        SELECT 
                            protocolo,
                            MAX(fecha) AS fecha_mais_recente
                        FROM 
                            mensajes_adm
                        GROUP BY 
                            protocolo
                        HAVING 
                            COUNT(*) > 1
                    ) AS ultima
                JOIN 
                    mensajes_adm ma ON ma.protocolo = ultima.protocolo
                JOIN 
                    ayuda a ON ma.protocolo = a.id
                JOIN 
                    usuario u ON a.usuario_id = u.id
                GROUP BY 
                    ma.protocolo, u.name, ultima.fecha_mais_recente
                ORDER BY 
                    ultima.fecha_mais_recente DESC;";

$resultado_abiertos = mysqli_query($conn, $sql_abiertos);

// Iniciar el output buffer para capturar ambos resultados
ob_start();

// Mostrar los mensajes nuevos en la tabla
if (!$resultado_nuevos) {
    echo "<tr><td colspan='4'>Error en la consulta de nuevos mensajes</td></tr>";
} else {
    while ($linha = mysqli_fetch_assoc($resultado_nuevos)) {
                $data_formatada = date('H:i d/m', strtotime($linha['fecha']));

         echo "<div class='chat-item' data-cerrado='" . "' onclick='loadChat("
            . htmlspecialchars($linha['numero_protocolo']) . ", \""
            . htmlspecialchars(addslashes($linha['nombre_usuario'])) . "\")'>";
        echo "<span class='protocolo'>" . htmlspecialchars($linha['numero_protocolo']) . "</span>";
        echo "<span class='usuario'>" . htmlspecialchars($linha['nombre_usuario']) . "</span>";
        echo "<span class='preview'>" . htmlspecialchars($linha['mensaje']) . "</span>";
        echo "<span class='fecha'>" . htmlspecialchars($data_formatada) . "</span>";
        echo "</div>";
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
        $data_formatada = date('H:i d/m', strtotime($linha['fecha_mais_recente']));
        echo "<div class='chat-item' data-cerrado='" . intval($linha['cerrado']) . "' onclick='loadChat("
            . htmlspecialchars($linha['numero_protocolo']) . ", \""
            . htmlspecialchars(addslashes($linha['nombre_usuario'])) . "\")'>";
        echo "<span class='protocolo'>" . htmlspecialchars($linha['numero_protocolo']) . "</span>";
        echo "<span class='usuario'>" . htmlspecialchars($linha['nombre_usuario']) . "</span>";
        echo "<span class='fecha'>" . htmlspecialchars($data_formatada) . "</span>";
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
