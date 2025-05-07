<?php
require_once("../Model/DataBase.php");
require_once("../Model/Historia.php");

// Configuració de Cloudinary
$cloud_name = 'dpgwhmtud';
$api_key = '394999426817163';
$api_secret = '69kEgo9x1YRaxYm2CHJb4JP5vm4';

if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
    $imagen = $_FILES['imagen'];

    // Firmar la subida a Cloudinary
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

    // Decodificar la respuesta de Cloudinary
    $response_data = json_decode($response, true);

    if (!isset($response_data['secure_url'])) {
        echo "Error en la respuesta de Cloudinary: " . $response;
        exit();
    }

    // Obtener la URL segura de la imagen
    $urlImagen = $response_data['secure_url'];

    // Recoger los datos del formulario
    $nombre = $_POST['nombre'];
    $universidad = $_POST['universidad'];
    $descripcion = $_POST['descripcion'];

    if (strlen($nombre) < 2 || strlen($universidad) < 2 || strlen($descripcion) < 5) {
        echo "Error: Los campos deben tener contenido válido.";
        exit();
    }

    // Guardar a la BD via Model
    $db = new ConexionBD();
    $conn = $db->getConexion();
    $historiaModel = new Historia($conn);

    session_start(); // ⚠️ Afegeix això si no hi era abans
    $usuario_id = $_SESSION['usuarioID']; // Assumeix que l'usuari està loguejat
    
    if ($historiaModel->crear($nombre, $universidad, $descripcion, $urlImagen, $usuario_id)) {
    
        header("Location: ../View/Pantalla_Inicio/bienvenida.html");
        exit();
    } else {
        echo "Error al guardar en la base de datos.";
    }
} else {
    echo "No se recibió ninguna imagen o hubo un error.";
}
?>
