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
                if (data && data.id) {
                    window.location.href = "home.html";
                }
            } catch (e) {
                //window.location.href = "login_ADM.html";
            }
        })
        .catch(error => {
           // window.location.href = "login_ADM.html";
        });
}

// ===============================
// Ejecución Automática al Cargar la Página
// ===============================
document.addEventListener("DOMContentLoaded", () => {
    IsOpenSession(); // Siempre verifica la sesión
});