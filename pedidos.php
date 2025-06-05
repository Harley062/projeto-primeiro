<?php
$page_title = "Pedidos";
require_once 'config/database.php';
require_once 'classes/Pedido.php';

$database = new Database();
$db = $database->getConnection();

$pedido = new Pedido($db);
$pedidos = $pedido->listar();

include 'includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <h1><i class="fas fa-shopping-cart"></i> Pedidos</h1>
    </div>
</div>

<?php if (empty($pedidos)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> Nenhum pedido encontrado.
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th>Email</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos as $ped): ?>
                            <tr>
                                <td>#<?php echo $ped['id']; ?></td>
                                <td><?php echo htmlspecialchars($ped['cliente_nome']); ?></td>
                                <td><?php echo htmlspecialchars($ped['cliente_email']); ?></td>
                                <td>R$ <?php echo number_format($ped['total'], 2, ',', '.'); ?></td>
                                <td>
                                    <?php
                                    $status_class = '';
                                    switch ($ped['status']) {
                                        case 'pendente':
                                            $status_class = 'warning';
                                            break;
                                        case 'confirmado':
                                            $status_class = 'info';
                                            break;
                                        case 'enviado':
                                            $status_class = 'primary';
                                            break;
                                        case 'entregue':
                                            $status_class = 'success';
                                            break;
                                        case 'cancelado':
                                            $status_class = 'danger';
                                            break;
                                    }
                                    ?>
                                    <span class="badge bg-<?php echo $status_class; ?>">
                                        <?php echo ucfirst($ped['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($ped['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" 
                                            onclick="verDetalhes(<?php echo $ped['id']; ?>)">
                                        <i class="fas fa-eye"></i> Ver
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Modal Detalhes do Pedido -->
<div class="modal fade" id="modalDetalhes" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalhes do Pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detalhesContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function verDetalhes(pedidoId) {
    const modal = new bootstrap.Modal(document.getElementById('modalDetalhes'));
    modal.show();
    
    fetch('ajax/pedido_detalhes.php?id=' + pedidoId)
        .then(response => response.text())
        .then(html => {
            document.getElementById('detalhesContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('detalhesContent').innerHTML = 
                '<div class="alert alert-danger">Erro ao carregar detalhes do pedido.</div>';
        });
}
</script>

<?php include 'includes/footer.php'; ?>
