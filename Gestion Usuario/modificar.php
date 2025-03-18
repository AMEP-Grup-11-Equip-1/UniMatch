<?php
session_start();

class ActualizacionPerfil {
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

    public function actualizarPerfil($nombre_usuario, $email, $password) {
        $usuarioID = $_SESSION['usuarioID'];

        if ($this->usuarioOCorreoExistente($nombre_usuario, $email, $usuarioID)) {
            $_SESSION['error'] = "¡El usuario o correo ya están registrados!";
            header("Location: ../Pantalla%20Perfil/perfil.php");
            exit();
        }

        $sql = "UPDATE usuarios SET nom_usuari = ?, correu_electronic = ?, password = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssi", $nombre_usuario, $email, $password, $usuarioID);

        if ($stmt->execute()) {
            $_SESSION['usuario'] = $nombre_usuario;
            $_SESSION['email'] = $email;

            if (!empty($password)) {
                $_SESSION['contraseña'] = $password;
            }

            header("Location: ../Pantalla%20Perfil/perfil.php");
            exit();
        } else {
            $_SESSION['error'] = "Error al actualizar los datos del usuario.";
            header("Location: perfil.php");
            exit();
        }

        $stmt->close();
    }

    private function usuarioOCorreoExistente($nombre_usuario, $email, $usuarioID) {
        $sql = "SELECT * FROM usuarios WHERE (nom_usuari = ? OR correu_electronic = ?) AND id != ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $nombre_usuario, $email, $usuarioID);
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
    $actualizacion = new ActualizacionPerfil();
    $nombre_usuario = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = trim($_POST['contraseña']);
    $actualizacion->actualizarPerfil($nombre_usuario, $email, $password);
}
?>
