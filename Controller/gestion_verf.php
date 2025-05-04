<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once '../Model/DataBase.php';

$conexion = new ConexionBD();
$conn = $conexion->getConexion();


$sql = "SELECT u.name, v.pending, v.report_id
        FROM verifications v
        JOIN usuario u ON v.user = u.id
        WHERE v.pending = TRUE";

$resultado = mysqli_query($conn, $sql);

if (!$resultado) {
    echo "<tr><td colspan='3'>Error en la consulta</td></tr>";
    exit;
}

while ($linha = mysqli_fetch_assoc($resultado)) {
    echo "<tr>";
    // AQUI HAY QUE AÑADIR UN ENELACE AL PERFIL DEL USUARIO EN MODO ADMIN
    echo "<td>" . htmlspecialchars($linha['name'] ?? '') . "</td>";
    echo "<td>" . htmlspecialchars($linha['pending'] ?? '') . "</td>";
    echo "<td>" . htmlspecialchars($linha['report_id'] ?? 'N/D') . "</td>";
    echo "</tr>";
}
?>