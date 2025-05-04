<?php
session_start();

// Mostrar errores (solo en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluye la clase de conexión a la base de datos
include_once '../Model/DataBase.php';
<link rel="icon" href="../Imagenes/img1.png" type="image/x-icon">


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

            header("Location: ../View/ADM/perfil_ADM.html");
            exit();
        } else {
            $_SESSION['login_error'] = "Usuario o contraseña incorrectos.";
            header("Location: ../View/ADM/login_ADM.html");
            exit();
        }
    
        $stmt->close();
    }
}

// Crea la conexión
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
