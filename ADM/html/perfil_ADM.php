<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unimatch - ADM</title>
    <link rel="icon" href="../../Imagenes/img1.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/principal.css">
</head>

<body>
    
    <?php include("../php/menu_ADM.php"); ?>
    <!-- Caja de información que muestra los datos del perfil del administrador -->
    <div class="info-box">
        <h2>Perfil del Administrador</h2>
        <p><strong>ID:</strong> <span id="id"></span></p>
        <p><strong>Nombre:</strong> <span id="name"></span></p>
        <p><strong>Email:</strong> <span id="mail"></span></p>
    </div>

    <script>
        // Realiza una solicitud al archivo PHP para obtener los datos del administrador
        fetch("../php/login_ADM.php", { cache: "no-store" })
            .then(response => response.json()) // Convierte la respuesta en formato JSON
            .then(data => {
                // Si deseas ver los datos recibidos en la consola, puedes descomentar la siguiente línea
                //console.log("Datos recebidos:", data);


                // Verifica si hay un error en los datos recibidos
                if (data.error) {
                    // Si hay un error, redirige al usuario a la página de inicio de sesión
                    window.location.href = "login_ADM.html";
                } else {
                    // Si no hay error, muestra los datos del administrador en la página
                    document.getElementById("id").textContent = data.id || "error";
                    document.getElementById("name").textContent = data.name || "error";
                    document.getElementById("mail").textContent = data.mail || "error";
                }
            })
            .catch(error => {
                // Maneja errores en la solicitud o en el procesamiento de los datos
                console.error("Error al cargar los datos:", error);
                document.getElementById("id").textContent = "error";
                document.getElementById("name").textContent = "error";
                document.getElementById("mail").textContent = "error";
                // Optionally, you can add a message to inform the user
                alert("Error al cargar los datos del administrador.");
            });
    </script>

</body>

</html>