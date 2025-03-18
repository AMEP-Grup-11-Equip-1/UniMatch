<?php
session_start();

class RegistroUsuario {
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
            die("Error de conexión: " . $this->conn->connect_error);
        }
    }

    public function registrarUsuario($nombre_usuario, $email, $password) {
        if ($this->usuarioExiste($nombre_usuario, $email)) {
            $_SESSION['error'] = "¡El usuario o correo ya están registrados!";
            header("Location: registro.php");
            exit();
        }

        $sql = "INSERT INTO usuarios (nom_usuari, correu_electronic, password) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sss", $nombre_usuario, $email, $password);

        if ($stmt->execute()) {
            header("Location: ../Pantalla%20de%20Bloqueo/registro_exitos.html");
            exit();
        } else {
            $_SESSION['error'] = "Error al registrar el usuario.";
            header("Location: registro.php");
            exit();
        }

        $stmt->close();
    }

    private function usuarioExiste($nombre_usuario, $email) {
        $sql = "SELECT * FROM usuarios WHERE nom_usuari = ? OR correu_electronic = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $nombre_usuario, $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $existe = $resultado->num_rows > 0;
        $stmt->close();

        return $existe;
    }

    public function __destruct() {
        $this->conn->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $registro = new RegistroUsuario();
    $nombre_usuario = trim($_POST['new-username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['new-password']);
    $registro->registrarUsuario($nombre_usuario, $email, $password);
}
?>
