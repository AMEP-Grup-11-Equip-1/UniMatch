<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include_once '../Model/DataBase.php';

$conexion = new ConexionBD();
$conn = $conexion->getConexion();


$sql = "SELECT v.id, u.name, v.mensaje
        FROM ayuda v
        JOIN usuario u ON v.usuario_id = u.id
        WHERE v.mensaje IS NOT NULL;";

$resultado = mysqli_query($conn, $sql);

if (!$resultado) {
    echo "<tr><td colspan='3'>Error en la consulta</td></tr>";
    exit;
}

while ($linha = mysqli_fetch_assoc($resultado)) {
echo "<tr>";
    echo "<td><a href='user.html?id=" . urlencode($linha['id']) . "' style='display:block; width:100%; height:100%; text-decoration:none; color:inherit;'>" . htmlspecialchars($linha['name'] ?? '') . "</a></td>";
    echo "</tr>";
}
?>