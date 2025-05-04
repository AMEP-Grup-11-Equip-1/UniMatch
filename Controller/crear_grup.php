<?php
session_start();
require_once("../Model/DataBase.php");
require_once("../Model/Grup.php");

// Mostrar errores (solo en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);

   
}

// Verificar si los datos POST están disponibles
if (!isset($_POST['nom'], $_POST['descripcio'], $_POST['visibilitat'])) {
    echo "Error: Datos incompletos.";
    exit();
}

$nombre = trim($_POST['nom']);
$descripcion = trim($_POST['descripcio']);
$visibilidad = trim($_POST['visibilitat']);

// Validar los datos
if (strlen($nombre) < 2 || strlen($descripcion) < 5) {
    echo "Error: Los campos deben tener contenido válido.";
    exit();
}

if ($visibilidad !== "public" && $visibilidad !== "privat") {
    echo "Error: Valor de visibilidad no válido.";
    exit();
}

// Conectar a la base de datos
$db = new ConexionBD();
$conn = $db->getConexion();

// Verificar si la conexión fue exitosa
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Verificar que el usuario está logueado
if (!isset($_SESSION['usuarioID'])) {
    echo "Error: No has iniciado sesión.";
    exit();
}

$propietario_id = $_SESSION['usuarioID'];

// Depurar la variable de sesión
echo "UsuarioID: " . $propietario_id . "<br>"; // Muestra el valor de $_SESSION['usuarioID']

// Verificar que el usuario exista en la base de datos
$checkUser = $conn->prepare("SELECT id FROM usuario WHERE id = ?");
$checkUser->bind_param("i", $propietario_id);
$checkUser->execute();
$checkUser->store_result();

// Depuración adicional para mostrar si la consulta encuentra al usuario
if ($checkUser->num_rows === 0) {
    echo "Error: Usuario no válido o no encontrado en la base de datos.";
    exit();
} else {
    echo "Usuario válido encontrado.<br>"; // Solo para depuración
}

$checkUser->close();

// Crear el grupo
$grupModel = new Grup($conn);
$resultado = $grupModel->crearGrup($nombre, $descripcion, $visibilidad, $propietario_id);

// Redirigir o mostrar mensaje de error según el resultado
if ($resultado === true) {
    header("Location: ../View/Pantalla_Inicio/bienvenida.html");
    exit();
} else {
    echo "Error al guardar el grupo. Consulta el log.";
}
?>
