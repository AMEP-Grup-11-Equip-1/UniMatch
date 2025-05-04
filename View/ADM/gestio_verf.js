// Função para fazer a requisição AJAX
function carregarDados() {
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "../../Controller/gestion_verf.php", true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            const dados = xhr.responseText;
            document.getElementById("tabla-datos").innerHTML = dados;
        } else {
            console.error("Erro ao carregar dados");
        }
    };
    xhr.send();
}

// Chama a função ao carregar a página
window.onload = function () {
    carregarDados();
};