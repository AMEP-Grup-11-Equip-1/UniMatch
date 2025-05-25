<?php
session_start();
require_once("../Model/DataBase.php");
require_once("../Model/Grup.php");

// Mostrar errores (solo en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuración Cloudinary
$cloud_name = 'dpgwhmtud';
$api_key = '394999426817163';
$api_secret = '69kEgo9x1YRaxYm2CHJb4JP5vm4';

// Verificar datos del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['nom'], $_POST['descripcio'], $_POST['visibilidad']) || !isset($_FILES['imagen'])) {
        echo "Error: Datos incompletos o imagen no subida.";
        exit();
    }

    $nombre = trim($_POST['nom']);
    $descripcion = trim($_POST['descripcio']);
    $visibilidad = trim($_POST['visibilidad']);
    $imagen = $_FILES['imagen'];

    // Validar los datos
    if (strlen($nombre) < 2 || strlen($descripcion) < 5) {
        echo "Error: Los campos deben tener contenido válido.";
        exit();
    }

    if ($visibilidad !== "public" && $visibilidad !== "privat") {
        echo "Error: Valor de visibilidad no válido.";
        exit();
    }

    // Verificar que el usuario esté logueado
    if (!isset($_SESSION['usuarioID'])) {
        echo "Error: No has iniciado sesión.";
        exit();
    }
    $propietario_id = $_SESSION['usuarioID'];

    // Subir imagen a Cloudinary
    if ($imagen['error'] === 0) {
        $timestamp = time();
        $string_to_sign = "timestamp=$timestamp" . $api_secret;
        $signature = sha1($string_to_sign);

        $ch = curl_init();
        $data = [
            'file' => new CURLFile($imagen['tmp_name']),
            'api_key' => $api_key,
            'timestamp' => $timestamp,
            'signature' => $signature
        ];

        curl_setopt($ch, CURLOPT_URL, "https://api.cloudinary.com/v1_1/$cloud_name/image/upload");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            echo "Error en la subida a Cloudinary: " . curl_error($ch);
            exit();
        }
        curl_close($ch);

        $response_data = json_decode($response, true);

        if (!isset($response_data['secure_url'])) {
            echo "Error en la respuesta de Cloudinary: " . $response;
            exit();
        }

        $urlImagen = $response_data['secure_url'];

    } else {
        echo "Error: No se recibió ninguna imagen válida.";
        exit();
    }

    // Conectar a la base de datos
    $db = new ConexionBD();
    $conn = $db->getConexion();

    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // Verificar que el usuario exista en la base de datos
    $checkUser = $conn->prepare("SELECT id FROM usuario WHERE id = ?");
    $checkUser->bind_param("i", $propietario_id);
    $checkUser->execute();
    $checkUser->store_result();

    if ($checkUser->num_rows === 0) {
        echo "Error: Usuario no válido o no encontrado en la base de datos.";
        exit();
    }
    $checkUser->close();

    // Crear el grupo incluyendo la URL de la imagen
    $grupModel = new Grup($conn);
    $resultado = $grupModel->crearGrup($nombre, $descripcion, $visibilidad, $propietario_id, $urlImagen);

    if ($resultado === true) {
        header("Location: ../View/Pantalla_Inicio/bienvenida.html");
        exit();
    } else {
        echo "Error al guardar el grupo. Consulta el log.";
    }

} else {
    echo "Método no permitido.";
}
?>
