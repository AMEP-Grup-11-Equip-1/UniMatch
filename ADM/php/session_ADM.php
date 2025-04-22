<?php
session_start();

header('Content-Type: application/json');

// Verifica se a sessão do administrador está aberta
if (isset($_SESSION['admin'])) {
    $admin = $_SESSION['admin']; // Obtém os dados da sessão do administrador
    echo json_encode([
        'id' => $admin['id'],           // ou 'id_ADM', dependendo do nome real no banco
        'name' => $admin['name'],       // ajuste conforme o nome da coluna
        'mail' => $admin['mail']
    ]);
} else {
    echo json_encode(['error' => 'não logado']);
}
?>