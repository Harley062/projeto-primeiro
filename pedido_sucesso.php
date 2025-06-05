<?php
$page_title = "Pedido Confirmado";
require_once 'config/database.php';
require_once 'classes/Pedido.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$pedido = new Pedido($db);
$pedido_data = $pedido->buscarPorId($_GET['id']);

if (!$pedido_data) {
    header('Location: index.php');
    exit;
}

include 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body text-center">
                <div class="mb-4">
                    <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                </div>
                
                <h2 class="text-success mb-3">Pedido Confirmado!</h2>
                
                <p class="lead">Seu pedido foi processado com sucesso.</p>
                
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle"></i> Detalhes do Pedido</h5>
                    <p><strong>Número do Pedido:</strong> #<?php echo $pedido_data['id']; ?></p>
                    <p><strong>Total:</strong> R$ <?php echo number_format($pedido_data['total'], 2, ',', '.'); ?></p>
                    <p><strong>Status:</strong> <?php echo ucfirst($pedido_data['status']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($pedido_data['cliente_email']); ?></p>
                </div>
                
                <div class="alert alert-success">
                    <i class="fas fa-envelope"></i> 
                    Um email de confirmação foi enviado para <strong><?php echo htmlspecialchars($pedido_data['cliente_email']); ?></strong>
                </div>
                
                <div class="d-grid gap-2 d-md-block">
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-home"></i> Voltar à Loja
                    </a>
                    <a href="pedidos.php" class="btn btn-outline-primary">
                        <i class="fas fa-list"></i> Ver Todos os Pedidos
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
