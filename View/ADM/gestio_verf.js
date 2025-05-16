console.log("Script carregado!");

function carregarDados() {
  // 1. Carregar dados de verificação
  const xhr1 = new XMLHttpRequest();
  xhr1.open("GET", "../../Controller/gestion_verf.php", true);
  xhr1.onload = function() {
    if (xhr1.status === 200) {
      document.getElementById("tabla-datos").innerHTML = xhr1.responseText;
    } else {
      console.error("Erro na solicitação 1:", xhr1.status, xhr1.statusText);
    }
  };
  xhr1.onerror = function() {
    console.error("Falha na conexão (solicitação 1)");
  };

  // 2. Carregar dados de denúncias
  const xhr2 = new XMLHttpRequest();
  xhr2.open("GET", "../../Controller/gestion_denun.php", true);
  xhr2.onload = function() {
    if (xhr2.status === 200) {
      document.getElementById("tabla-denun").innerHTML = xhr2.responseText;
    } else {
      console.error("Erro na solicitação 2:", xhr2.status, xhr2.statusText);
    }
  };
  xhr2.onerror = function() {
    console.error("Falha na conexão (solicitação 2)");
  };

  // Envia ambas as requisições
  xhr1.send();
  xhr2.send();
}

window.onload = carregarDados;