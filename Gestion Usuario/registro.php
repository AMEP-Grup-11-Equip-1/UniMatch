<?php
session_start(); // Iniciar sesión

// Datos de conexión a MySQL
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

// Si se envió el formulario por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recolectar los valores de los campos del formulario
    $nombre_usuario = trim($_POST['new-username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['new-password']);

    // Verificar si el usuario ya existe en la base de datos
    $sql_verificar = "SELECT * FROM usuarios WHERE nom_usuari = ? OR correu_electronic = ?";
    $stmt_verificar = $conn->prepare($sql_verificar);
    $stmt_verificar->bind_param("ss", $nombre_usuario, $email);
    $stmt_verificar->execute();
    $resultado = $stmt_verificar->get_result();

    if ($resultado->num_rows > 0) {
        // Si el usuario ya existe, redirigir de nuevo con un error
        $_SESSION['error'] = "¡El usuario o correo ya están registrados!";
        header("Location: registro.php");
        exit();
    }
    $stmt_verificar->close();

    // Insertar en la base de datos
    $sql = "INSERT INTO usuarios (nom_usuari, correu_electronic, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $nombre_usuario, $email, $password);

    if ($stmt->execute()) {
        // Redirigir a la página de éxito si el registro fue exitoso
        header("Location: ../Pantalla%20de%20Bloqueo/registro_exito.html");
        exit();
    } else {
        $_SESSION['error'] = "Error al registrar el usuario.";
        header("Location: registro.php");
        exit();
    }

    $stmt->close();
}

$conn->close();
?>
