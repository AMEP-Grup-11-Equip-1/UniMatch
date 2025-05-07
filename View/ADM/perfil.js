// Realiza una solicitud fetch al controlador PHP para verificar si el administrador está logueado
fetch("../../Controller/ADM_login.php")
    .then(response => response.json()) // Convierte la respuesta en formato JSON
    .then(data => {
        // Si hay un error, redirige a la página de login
        if (data.error) {
            window.location.href = "login_ADM.html";
        } else {
            // Si no hay error, muestra los datos del administrador en el perfil
            document.getElementById("id").textContent = data.id;
            document.getElementById("name").textContent = data.name;
            document.getElementById("mail").textContent = data.mail;
        }
    })
    .catch(error => {
        // Muestra un mensaje de error en la consola si falla la solicitud
        console.error("Error al cargar los datos:", error);
    });