<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once '../Model/DataBase.php';

$conexion = new ConexionBD();
$conn = $conexion->getConexion();


$sql = "SELECT v.id as verification_id, u.id, u.name, v.report_num
            FROM verifications v
            JOIN usuario u ON v.user = u.id
            WHERE v.report_num > 0";

$resultado = mysqli_query($conn, $sql);

if (!$resultado) {
    echo "<tr><td colspan='3'>Error en la consulta</td></tr>";
    exit;
}

while ($linha = mysqli_fetch_assoc($resultado)) {
    $url = 'user.html?id=' . urlencode($linha['verification_id']);
    echo "<tr onclick=\"window.location.href='{$url}'\" style='cursor:pointer;'>";
    echo "<td>" . htmlspecialchars($linha['name'] ?? '') . "</td>";
    echo "<td>" . htmlspecialchars($linha['report_num'] ?? '') . "</td>";
    echo "</tr>";
}
?>