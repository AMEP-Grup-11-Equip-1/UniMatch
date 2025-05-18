console.log("Script carregado!");

// Función para cargar los datos de verificación y denuncias
function carregarDados() {
  // 1. Cargar datos de verificación
  const xhr1 = new XMLHttpRequest();
  xhr1.open("GET", "../../Controller/gestion_verf.php", true);
  xhr1.onload = function() {
    // Si la solicitud es exitosa, insertar la respuesta en el elemento con id "tabla-datos"
    if (xhr1.status === 200) {
      document.getElementById("tabla-datos").innerHTML = xhr1.responseText;
    } else {
      // Si hay error en la solicitud, mostrar mensaje en consola
      console.error("Erro na solicitação 1:", xhr1.status, xhr1.statusText);
    }
  };
  // Si ocurre un error de conexión, mostrar mensaje en consola
  xhr1.onerror = function() {
    console.error("Falha na conexão (solicitação 1)");
  };

  // 2. Cargar datos de denuncias
  const xhr2 = new XMLHttpRequest();
  xhr2.open("GET", "../../Controller/gestion_denun.php", true);
  xhr2.onload = function() {
    // Si la solicitud es exitosa, insertar la respuesta en el elemento con id "tabla-denun"
    if (xhr2.status === 200) {
      document.getElementById("tabla-denun").innerHTML = xhr2.responseText;
    } else {
      // Si hay error en la solicitud, mostrar mensaje en consola
      console.error("Erro na solicitação 2:", xhr2.status, xhr2.statusText);
    }
  };
  // Si ocurre un error de conexión, mostrar mensaje en consola
  xhr2.onerror = function() {
    console.error("Falha na conexão (solicitação 2)");
  };

  // Enviar ambas solicitudes
  xhr1.send();
  xhr2.send();
}

// Ejecutar la función al cargar la página
window.onload = carregarDados;