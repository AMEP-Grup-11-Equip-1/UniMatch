<?php
// Verifica si el formulario fue enviado por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recolectar los valores de los campos del formulario
    $nombre_usuario = $_POST['new-username'];
    $email = $_POST['email'];
    $password = $_POST['new-password'];

    // Concatenar los datos en un formato legible
    $registro = "Usuario: " . $nombre_usuario . "\n" . "Email: " . $email . "\n" . "Contraseña: " . $password . "\n\n";

    // Ruta donde se almacenarán los registros
    $archivo = "usuarios.txt";

    // Intentar abrir el archivo y escribir los datos
    if (file_put_contents($archivo, $registro, FILE_APPEND)) {
        // Redirigir al usuario a la página de agradecimiento después de guardar los datos
        header("Location: agradecimiento.html");
        exit(); // Terminar la ejecución del script
    } else {
        // Si hay un error al escribir el archivo, mostrar un mensaje
        echo "Hubo un error al guardar tus datos. Intenta nuevamente.";
    }
}
?>
