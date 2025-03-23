<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'DataBase.php';

$bd = new ConexionBD();
$conexion = $bd->getConexion();


if (!$conexion) {
    die("Error al conectar a la base de datos.");
}

class RegistroUsuario {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function registrarUsuario($nombre_usuario, $password, $email, $uni, $gender) {
        //# id, name, password, mail, uni, gender, bloqued

        $sql = "INSERT INTO usuario (name, password, mail, uni, gender) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssss", $nombre_usuario, $password, $email, $uni, $gender);

        if ($stmt->execute()) {
            header("Location: ../Pantalla_de_Bloqueo/registro_exitos.html");
            exit();
        } else {
            $_SESSION['error'] = "Error al registrar el usuario.";
            header("Location: ../Pantalla_de_Bloqueo/Pantalladebloqueo.html");
            exit();
        }

        $stmt->close();
    }

    public function usuarioExiste($email) {
        $sql = "SELECT * FROM usuario WHERE mail = ?";
        $stmt = $this->conn->prepare($sql);
    
        if (!$stmt) {
            // Erro ao preparar a query
            return false;
        }
    
        $stmt->bind_param("s", $email); 
        if (!$stmt->execute()) {
            // Erro ao executar a query
            $stmt->close();
            return false;
        }
    
        $resultado = $stmt->get_result();
        $existe = $resultado->num_rows > 0;
        $stmt->close();
    
        return $existe;
    }

    
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    header('Content-Type: application/json');

    if(isset($_GET['action'])) {
        $registroUsuario = new RegistroUsuario($conexion); // Criar a instância da classe

        switch ($_GET['action']) {
            case 'getUniversities':
                $universities = $bd->getUniversitiesJSON();
                if (!$universities) {
                    $universities = []; // Garante que o JSON sempre tenha um array
                }
                echo json_encode(["data" => $universities]);
                break;

            case 'getGenders':
                $gender = $bd->getGenderJSON();
                echo json_encode(["data" => $gender]);
                break;
            
            case 'isValid':
                $email = trim($_GET['email_full']);
                
                if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    echo json_encode(["success" => false, "message" => "Correo electrónico inválido."]);
                    exit();
                }
                
                $usuarioExiste = $registroUsuario->usuarioExiste($email);
                if ($usuarioExiste) {
                    echo json_encode(["success" => false, "message" => "El correo electrónico ya está registrado."]);
                } else {
                    echo json_encode(["success" => true, "message" => "Correo electrónico válido."]);
                }
                break;

            default:
                echo json_encode(['error' => 'Invalid action']);
                break;
        }
    } 
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $registro = new RegistroUsuario($conexion);
    $nombre_usuario = trim($_POST['username']);
    $email = trim($_POST['email_full_2']);
    $password = trim($_POST['password']);
    $gender = trim ($_POST['id_gender']);
    $uni = trim ($_POST['id_uni']);
    $registro->registrarUsuario($nombre_usuario, $password, $email, $uni, $gender);
}

?>


