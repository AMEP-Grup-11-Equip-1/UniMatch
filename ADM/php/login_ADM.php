<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'DataBase_ADM.php';

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

            // Nunca armazene a senha na sessão (boa prática)
            unset($admin['password']); 

            $_SESSION['admin'] = $admin;

            header("Location: ../html/perfil_ADM.php");
            exit();
        } else {
            $_SESSION['login_error'] = "Usuario o contraseña incorrectos.";
            header("Location: ../html/login_ADM.html");
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

// Se for uma requisição AJAX via GET, retorna os dados do ADM logado
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    header('Content-Type: application/json');

    if (isset($_SESSION['admin'])) {
        $admin = $_SESSION['admin'];
        echo json_encode([
            'id' => $admin['id'],           // ou 'id_ADM', dependendo do nome real no banco
            'name' => $admin['name'],       // ajuste conforme o nome da coluna
            'mail' => $admin['mail']
        ]);
    } else {
        echo json_encode(['error' => 'não logado']);
    }

    exit();
}
?>
