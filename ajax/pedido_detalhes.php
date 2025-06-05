<?php
require_once '../config/database.php';
require_once '../classes/Pedido.php';

if (!isset($_GET['id'])) {
    echo '<div class="alert alert-danger">ID do pedido não fornecido.</div>';
    exit;
}

$database = new Database();
$db = $database->getConnection();

$pedido = new Pedido($db);
$pedido_data = $pedido->buscarPorId($_GET['id']);

if (!$pedido_data) {
    echo '<div class="alert alert-danger">Pedido não encontrado.</div>';
    exit;
}

$query = "SELECT pi.*, p.nome as produto_nome, pv.nome as variacao_nome
          FROM pedido_itens pi
          JOIN produtos p ON pi.produto_id = p.id
          LEFT JOIN produto_variacoes pv ON pi.variacao_id = pv.id
          WHERE pi.pedido_id = ?";

$stmt = $db->prepare($query);
$stmt->bindParam(1, $_GET['id']);
$stmt->execute();
$itens = $stmt->fetchAll();
?>

<div class="row">
    <div class="col-md-6">
        <h6><i class="fas fa-user"></i> Dados do Cliente</h6>
        <p><strong>Nome:</strong> <?php echo htmlspecialchars($pedido_data['cliente_nome']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($pedido_data['cliente_email']); ?></p>
        <p><strong>Telefone:</strong> <?php echo htmlspecialchars($pedido_data['cliente_telefone']); ?></p>
        
        <h6 class="mt-3"><i class="fas fa-map-marker-alt"></i> Endereço</h6>
        <p>
            <?php echo htmlspecialchars($pedido_data['endereco_logradouro']); ?>, 
            <?php echo htmlspecialchars($pedido_data['endereco_numero']); ?>
            <?php if ($pedido_data['endereco_complemento']): ?>
                - <?php echo htmlspecialchars($pedido_data['endereco_complemento']); ?>
            <?php endif; ?><br>
            <?php echo htmlspecialchars($pedido_data['endereco_bairro']); ?><br>
            <?php echo htmlspecialchars($pedido_data['endereco_cidade']); ?> - <?php echo htmlspecialchars($pedido_data['endereco_uf']); ?><br>
            CEP: <?php echo htmlspecialchars($pedido_data['endereco_cep']); ?>
        </p>
    </div>
    
    <div class="col-md-6">
        <h6><i class="fas fa-info-circle"></i> Informações do Pedido</h6>
        <p><strong>Número:</strong> #<?php echo $pedido_data['id']; ?></p>
        <p><strong>Status:</strong> 
            <?php
            $status_class = '';
            switch ($pedido_data['status']) {
                case 'pendente': $status_class = 'warning'; break;
                case 'confirmado': $status_class = 'info'; break;
                case 'enviado': $status_class = 'primary'; break;
                case 'entregue': $status_class = 'success'; break;
                case 'cancelado': $status_class = 'danger'; break;
            }
            ?>
            <span class="badge bg-<?php echo $status_class; ?>">
                <?php echo ucfirst($pedido_data['status']); ?>
            </span>
        </p>
        <p><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($pedido_data['created_at'])); ?></p>
        <?php if ($pedido_data['cupom_codigo']): ?>
            <p><strong>Cupom:</strong> <?php echo htmlspecialchars($pedido_data['cupom_codigo']); ?></p>
        <?php endif; ?>
    </div>
</div>

<hr>

<h6><i class="fas fa-list"></i> Itens do Pedido</h6>
<div class="table-responsive">
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Variação</th>
                <th>Quantidade</th>
                <th>Preço Unit.</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($itens as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['produto_nome']); ?></td>
                    <td><?php echo $item['variacao_nome'] ? htmlspecialchars($item['variacao_nome']) : '-'; ?></td>
                    <td><?php echo $item['quantidade']; ?></td>
                    <td>R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?></td>
                    <td>R$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<hr>

<div class="row">
    <div class="col-md-6 offset-md-6">
        <div class="d-flex justify-content-between mb-2">
            <span>Subtotal:</span>
            <span>R$ <?php echo number_format($pedido_data['subtotal'], 2, ',', '.'); ?></span>
        </div>
        
        <?php if ($pedido_data['desconto'] > 0): ?>
            <div class="d-flex justify-content-between mb-2 text-success">
                <span>Desconto:</span>
                <span>-R$ <?php echo number_format($pedido_data['desconto'], 2, ',', '.'); ?></span>
            </div>
        <?php endif; ?>
        
        <div class="d-flex justify-content-between mb-2">
            <span>Frete:</span>
            <span>
                <?php if ($pedido_data['frete'] == 0): ?>
                    <span class="text-success">Grátis</span>
                <?php else: ?>
                    R$ <?php echo number_format($pedido_data['frete'], 2, ',', '.'); ?>
                <?php endif; ?>
            </span>
        </div>
        
        <hr>
        
        <div class="d-flex justify-content-between">
            <strong>Total:</strong>
            <strong class="text-success">R$ <?php echo number_format($pedido_data['total'], 2, ',', '.'); ?></strong>
        </div>
    </div>
</div>
