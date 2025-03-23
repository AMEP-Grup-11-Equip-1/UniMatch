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
        // Verifica se a conexão ainda está ativa antes de fechá-la
        if ($this->conn && !$this->conn->connect_error) {
            $this->conn->close();
        }
    }

    // Método para obter a conexão
    public function getConexion() {
        return $this->conn;
    }
}

?>

