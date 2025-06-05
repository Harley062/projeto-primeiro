<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../classes/Produto.php';
require_once '../classes/Carrinho.php';

$database = new Database();
$db = $database->getConnection();

$produto = new Produto($db);
$carrinho = new Carrinho();

if ($_POST['produto_id'] && $_POST['quantidade']) {
    $produto_id = intval($_POST['produto_id']);
    $variacao_id = isset($_POST['variacao_id']) ? intval($_POST['variacao_id']) : null;
    $quantidade = intval($_POST['quantidade']);
    
    $produto_data = $produto->buscarPorId($produto_id);
    
    if (!$produto_data) {
        echo json_encode(['success' => false, 'message' => 'Produto não encontrado']);
        exit;
    }
    
    if (!$produto->verificarEstoque($produto_id, $variacao_id, $quantidade)) {
        echo json_encode(['success' => false, 'message' => 'Estoque insuficiente']);
        exit;
    }
    
    $preco_unitario = floatval($produto_data['preco']);
    $nome_variacao = '';
    
    if ($variacao_id && $produto_data['variacoes']) {
        $variacoes = explode('|', $produto_data['variacoes']);
        foreach ($variacoes as $var) {
            $var_parts = explode(':', $var);
            if (count($var_parts) >= 3 && $var_parts[0] == $variacao_id) {
                $preco_unitario += floatval($var_parts[2]);
                $nome_variacao = $var_parts[1];
                break;
            }
        }
    }
    
    $carrinho->adicionarItem(
        $produto_id, 
        $variacao_id, 
        $quantidade, 
        $preco_unitario, 
        $produto_data['nome'],
        $nome_variacao
    );
    
    echo json_encode(['success' => true, 'message' => 'Produto adicionado ao carrinho']);
} else {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
}
?>
