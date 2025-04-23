<?php
// Configuración de debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inicio de sesión y conexión a BD
session_start();
include 'DataBase_ADM.php';

// Clase para autenticación de administradores
class AutenticarADM {
    private $conn;

    // Recibe conexión a BD
    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    // Lógica principal de autenticación
    public function autenticar($mail, $password) {
        // Consulta preparada para evitar inyección SQL
        $stmt = $this->conn->prepare("SELECT * FROM ADM WHERE mail = ? AND password = ?");
        $stmt->bind_param("ss", $mail, $password);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            unset($admin['password']); // Elimina pass por seguridad
            
            $_SESSION['admin'] = $admin; // Guarda datos en sesión
            header("Location: ../html/perfil_ADM.php"); // Redirige a perfil
            exit();
        } else {
            // Mensaje de error y redirección
            $_SESSION['login_error'] = "Usuario o contraseña incorrectos.";
            header("Location: ../html/login_ADM.html");
            exit();
        }

        $stmt->close();
    }
}

// Establece conexión con BD
$bd = new ConexionBD();
$conexion = $bd->getConexion();

// Maneja POST (envío de formulario)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $autenticacion = new AutenticarADM($conexion);
    $autenticacion->autenticar(trim($_POST['mail']), trim($_POST['password']));
}

// Maneja GET (verificación de sesión)
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    header('Content-Type: application/json');
    echo isset($_SESSION['admin']) 
        ? json_encode(["logged" => true, "admin" => $_SESSION['admin']]) 
        : json_encode(["logged" => false]);
    exit();
}