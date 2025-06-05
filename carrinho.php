<?php
$page_title = "Carrinho de Compras";
require_once 'config/database.php';
require_once 'classes/Carrinho.php';
require_once 'classes/Cupom.php';

$database = new Database();
$db = $database->getConnection();

$carrinho = new Carrinho();
$cupom = new Cupom($db);

$message = '';
$message_type = '';

if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_quantity':
                $key = $_POST['key'];
                $quantidade = intval($_POST['quantidade']);
                $carrinho->atualizarQuantidade($key, $quantidade);
                $message = 'Quantidade atualizada!';
                $message_type = 'success';
                break;
                
            case 'remove_item':
                $key = $_POST['key'];
                $carrinho->removerItem($key);
                $message = 'Item removido do carrinho!';
                $message_type = 'success';
                break;
                
            case 'apply_coupon':
                $codigo_cupom = trim($_POST['codigo_cupom']);
                $subtotal = $carrinho->calcularSubtotal();
                
                $cupom_data = $cupom->validarCupom($codigo_cupom, $subtotal);
                
                if ($cupom_data) {
                    $_SESSION['cupom'] = $cupom_data;
                    $message = 'Cupom aplicado com sucesso!';
                    $message_type = 'success';
                } else {
                    $message = 'Cupom inválido ou não aplicável!';
                    $message_type = 'danger';
                }
                break;
                
            case 'remove_coupon':
                unset($_SESSION['cupom']);
                $message = 'Cupom removido!';
                $message_type = 'info';
                break;
        }
    }
}

$itens = $carrinho->obterItens();
$subtotal = $carrinho->calcularSubtotal();
$frete = $carrinho->calcularFrete($subtotal);

$desconto = 0;
$cupom_aplicado = null;
if (isset($_SESSION['cupom'])) {
    $cupom_aplicado = $_SESSION['cupom'];
    $desconto = $cupom->calcularDesconto($cupom_aplicado, $subtotal);
}

$total = $subtotal - $desconto + $frete;

include 'includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h1><i class="fas fa-shopping-cart"></i> Carrinho de Compras</h1>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (empty($itens)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> Seu carrinho está vazio.
        <a href="index.php" class="alert-link">Continue comprando</a>.
    </div>
<?php else: ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-list"></i> Itens do Carrinho</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($itens as $key => $item): ?>
                        <div class="row align-items-center mb-3 pb-3 border-bottom">
                            <div class="col-md-6">
                                <h6><?php echo htmlspecialchars($item['nome_produto']); ?></h6>
                                <?php if ($item['nome_variacao']): ?>
                                    <small class="text-muted">Variação: <?php echo htmlspecialchars($item['nome_variacao']); ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-2">
                                <strong>R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?></strong>
                            </div>
                            <div class="col-md-2">
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="update_quantity">
                                    <input type="hidden" name="key" value="<?php echo $key; ?>">
                                    <input type="number" class="form-control form-control-sm" 
                                           name="quantidade" value="<?php echo $item['quantidade']; ?>" 
                                           min="1" max="10" onchange="this.form.submit()">
                                </form>
                            </div>
                            <div class="col-md-1">
                                <strong>R$ <?php echo number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.'); ?></strong>
                            </div>
                            <div class="col-md-1">
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="remove_item">
                                    <input type="hidden" name="key" value="<?php echo $key; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-calculator"></i> Resumo do Pedido</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <span>R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></span>
                    </div>
                    
                    <?php if ($cupom_aplicado): ?>
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>
                                Desconto (<?php echo htmlspecialchars($cupom_aplicado['codigo']); ?>):
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="remove_coupon">
                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </span>
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
                    
                    <?php if (!$cupom_aplicado): ?>
                        <form method="POST" class="mb-3">
                            <input type="hidden" name="action" value="apply_coupon">
                            <div class="input-group">
                                <input type="text" class="form-control" name="codigo_cupom" 
                                       placeholder="Código do cupom">
                                <button type="submit" class="btn btn-outline-secondary">
                                    Aplicar
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                    
                    <div class="d-grid gap-2">
                        <a href="checkout.php" class="btn btn-success btn-lg">
                            <i class="fas fa-credit-card"></i> Finalizar Compra
                        </a>
                        <a href="index.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left"></i> Continuar Comprando
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card mt-3">
                <div class="card-body">
                    <h6><i class="fas fa-truck"></i> Informações de Frete</h6>
                    <small class="text-muted">
                        • Frete grátis para compras acima de R$ 200,00<br>
                        • R$ 15,00 para compras entre R$ 52,00 e R$ 166,59<br>
                        • R$ 20,00 para outros valores
                    </small>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
