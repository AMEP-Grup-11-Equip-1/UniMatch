<?php
session_start();
include_once '../Model/DataBase.php';
include_once '../Model/User.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['usuarioID'])) {
        $_SESSION['error'] = "No hay usuario autenticado";
        header("Location: ../View/Pantalla_Perfil/perfil.php");
        exit();
    }

    $bd = new ConexionBD();
    $conexion = $bd->getConexion();
    $usuarioModel = new Usuario($conexion);

    $nombre_usuario = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $descripcion = trim($_POST['descripcion']);

    // Subida a Cloudinary
   $urlImagen = null;

   
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
    $cloud_name = 'dpgwhmtud';
    $api_key = '394999426817163';
    $api_secret = '69kEgo9x1YRaxYm2CHJb4JP5vm4';

    $timestamp = time();

    // Crear el string para firmar, solo con parámetros que envías (timestamp en este caso)
    $params_to_sign = "timestamp=$timestamp";

    $signature = sha1($params_to_sign . $api_secret);

    $ch = curl_init();
    $data = [
        'file' => new CURLFile($_FILES['imagen']['tmp_name']),
        'api_key' => $api_key,
        'timestamp' => $timestamp,
        'signature' => $signature,
        // 'folder' => 'tu_carpeta_opcional', // si quieres subir a carpeta específica en Cloudinary
    ];

    curl_setopt($ch, CURLOPT_URL, "https://api.cloudinary.com/v1_1/$cloud_name/image/upload");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $_SESSION['error'] = "Error al subir a Cloudinary: " . curl_error($ch);
        curl_close($ch);
        header("Location: ../View/Pantalla_Perfil/perfil.php");
        exit();
    }

    curl_close($ch);

    $response_data = json_decode($response, true);

    if (!$response_data || !isset($response_data['secure_url'])) {
        $_SESSION['error'] = "Error en la respuesta de Cloudinary: " . $response;
        header("Location: ../View/Pantalla_Perfil/perfil.php");
        exit();
    }

    $urlImagen = $response_data['secure_url'];
} else {
    $urlImagen = null;
}


    // Llamamos a la función que actualiza el perfil
    $result = $usuarioModel->actualizarPerfilCompleto(
        $_SESSION['usuarioID'],
        $nombre_usuario,
        $email,
        $descripcion,
        $password,
        $urlImagen
    );

    if ($result["status"] === "success") {
        $_SESSION['usuario'] = $nombre_usuario;
        $_SESSION['email'] = $email;
        $_SESSION['descripcion'] = $descripcion;
        if (!empty($password)) {
            $_SESSION['password'] = $password;
        }
        if ($urlImagen !== null) {
            $_SESSION['imagen'] = $urlImagen;
        }
        header("Location: ../View/Pantalla_Perfil/perfil.php");
        exit();
    } else {
        $_SESSION['error'] = $result["message"];
        header("Location: ../View/Pantalla_Perfil/perfil.php");
        exit();
    }
}
?>
