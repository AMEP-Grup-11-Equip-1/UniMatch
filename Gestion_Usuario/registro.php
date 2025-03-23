<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'DataBase.php';

$bd = new ConexionBD();
$conexion = $bd->getConexion();

if (!$conexion) {
    die("Error al conectar a la base de datos.");
}



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $registroUsuario = new RegistroUsuario($conexion); // Aqui, você inicializa o objeto da classe RegistroUsuario

    $email = trim($_POST['email_full']);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["error" => "Invalidy Mail."]);
        exit();
    }

    $usuarioExiste = $registroUsuario->usuarioExiste($email);

    echo json_encode(["existe" => $usuarioExiste]);
    exit();
}

class RegistroUsuario {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function registrarUsuario($nombre_usuario, $email, $password) {
        if ($this->usuarioExiste($email)) {
            $_SESSION['error'] = "¡El Correo ya está registrado!";
            header("Location: ../Pantalla_de_Bloqueo/Pantalladebloqueo.html");
            exit();
        }

        header("Location: ../Pantalla_de_Bloqueo/Registro.html");
        exit();
    }

    public function usuarioExiste($email) {
        $sql = "SELECT * FROM usuario WHERE mail = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email); 
        $stmt->execute();
        $resultado = $stmt->get_result();
        $existe = $resultado->num_rows > 0;
        $stmt->close();

        return $existe;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    header('Content-Type: application/json');

    $sql = "SELECT id, Uni_name, Uni_mail, Uni_acronym FROM universities";
    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        echo json_encode(["error" => "Error al preparar la consulta SQL."]);
        exit();
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $universities = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $universities[] = [
                "id" => $row['id'],
                "Uni_name" => $row['Uni_name'],
                "Uni_mail" => $row['Uni_mail'],
                "Uni_acronym" => $row['Uni_acronym']
            ];
        }
    } else {
        $universities = [];
    }

    $stmt->close();
    echo json_encode(["data" => $universities]);
    exit();
}
?>
