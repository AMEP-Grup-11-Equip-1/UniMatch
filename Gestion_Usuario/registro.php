<?php
session_start();
include 'DataBase.php';


class RegistroUsuario {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function registrarUsuario($nombre_usuario, $email, $password) {
        if ($this->usuarioExiste($nombre_usuario, $email)) {
            $_SESSION['error'] = "¡El usuario o correo ya están registrados!";
            header("Location: ../Pantalla_de_Bloqueo/Pantalladebloqueo.html");
            exit();
        }

        $sql = "INSERT INTO usuarios (nom_usuari, correu_electronic, password) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sss", $nombre_usuario, $email, $password);

        if ($stmt->execute()) {
            header("Location: ../Pantalla_de_Bloqueo/registro_exito.html");
            exit();
        } else {
            $_SESSION['error'] = "Error al registrar el usuario.";
            header("Location: ../Pantalla_de_Bloqueo/Pantalladebloqueo.html");
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

// Crea la conexión
$bd = new ConexionBD();
$conexion = $bd->getConexion();

?>
