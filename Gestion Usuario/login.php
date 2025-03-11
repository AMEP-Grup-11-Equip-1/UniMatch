<?php
session_start(); // Iniciar sesión

$servidor = "ubiwan.epsevg.upc.edu"; // O la IP de tu servidor MySQL
$usuario = "amep04";       // Usuario de MySQL
$clave = "od5Ieg6Keit0ai"; // Contraseña de MySQL
$bd = "amep04";           // Nombre de la base de datos

// Conectar a MySQL
$conn = new mysqli($servidor, $usuario, $clave, $bd);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Inicializar error
if (!isset($_SESSION['error'])) {
    $_SESSION['error'] = "";
}

// Si se envió el formulario por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Consulta para buscar el usuario
    $sql = "SELECT * FROM usuarios WHERE nom_usuari = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();

        // Verificar la contraseña (sin encriptar)
        if ($password == $fila['password']) {
            $_SESSION['usuario'] = $fila['nom_usuari'];
            $_SESSION['error'] = ""; // Reiniciar error si inicia sesión correctamente
            header("Location: ../Pantalla%20Inicio/bienvenida.html");
            exit();
        } else {
            $_SESSION['error'] = "¡Contraseña incorrecta!";
            header("Location: login.php"); // Redirigir de nuevo al login
            exit();
        }
    } else {
        $_SESSION['error'] = "¡Usuario no encontrado!";
        header("Location: ../Pantalla%20de%20Bloqueo/Pantalladebloqueo.html"); // Redirigir de nuevo al login
        exit();
    }
    $stmt->close();
}
$conn->close();
?>