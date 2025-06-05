<?php
header('Content-Type: application/json');

require_once 'config/database.php';
require_once 'classes/Pedido.php';

$input = file_get_contents('php://input');
error_log("Webhook recebido: " . $input);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);
    exit;
}

$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'JSON inválido']);
    exit;
}

if (!isset($data['id']) || !isset($data['status'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Campos obrigatórios: id, status']);
    exit;
}

$pedido_id = intval($data['id']);
$status = trim(strtolower($data['status']));

$status_validos = ['pendente', 'confirmado', 'enviado', 'entregue', 'cancelado'];
if (!in_array($status, $status_validos)) {
    http_response_code(400);
    echo json_encode(['error' => 'Status inválido. Valores aceitos: ' . implode(', ', $status_validos)]);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $pedido = new Pedido($db);
    
    $pedido_data = $pedido->buscarPorId($pedido_id);
    
    if (!$pedido_data) {
        http_response_code(404);
        echo json_encode(['error' => 'Pedido não encontrado']);
        exit;
    }
    
    if ($status === 'cancelado') {
        if ($pedido->excluir($pedido_id)) {
            echo json_encode([
                'success' => true,
                'message' => 'Pedido cancelado e removido com sucesso',
                'pedido_id' => $pedido_id
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao remover pedido']);
        }
    } else {
        if ($pedido->atualizarStatus($pedido_id, $status)) {
            echo json_encode([
                'success' => true,
                'message' => 'Status do pedido atualizado com sucesso',
                'pedido_id' => $pedido_id,
                'novo_status' => $status
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao atualizar status do pedido']);
        }
    }
    
} catch (Exception $e) {
    error_log("Erro no webhook: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?>
