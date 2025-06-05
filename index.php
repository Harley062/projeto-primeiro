<?php
$page_title = "Início";
require_once 'config/database.php';
require_once 'classes/Produto.php';
require_once 'classes/Carrinho.php';

$database = new Database();
$db = $database->getConnection();

$produto = new Produto($db);
$carrinho = new Carrinho();

$produtos = $produto->listar();

include 'includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-store"></i> Loja Virtual</h1>
            <a href="produtos.php" class="btn btn-primary">
                <i class="fas fa-cog"></i> Gerenciar Produtos
            </a>
        </div>
    </div>
</div>

<?php if (empty($produtos)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> Nenhum produto cadastrado ainda.
        <a href="produtos.php" class="alert-link">Clique aqui para cadastrar produtos</a>.
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($produtos as $prod): ?>
            <div class="col-md-4 mb-4">
                <div class="card product-card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($prod['nome']); ?></h5>
                        <p class="card-text text-muted"><?php echo htmlspecialchars($prod['descricao']); ?></p>
                        <div class="price mb-3">R$ <?php echo number_format($prod['preco'], 2, ',', '.'); ?></div>
                        
                        <?php if ($prod['variacoes']): ?>
                            <div class="mb-3">
                                <label class="form-label">Variação:</label>
                                <select class="form-select variation-select" id="variacao_<?php echo $prod['id']; ?>">
                                    <?php
                                    $variacoes = explode('|', $prod['variacoes']);
                                    $estoques = [];
                                    if ($prod['estoque']) {
                                        foreach (explode('|', $prod['estoque']) as $est) {
                                            $parts = explode(':', $est);
                                            if (count($parts) >= 2) {
                                                $estoques[$parts[0]] = $parts[1];
                                            }
                                        }
                                    }
                                    
                                    foreach ($variacoes as $var):
                                        $var_parts = explode(':', $var);
                                        if (count($var_parts) >= 3):
                                            $var_id = $var_parts[0];
                                            $var_nome = $var_parts[1];
                                            $var_valor = floatval($var_parts[2]);
                                            $estoque_qty = isset($estoques[$var_id]) ? $estoques[$var_id] : 0;
                                            $preco_final = $prod['preco'] + $var_valor;
                                    ?>
                                        <option value="<?php echo $var_id; ?>" 
                                                data-preco="<?php echo $preco_final; ?>"
                                                data-estoque="<?php echo $estoque_qty; ?>"
                                                <?php echo $estoque_qty <= 0 ? 'disabled' : ''; ?>>
                                            <?php echo htmlspecialchars($var_nome); ?>
                                            <?php if ($var_valor > 0): ?>
                                                (+R$ <?php echo number_format($var_valor, 2, ',', '.'); ?>)
                                            <?php endif; ?>
                                            - Estoque: <?php echo $estoque_qty; ?>
                                        </option>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </select>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label">Quantidade:</label>
                            <input type="number" class="form-control" id="quantidade_<?php echo $prod['id']; ?>" 
                                   value="1" min="1" max="10">
                        </div>
                        
                        <div class="stock-info mb-3" id="stock_info_<?php echo $prod['id']; ?>">
                            <i class="fas fa-box"></i> Estoque disponível
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button class="btn btn-success w-100" onclick="adicionarAoCarrinho(<?php echo $prod['id']; ?>)">
                            <i class="fas fa-cart-plus"></i> Comprar
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
function adicionarAoCarrinho(produtoId) {
    const variacaoSelect = document.getElementById('variacao_' + produtoId);
    const quantidadeInput = document.getElementById('quantidade_' + produtoId);
    
    let variacaoId = null;
    let preco = 0;
    let estoque = 0;
    
    if (variacaoSelect) {
        const selectedOption = variacaoSelect.options[variacaoSelect.selectedIndex];
        if (!selectedOption.value) {
            alert('Selecione uma variação!');
            return;
        }
        variacaoId = selectedOption.value;
        preco = parseFloat(selectedOption.dataset.preco);
        estoque = parseInt(selectedOption.dataset.estoque);
    }
    
    const quantidade = parseInt(quantidadeInput.value);
    
    if (quantidade > estoque) {
        alert('Quantidade solicitada maior que o estoque disponível!');
        return;
    }
    
    $.post('ajax/add_to_cart.php', {
        produto_id: produtoId,
        variacao_id: variacaoId,
        quantidade: quantidade
    }, function(response) {
        if (response.success) {
            alert('Produto adicionado ao carrinho!');
            updateCartCount();
        } else {
            alert('Erro: ' + response.message);
        }
    }, 'json');
}

$(document).ready(function() {
    $('[id^="variacao_"]').change(function() {
        const selectedOption = this.options[this.selectedIndex];
        const produtoId = this.id.replace('variacao_', '');
        const preco = parseFloat(selectedOption.dataset.preco);
        const estoque = parseInt(selectedOption.dataset.estoque);
        
        const card = $(this).closest('.card');
        card.find('.price').text('R$ ' + preco.toLocaleString('pt-BR', {minimumFractionDigits: 2}));
        
        const stockInfo = document.getElementById('stock_info_' + produtoId);
        if (estoque <= 0) {
            stockInfo.innerHTML = '<i class="fas fa-exclamation-triangle text-danger"></i> Sem estoque';
            stockInfo.className = 'stock-info mb-3 text-danger';
        } else if (estoque <= 5) {
            stockInfo.innerHTML = '<i class="fas fa-exclamation-triangle text-warning"></i> Estoque baixo: ' + estoque;
            stockInfo.className = 'stock-info mb-3 text-warning';
        } else {
            stockInfo.innerHTML = '<i class="fas fa-box text-success"></i> Estoque: ' + estoque;
            stockInfo.className = 'stock-info mb-3 text-success';
        }
        
        const quantidadeInput = document.getElementById('quantidade_' + produtoId);
        quantidadeInput.max = estoque;
        if (parseInt(quantidadeInput.value) > estoque) {
            quantidadeInput.value = estoque;
        }
    });
    
    $('[id^="variacao_"]').trigger('change');
});
</script>

<?php include 'includes/footer.php'; ?>
