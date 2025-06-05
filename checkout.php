<?php
$page_title = "Finalizar Compra";
require_once 'config/database.php';
require_once 'classes/Carrinho.php';
require_once 'classes/Cupom.php';
require_once 'classes/Pedido.php';
require_once 'classes/Produto.php';

$database = new Database();
$db = $database->getConnection();

$carrinho = new Carrinho();
$cupom = new Cupom($db);
$pedido = new Pedido($db);
$produto = new Produto($db);

$itens = $carrinho->obterItens();
if (empty($itens)) {
    header('Location: carrinho.php');
    exit;
}

$message = '';
$message_type = '';

if ($_POST && isset($_POST['finalizar_pedido'])) {
    $subtotal = $carrinho->calcularSubtotal();
    $frete = $carrinho->calcularFrete($subtotal);
    
    $desconto = 0;
    $cupom_id = null;
    if (isset($_SESSION['cupom'])) {
        $cupom_aplicado = $_SESSION['cupom'];
        $desconto = $cupom->calcularDesconto($cupom_aplicado, $subtotal);
        $cupom_id = $cupom_aplicado['id'];
    }
    
    $total = $subtotal - $desconto + $frete;
    
    $estoque_ok = true;
    foreach ($itens as $item) {
        if (!$produto->verificarEstoque($item['produto_id'], $item['variacao_id'], $item['quantidade'])) {
            $estoque_ok = false;
            break;
        }
    }
    
    if (!$estoque_ok) {
        $message = 'Alguns itens não possuem estoque suficiente!';
        $message_type = 'danger';
    } else {
        $pedido->subtotal = $subtotal;
        $pedido->desconto = $desconto;
        $pedido->frete = $frete;
        $pedido->total = $total;
        $pedido->cupom_id = $cupom_id;
        $pedido->status = 'pendente';
        $pedido->cliente_nome = $_POST['cliente_nome'];
        $pedido->cliente_email = $_POST['cliente_email'];
        $pedido->cliente_telefone = $_POST['cliente_telefone'];
        $pedido->endereco_cep = $_POST['endereco_cep'];
        $pedido->endereco_logradouro = $_POST['endereco_logradouro'];
        $pedido->endereco_numero = $_POST['endereco_numero'];
        $pedido->endereco_complemento = $_POST['endereco_complemento'];
        $pedido->endereco_bairro = $_POST['endereco_bairro'];
        $pedido->endereco_cidade = $_POST['endereco_cidade'];
        $pedido->endereco_uf = $_POST['endereco_uf'];
        
        $pedido_id = $pedido->criar();
        
        if ($pedido_id) {
            foreach ($itens as $item) {
                $pedido->adicionarItem(
                    $pedido_id,
                    $item['produto_id'],
                    $item['variacao_id'],
                    $item['quantidade'],
                    $item['preco_unitario']
                );
                
                $produto->reduzirEstoque($item['produto_id'], $item['variacao_id'], $item['quantidade']);
            }
            
            if ($cupom_id) {
                $cupom->usarCupom($cupom_id);
            }
            
            $email_enviado = enviarEmailPedido($pedido->cliente_email, $pedido_id, $total);
            
            $carrinho->limpar();
            unset($_SESSION['cupom']);
            
            header('Location: pedido_sucesso.php?id=' . $pedido_id);
            exit;
        } else {
            $message = 'Erro ao processar pedido!';
            $message_type = 'danger';
        }
    }
}

$subtotal = $carrinho->calcularSubtotal();
$frete = $carrinho->calcularFrete($subtotal);

$desconto = 0;
$cupom_aplicado = null;
if (isset($_SESSION['cupom'])) {
    $cupom_aplicado = $_SESSION['cupom'];
    $desconto = $cupom->calcularDesconto($cupom_aplicado, $subtotal);
}

$total = $subtotal - $desconto + $frete;

function enviarEmailPedido($email, $pedido_id, $total) {
    
    $assunto = "Pedido #$pedido_id confirmado";
    $mensagem = "Seu pedido #$pedido_id foi confirmado com sucesso!\n";
    $mensagem .= "Total: R$ " . number_format($total, 2, ',', '.') . "\n";
    $mensagem .= "Obrigado pela sua compra!";
    
    error_log("EMAIL ENVIADO PARA: $email - ASSUNTO: $assunto - MENSAGEM: $mensagem");
    
    return true;
}

include 'includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h1><i class="fas fa-credit-card"></i> Finalizar Compra</h1>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<form method="POST">
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-user"></i> Dados do Cliente</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nome Completo *</label>
                            <input type="text" class="form-control" name="cliente_nome" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="cliente_email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telefone</label>
                            <input type="tel" class="form-control" name="cliente_telefone">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-map-marker-alt"></i> Endereço de Entrega</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">CEP *</label>
                            <input type="text" class="form-control" name="endereco_cep" id="endereco_cep" 
                                   required maxlength="9" onkeyup="mascaraCEP(this)" onblur="buscarCEP(this.value)">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Logradouro *</label>
                            <input type="text" class="form-control" name="endereco_logradouro" id="endereco_logradouro" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Número *</label>
                            <input type="text" class="form-control" name="endereco_numero" id="endereco_numero" required>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label">Complemento</label>
                            <input type="text" class="form-control" name="endereco_complemento" id="endereco_complemento">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Bairro *</label>
                            <input type="text" class="form-control" name="endereco_bairro" id="endereco_bairro" required>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Cidade *</label>
                            <input type="text" class="form-control" name="endereco_cidade" id="endereco_cidade" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">UF *</label>
                            <input type="text" class="form-control" name="endereco_uf" id="endereco_uf" 
                                   required maxlength="2" style="text-transform: uppercase;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list"></i> Resumo do Pedido</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($itens as $item): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span>
                                <?php echo htmlspecialchars($item['nome_produto']); ?>
                                <?php if ($item['nome_variacao']): ?>
                                    <small class="text-muted">(<?php echo htmlspecialchars($item['nome_variacao']); ?>)</small>
                                <?php endif; ?>
                                x<?php echo $item['quantidade']; ?>
                            </span>
                            <span>R$ <?php echo number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.'); ?></span>
                        </div>
                    <?php endforeach; ?>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></span>
                    </div>
                    
                    <?php if ($cupom_aplicado): ?>
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Desconto (<?php echo htmlspecialchars($cupom_aplicado['codigo']); ?>):</span>
                            <span>-R$ <?php echo number_format($desconto, 2, ',', '.'); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Frete:</span>
                        <span>
                            <?php if ($frete == 0): ?>
                                <span class="text-success">Grátis</span>
                            <?php else: ?>
                                R$ <?php echo number_format($frete, 2, ',', '.'); ?>
                            <?php endif; ?>
                        </span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total:</strong>
                        <strong class="text-success">R$ <?php echo number_format($total, 2, ',', '.'); ?></strong>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="finalizar_pedido" class="btn btn-success btn-lg">
                            <i class="fas fa-check"></i> Finalizar Pedido
                        </button>
                        <a href="carrinho.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Voltar ao Carrinho
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php include 'includes/footer.php'; ?>
