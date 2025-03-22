<?php
//session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'DataBase.php';

// Cria a conexão
$bd = new ConexionBD();
$conexion = $bd->getConexion();

// Verifica se a conexão foi estabelecida com sucesso
if (!$conexion) {
    die("Erro ao conectar ao banco de dados.");
}

// Requisição POST para registrar um usuário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $registro = new RegistroUsuario($conexion);
    $nombre_usuario = trim($_POST['new-username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['new-password']);
    $registro->registrarUsuario($nombre_usuario, $email, $password);
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

        // Aqui você deve adicionar a lógica para inserir o usuário no banco de dados
        // Exemplo:
        // $sql = "INSERT INTO usuario (nome, mail, senha) VALUES (?, ?, ?)";
        // $stmt = $this->conn->prepare($sql);
        // $stmt->bind_param("sss", $nombre_usuario, $email, $password);
        // $stmt->execute();

        header("Location: ../Pantalla_de_Bloqueo/Registro.html");
        exit();
    }

    private function usuarioExiste($email) {
        $sql = "SELECT * FROM usuario WHERE mail = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $email); 
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

// Requisição GET para retornar os dados de gênero
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    header('Content-Type: application/json');

    $sql = "SELECT id, Gender FROM generes";
    $stmt = $conexion->prepare($sql);

    if (!$stmt) {
        echo json_encode(["error" => "Erro ao preparar a consulta SQL."]);
        exit();
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $genders = [];

    // Verifica se a consulta retornou algum dado
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $genders[] = [
                "id" => $row['id'],
                "Gender" => $row['Gender']
            ];
        }
    } else {
        // Se não houver dados, retorna um array vazio
        $genders = [];
    }

    $stmt->close();

    // Retorna os dados como JSON
    echo json_encode(["data" => $genders]);
    exit();
}
?>