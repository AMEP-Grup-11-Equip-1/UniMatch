<?php
session_start();

class AutenticacionUsuario {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function autenticar($username, $password) {
        $sql = "SELECT * FROM usuarios WHERE nom_usuari = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $fila = $resultado->fetch_assoc();

            // Verificar contraseña sin encriptar
            if ($password === $fila['password']) {
                $_SESSION['usuario'] = $fila['nom_usuari'];
                $_SESSION['email'] = $fila['correu_electronic'];
                $_SESSION['usuarioID'] = $fila['id'];
                $_SESSION['contraseña'] = $fila['password'];

                $_SESSION['error'] = "";
                header("Location: ../Pantalla_Inicio/bienvenida.html");
                exit();
            } else {
                $_SESSION['error'] = "¡Contraseña incorrecta!";
            }
        } else {
            $_SESSION['error'] = "¡Usuario no encontrado!";
        }

        header("Location: ../Pantalla_de_Bloqueo/Pantalladebloqueo.html");
        exit();
    }

    public function __destruct() {
        $this->conn->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $autenticacion = new AutenticacionUsuario();
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $autenticacion->autenticar($username, $password);
}
?>

