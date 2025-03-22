<?php
session_start();
include 'DataBase.php';

class AutenticarADM {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function autenticar($mail, $password) {
        $stmt = $this->conn->prepare("SELECT * FROM ADM WHERE mail = ? AND password = ?");
        $stmt->bind_param("ss", $mail, $password);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            $_SESSION['admin'] = $admin;  // Guarda toda la info del ADM

            header("Location: perfil_ADM.php");
            exit();
        } else {
            $_SESSION['login_error'] = "Usuario o contraseña incorrectos.";
            header("Location: ../ADM/login_ADM.html");
            exit();
        }
    
        $stmt->close();
    }
}

// Criar a conexão
$bd = new ConexionBD();
$conexion = $bd->getConexion();

// Processar o login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $autenticacion = new AutenticarADM($conexion);
    $mail = trim($_POST['mail']);
    $password = trim($_POST['password']);
    $autenticacion->autenticar($mail, $password);
}

// Se for uma requisição AJAX, retorna os dados do ADM logado
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_SESSION['admin'])) {
    header('Content-Type: application/json');
    echo json_encode($_SESSION['admin']);
    exit();
}
?>