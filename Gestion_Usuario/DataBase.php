<?php
session_start();

class ConexionBD {
    private $servidor = "ubiwan.epsevg.upc.edu";
    private $usuario = "amep04";
    private $clave = "od5Ieg6Keit0ai";
    private $bd = "amep04";
    private $conn;

    public function __construct() {
        $this->conectarBD();
        if (!isset($_SESSION['error'])) {
            $_SESSION['error'] = "";
        }
    }

    private function conectarBD() {
        $this->conn = new mysqli($this->servidor, $this->usuario, $this->clave, $this->bd);

        if ($this->conn->connect_error) {
            die("Error de conexión: " . $this->conn->connect_error);
        }
    }

    public function __destruct() {
        if ($this->conn && !$this->conn->connect_error) {
            $this->conn->close();
        }
    }

    public function getConexion() {
        return $this->conn;
    }

    // Nova função para retornar universidades em JSON
    public function getUniversitiesJSON() {
        $sql = "SELECT * FROM universities";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            echo json_encode(["error" => "Error al preparar la consulta SQL."]);
            exit();
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $universities = [];

        while ($row = $result->fetch_assoc()) {
            $universities[] = [
                "id" => $row['id'],
                "Uni_name" => $row['Uni_name'],
                "Uni_mail" => $row['Uni_mail'],
                "Uni_acronym" => $row['Uni_acronym']
            ];
        }

        $stmt->close();
        return $universities;
        exit();
    }
}
?>