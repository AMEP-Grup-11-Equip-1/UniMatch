<?php
session_start();

class AutenticacionUsuario {
    private $servidor = "ubiwan.epsevg.upc.edu";
    private $usuario = "amep04";
    private $clave = "od5Ieg6Keit0ai";
    private $bd = "amep04";
    private $conn;

    public function __construct() {
        $this->conectarBD();
    }

    private function conectarBD() {
        $this->conn = new mysqli($this->servidor, $this->usuario, $this->clave, $this->bd);
        if ($this->conn->connect_error) {
            die("Error en la conexión a la base de datos: " . $this->conn->connect_error);
        }
    }

    public function eliminarUsuario() {
        if (!isset($_SESSION['usuarioID'])) { // Usamos 'usuarioID' en lugar de 'id'
            return json_encode(["status" => "error", "message" => "No hay usuario autenticado"]);
        }

        $id = $_SESSION['usuarioID'];

        $query = "DELETE FROM usuarios WHERE id = ?";
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

// Ejecutar la eliminación
$autenticacion = new AutenticacionUsuario();
echo $autenticacion->eliminarUsuario();
?>


