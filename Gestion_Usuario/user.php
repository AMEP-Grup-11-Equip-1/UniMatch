<?php
session_start();
include 'DataBase.php';

class AutenticacionUsuario {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function eliminarUsuario() {
        if (!isset($_SESSION['usuarioID'])) { // Usamos 'usuarioID' em lugar de 'id'
            return json_encode(["status" => "error", "message" => "No hay usuario autenticado"]);
        }

        $id = $_SESSION['usuarioID'];

        $query = "DELETE FROM usuario WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            return json_encode(["status" => "error", "message" => "Error en la preparación de la consulta"]);
        }

        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            session_unset();
            session_destroy();
            return json_encode(["status" => "success", "message" => "Cuenta eliminada correctamente"]);
        } else {
            return json_encode(["status" => "error", "message" => "Error al ejecutar la consulta"]);
        }
    }
}

// Crea la conexión correctamente
$bd = new ConexionBD();
$conexion = $bd->getConexion();

// Ejecutar la eliminación
$autenticacion = new AutenticacionUsuario($conexion);
echo $autenticacion->eliminarUsuario();
?>


