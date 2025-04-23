<?php
// Configuración para mostrar todos los errores (útil en desarrollo)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inicia sesión solo si no está activa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Clase para manejar la conexión a la base de datos
class ConexionBD {
    // Credenciales de conexión (deberían estar en variables de entorno)
    private $servidor = "ubiwan.epsevg.upc.edu";
    private $usuario = "amep04";
    private $clave = "od5Ieg6Keit0ai";
    private $bd = "amep04";
    private $conn; // Objeto de conexión MySQLi

    // Constructor - establece conexión e inicializa variable de error
    public function __construct() {
        $this->conectarBD();
        if (!isset($_SESSION['error'])) {
            $_SESSION['error'] = ""; // Inicializa variable de error en sesión
        }
    }

    // Método privado para establecer conexión
    private function conectarBD() {
        $this->conn = new mysqli(
            $this->servidor, 
            $this->usuario, 
            $this->clave, 
            $this->bd
        );

        // Manejo de errores de conexión
        if ($this->conn->connect_error) {
            die("Error de conexión: " . $this->conn->connect_error); // Termina script si hay error
        }
    }

    // Destructor - cierra conexión al destruir el objeto
    public function __destruct() {
        if ($this->conn && !$this->conn->connect_error) {
            $this->conn->close(); // Cierra conexión si está abierta
        }
    }

    // Getter para obtener la conexión
    public function getConexion() {
        return $this->conn; // Retorna el objeto de conexión
    }
}
?>