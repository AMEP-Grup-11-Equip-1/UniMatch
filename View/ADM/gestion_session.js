// ===============================
// Funciones de Sesión del Administrador
// ===============================

// Verifica si la sesión del administrador está activa
function IsOpenSession() {
    fetch("../../Controller/ADM_login.php", { cache: "no-store" })
        .then(response => {
            return response.text();
        })
        .then(text => {
            try {
                const data = JSON.parse(text);
                if (!data || !data.id) {
                    window.location.href = "login_ADM.html";
                } else {
                    // sesión activa
                }
            } catch (e) {
                window.location.href = "login_ADM.html";
            }
        })
        .catch(error => {
            window.location.href = "login_ADM.html";
        });
}

// Muestra los datos del administrador en los elementos HTML con IDs "name" y "mail"
function ShowAdminData(data) {
    const nameElement = document.getElementById("name");
    const mailElement = document.getElementById("mail");

    if (nameElement) nameElement.textContent = data.name || "error";
    if (mailElement) mailElement.textContent = data.mail || "error";
}

// Obtiene los datos del administrador y, si es aplicable, los muestra en el HTML
function FetchAndShowAdminData() {
    fetch("../../Controller/ADM_login.php", { cache: "no-store" })
        .then(response => response.json())
        .then(data => {
            if (data && data.id) {
                ShowAdminData(data);
            } else {
                console.error("Sesión inválida.");
            }
        })
        .catch(error => {
            console.error("Error al obtener los datos del administrador:", error);
        });
}

// ===============================
// Ejecución Automática al Cargar la Página
// ===============================
document.addEventListener("DOMContentLoaded", () => {
    IsOpenSession(); // Siempre verifica la sesión

    // Solo obtiene y muestra los datos si los elementos existen en la página
    const nameExists = document.getElementById("name");
    const mailExists = document.getElementById("mail");
    if (nameExists || mailExists) {
        FetchAndShowAdminData();
    }
});