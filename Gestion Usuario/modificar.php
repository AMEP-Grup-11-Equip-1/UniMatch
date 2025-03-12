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
    $nombre_usuario = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = trim($_POST['contraseña']);
    $usuarioID = $_SESSION['usuarioID']; // ID del usuario almacenado en la sesión

    // Verificar si el nombre de usuario o el correo ya están registrados por otro usuario
    $sql_verificar = "SELECT * FROM usuarios WHERE (nom_usuari = ? OR correu_electronic = ?) AND id != ?";
    $stmt_verificar = $conn->prepare($sql_verificar);
    $stmt_verificar->bind_param("ssi", $nombre_usuario, $email, $usuarioID);
    $stmt_verificar->execute();
    $resultado = $stmt_verificar->get_result();

    if ($resultado->num_rows > 0) {
        // Si el usuario o el correo ya están registrados por otro usuario
        $_SESSION['error'] = "¡El usuario o correo ya están registrados!";
        header("Location: ../Pantalla%20Perfil/perfil.php");
        exit();
    }
    $stmt_verificar->close();

    // Si la contraseña ha sido modificada, la encriptamos
        

    // Consulta para actualizar los datos del usuario
    $sql = "UPDATE usuarios SET nom_usuari = ?, correu_electronic = ?, password = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $nombre_usuario, $email, $password, $usuarioID);

    if ($stmt->execute()) {
        // Si la actualización fue exitosa, actualizamos los datos en la sesión
        $_SESSION['usuario'] = $nombre_usuario;
        $_SESSION['email'] = $email;
        if(!empty($pasword)){   
            $_SESSION['contraseña'] = $password;
        }

        // Redirigir al perfil
        header("Location: ../Pantalla%20Perfil/perfil.php");
        exit();
    } else {
        $_SESSION['error'] = "Error al actualizar los datos del usuario.";
        header("Location: perfil.php");
        exit();
    }

    $stmt->close();
}

$conn->close();
?>
