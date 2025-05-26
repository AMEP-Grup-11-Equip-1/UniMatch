<?php
// === 1) CONFIGURAÇÃO INICIAL ===
ini_set('display_errors', 0);
ini_set('log_errors',     1);
// Atenção: ajuste o caminho abaixo para a sua pasta de logs  
ini_set('error_log',      __DIR__ . '/../logs/php-error.log');
error_reporting(E_ALL);

// Converte warnings/notices em exceptions
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Garantir JSON de saída mesmo em fatal errors
register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        error_log("FATAL: {$err['message']} in {$err['file']} on line {$err['line']}");
        echo json_encode([
            'status'  => 'error',
            'message' => 'Erro interno no servidor (checando log).'
        ]);
    }
});

// Define que a resposta será JSON
header('Content-Type: application/json; charset=utf-8');

try {
    // === 2) INCLUDES COM CAMINHOS ABSOLUTOS ===
    $modelDir = realpath(__DIR__ . '/../Model');
    if (!$modelDir) {
        throw new Exception("Pasta Model não encontrada");
    }
    require_once $modelDir . '/DataBase.php';
    require_once $modelDir . '/user.php';

    // === 3) VALIDAÇÃO DO PARÂMETRO ===
    if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
        http_response_code(400);
        echo json_encode(['status'=>'error','message'=>'ID inválido']);
        exit;
    }
    $id = (int) $_GET['id'];

    // === 4) CONEXÃO E QUERY ===
    $db   = new ConexionBD();
    $conn = $db->getConexion();
    if (!$conn) {
        throw new Exception("Falha na conexão ao banco");
    }

    $sql = "
        SELECT 
    r.id AS denuncia_id,
    u2.id AS denunciante_id,
    u2.name AS denunciante,
    u1.id AS denunciado_id,
    u1.name AS denunciado,
    rt.name AS tipo_denuncia,
    rt.description AS descricao, 
    r.created_at AS data,
    r.history_id AS historia_id,
    h.imagen AS imagem_historia,
    r.status AS status
FROM 
    reports r
JOIN 
    usuario u1 ON r.target_user_id = u1.id  
JOIN 
    usuario u2 ON r.reporting_user_id = u2.id  
JOIN 
    report_types rt ON r.report_type_id = rt.id 
LEFT JOIN 
    historias h ON r.history_id = h.id
WHERE 
    r.id = ?
LIMIT 1;
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Erro no prepare(): " . $conn->error);
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['status'=>'error','message'=>'Denúncia não encontrada']);
        exit;
    }

    $row = $result->fetch_assoc();
    // Formata data como string ISO
    $row['data'] = date('Y-m-d H:i:s', strtotime($row['data']));

    // === 5) ENVIA DADOS ===
    echo json_encode($row);
    exit;

} catch (Exception $ex) {
    // Qualquer erro vira JSON com 500
    http_response_code(500);
    error_log("EXCEPTION: " . $ex->getMessage());
    echo json_encode([
        'status'  => 'error',
        'message' => $ex->getMessage()
    ]);
    exit;
}
