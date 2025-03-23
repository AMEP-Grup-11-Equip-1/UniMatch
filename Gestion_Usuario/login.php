<?php
session_start();
include 'DataBase.php';

$bd = new ConexionBD();
$conexion = $bd->getConexion();

class AutenticacionUsuario {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function autenticar($username, $password) {
        $sql = "SELECT id, name, password, mail FROM usuario WHERE mail = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $fila = $resultado->fetch_assoc();

            if ($password === $fila['password']) {
                $_SESSION['usuario'] = $fila['name'];
                $_SESSION['email'] = $fila['mail'];
                $_SESSION['usuarioID'] = $fila['id'];
                $_SESSION['error'] = "";

                header("Location: ../Pantalla_Inicio/bienvenida.html");
                exit();
            } else {
                $_SESSION['error'] = "¡Contraseña incorrecta!";
            }
        } else {
            $_SESSION['error'] = "¡Usuario no encontrado!";
        }

        $error = urlencode($_SESSION['error']);
        header("Location: ../Pantalla_de_Bloqueo/Login.html?error=" . $error);
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $autenticacion = new AutenticacionUsuario($conexion);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $autenticacion->autenticar($username, $password);
}
?>