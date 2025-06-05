<?php
$page_title = "Gerenciar Produtos";
require_once 'config/database.php';
require_once 'classes/Produto.php';

$database = new Database();
$db = $database->getConnection();

$produto = new Produto($db);

$message = '';
$message_type = '';

if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $produto->nome = $_POST['nome'];
                $produto->preco = $_POST['preco'];
                $produto->descricao = $_POST['descricao'];
                
                $produto_id = $produto->criar();
                
                if ($produto_id) {
                    if (!empty($_POST['variacoes'])) {
                        $variacoes = explode(',', $_POST['variacoes']);
                        foreach ($variacoes as $var) {
                            $var = trim($var);
                            if (!empty($var)) {
                                $parts = explode(':', $var);
                                $nome_var = trim($parts[0]);
                                $valor_adicional = isset($parts[1]) ? floatval($parts[1]) : 0;
                                
                                $variacao_id = $produto->adicionarVariacao($produto_id, $nome_var, $valor_adicional);
                                
                                if ($variacao_id && isset($_POST['estoque_inicial'])) {
                                    $produto->atualizarEstoque($produto_id, $variacao_id, $_POST['estoque_inicial']);
                                }
                            }
                        }
                    } else {
                        if (isset($_POST['estoque_inicial'])) {
                            $produto->atualizarEstoque($produto_id, null, $_POST['estoque_inicial']);
                        }
                    }
                    
                    $message = 'Produto criado com sucesso!';
                    $message_type = 'success';
                } else {
                    $message = 'Erro ao criar produto!';
                    $message_type = 'danger';
                }
                break;
                
            case 'update':
                $produto->id = $_POST['id'];
                $produto->nome = $_POST['nome'];
                $produto->preco = $_POST['preco'];
                $produto->descricao = $_POST['descricao'];
                
                if ($produto->atualizar()) {
                    $message = 'Produto atualizado com sucesso!';
                    $message_type = 'success';
                } else {
                    $message = 'Erro ao atualizar produto!';
                    $message_type = 'danger';
                }
                break;
                
            case 'update_stock':
                $produto_id = $_POST['produto_id'];
                $variacao_id = !empty($_POST['variacao_id']) ? $_POST['variacao_id'] : null;
                $quantidade = $_POST['quantidade'];
                
                if ($produto->atualizarEstoque($produto_id, $variacao_id, $quantidade)) {
                    $message = 'Estoque atualizado com sucesso!';
                    $message_type = 'success';
                } else {
                    $message = 'Erro ao atualizar estoque!';
                    $message_type = 'danger';
                }
                break;
        }
    }
}

$produtos = $produto->listar();

include 'includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-box"></i> Gerenciar Produtos</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProduto">
                <i class="fas fa-plus"></i> Novo Produto
            </button>
        </div>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
        <?php echo $message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <?php foreach ($produtos as $prod): ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($prod['nome']); ?></h5>
                    <p class="card-text text-muted"><?php echo htmlspecialchars($prod['descricao']); ?></p>
                    <div class="price mb-3">R$ <?php echo number_format($prod['preco'], 2, ',', '.'); ?></div>
                    
                    <?php if ($prod['variacoes']): ?>
                        <h6>Variações e Estoque:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Variação</th>
                                        <th>Preço</th>
                                        <th>Estoque</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
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
                                        <tr>
                                            <td><?php echo htmlspecialchars($var_nome); ?></td>
                                            <td>R$ <?php echo number_format($preco_final, 2, ',', '.'); ?></td>
                                            <td>
                                                <span class="badge <?php echo $estoque_qty <= 5 ? 'bg-warning' : 'bg-success'; ?>">
                                                    <?php echo $estoque_qty; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" 
                                                        onclick="editarEstoque(<?php echo $prod['id']; ?>, <?php echo $var_id; ?>, <?php echo $estoque_qty; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="card-footer">
                    <button class="btn btn-outline-primary btn-sm" 
                            onclick="editarProduto(<?php echo htmlspecialchars(json_encode($prod)); ?>)">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Modal Produto -->
<div class="modal fade" id="modalProduto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalProdutoTitle">Novo Produto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" id="produtoAction" value="create">
                    <input type="hidden" name="id" id="produtoId">
                    
                    <div class="mb-3">
                        <label class="form-label">Nome *</label>
                        <input type="text" class="form-control" name="nome" id="produtoNome" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Preço Base *</label>
                        <input type="number" class="form-control" name="preco" id="produtoPreco" 
                               step="0.01" min="0" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Descrição</label>
                        <textarea class="form-control" name="descricao" id="produtoDescricao" rows="3"></textarea>
                    </div>
                    
                    <div class="mb-3" id="variacoesGroup">
                        <label class="form-label">Variações</label>
                        <input type="text" class="form-control" name="variacoes" id="produtoVariacoes" 
                               placeholder="Ex: P:0, M:0, G:5.00, GG:10.00">
                        <div class="form-text">
                            Formato: Nome:ValorAdicional, separados por vírgula. 
                            Deixe em branco se não houver variações.
                        </div>
                    </div>
                    
                    <div class="mb-3" id="estoqueGroup">
                        <label class="form-label">Estoque Inicial</label>
                        <input type="number" class="form-control" name="estoque_inicial" id="produtoEstoque" 
                               min="0" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Estoque -->
<div class="modal fade" id="modalEstoque" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Atualizar Estoque</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_stock">
                    <input type="hidden" name="produto_id" id="estoqueProdutoId">
                    <input type="hidden" name="variacao_id" id="estoqueVariacaoId">
                    
                    <div class="mb-3">
                        <label class="form-label">Quantidade</label>
                        <input type="number" class="form-control" name="quantidade" id="estoqueQuantidade" 
                               min="0" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Atualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editarProduto(produto) {
    document.getElementById('modalProdutoTitle').textContent = 'Editar Produto';
    document.getElementById('produtoAction').value = 'update';
    document.getElementById('produtoId').value = produto.id;
    document.getElementById('produtoNome').value = produto.nome;
    document.getElementById('produtoPreco').value = produto.preco;
    document.getElementById('produtoDescricao').value = produto.descricao;
    
    document.getElementById('variacoesGroup').style.display = 'none';
    document.getElementById('estoqueGroup').style.display = 'none';
    
    new bootstrap.Modal(document.getElementById('modalProduto')).show();
}

function editarEstoque(produtoId, variacaoId, quantidadeAtual) {
    document.getElementById('estoqueProdutoId').value = produtoId;
    document.getElementById('estoqueVariacaoId').value = variacaoId;
    document.getElementById('estoqueQuantidade').value = quantidadeAtual;
    
    new bootstrap.Modal(document.getElementById('modalEstoque')).show();
}

document.getElementById('modalProduto').addEventListener('hidden.bs.modal', function () {
    document.getElementById('modalProdutoTitle').textContent = 'Novo Produto';
    document.getElementById('produtoAction').value = 'create';
    document.getElementById('produtoId').value = '';
    document.getElementById('variacoesGroup').style.display = 'block';
    document.getElementById('estoqueGroup').style.display = 'block';
    this.querySelector('form').reset();
});
</script>

<?php include 'includes/footer.php'; ?>
