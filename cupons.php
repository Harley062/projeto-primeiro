<?php
$page_title = "Gerenciar Cupons";
require_once 'config/database.php';
require_once 'classes/Cupom.php';

$database = new Database();
$db = $database->getConnection();

$cupom = new Cupom($db);

$message = '';
$message_type = '';

if ($_POST) {
    if (isset($_POST['action']) && $_POST['action'] == 'create') {
        $cupom->codigo = strtoupper(trim($_POST['codigo']));
        $cupom->tipo = $_POST['tipo'];
        $cupom->valor = $_POST['valor'];
        $cupom->valor_minimo = $_POST['valor_minimo'];
        $cupom->data_inicio = $_POST['data_inicio'];
        $cupom->data_fim = $_POST['data_fim'];
        $cupom->limite_uso = !empty($_POST['limite_uso']) ? $_POST['limite_uso'] : null;
        
        if ($cupom->criar()) {
            $message = 'Cupom criado com sucesso!';
            $message_type = 'success';
        } else {
            $message = 'Erro ao criar cupom! Verifique se o código já não existe.';
            $message_type = 'danger';
        }
    }
}

$cupons = $cupom->listar();

include 'includes/header.php';
?>

<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-ticket-alt"></i> Gerenciar Cupons</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCupom">
                <i class="fas fa-plus"></i> Novo Cupom
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

<?php if (empty($cupons)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> Nenhum cupom cadastrado ainda.
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Tipo</th>
                            <th>Valor</th>
                            <th>Valor Mínimo</th>
                            <th>Período</th>
                            <th>Limite de Uso</th>
                            <th>Usado</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cupons as $cup): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($cup['codigo']); ?></strong>
                                </td>
                                <td>
                                    <?php if ($cup['tipo'] == 'percentual'): ?>
                                        <span class="badge bg-info">Percentual</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Valor Fixo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($cup['tipo'] == 'percentual'): ?>
                                        <?php echo number_format($cup['valor'], 1); ?>%
                                    <?php else: ?>
                                        R$ <?php echo number_format($cup['valor'], 2, ',', '.'); ?>
                                    <?php endif; ?>
                                </td>
                                <td>R$ <?php echo number_format($cup['valor_minimo'], 2, ',', '.'); ?></td>
                                <td>
                                    <?php echo date('d/m/Y', strtotime($cup['data_inicio'])); ?> até 
                                    <?php echo date('d/m/Y', strtotime($cup['data_fim'])); ?>
                                </td>
                                <td>
                                    <?php echo $cup['limite_uso'] ? $cup['limite_uso'] : 'Ilimitado'; ?>
                                </td>
                                <td><?php echo $cup['usado']; ?></td>
                                <td>
                                    <?php
                                    $hoje = date('Y-m-d');
                                    $ativo = $cup['ativo'] && 
                                             $cup['data_inicio'] <= $hoje && 
                                             $cup['data_fim'] >= $hoje &&
                                             ($cup['limite_uso'] === null || $cup['usado'] < $cup['limite_uso']);
                                    ?>
                                    <?php if ($ativo): ?>
                                        <span class="badge bg-success">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inativo</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Modal Cupom -->
<div class="modal fade" id="modalCupom" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Novo Cupom</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="mb-3">
                        <label class="form-label">Código *</label>
                        <input type="text" class="form-control" name="codigo" required 
                               style="text-transform: uppercase;" maxlength="50">
                        <div class="form-text">Use apenas letras e números, sem espaços.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tipo de Desconto *</label>
                        <select class="form-select" name="tipo" id="tipoCupom" required onchange="atualizarTipoCupom()">
                            <option value="">Selecione...</option>
                            <option value="percentual">Percentual (%)</option>
                            <option value="valor_fixo">Valor Fixo (R$)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" id="labelValor">Valor *</label>
                        <input type="number" class="form-control" name="valor" id="valorCupom" 
                               step="0.01" min="0" required>
                        <div class="form-text" id="helpValor"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Valor Mínimo do Pedido *</label>
                        <input type="number" class="form-control" name="valor_minimo" 
                               step="0.01" min="0" value="0" required>
                        <div class="form-text">Valor mínimo do subtotal para aplicar o cupom.</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Data de Início *</label>
                            <input type="date" class="form-control" name="data_inicio" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Data de Fim *</label>
                            <input type="date" class="form-control" name="data_fim" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Limite de Uso</label>
                        <input type="number" class="form-control" name="limite_uso" min="1">
                        <div class="form-text">Deixe em branco para uso ilimitado.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Criar Cupom</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function atualizarTipoCupom() {
    const tipo = document.getElementById('tipoCupom').value;
    const labelValor = document.getElementById('labelValor');
    const valorInput = document.getElementById('valorCupom');
    const helpValor = document.getElementById('helpValor');
    
    if (tipo === 'percentual') {
        labelValor.textContent = 'Percentual de Desconto *';
        valorInput.max = '100';
        helpValor.textContent = 'Digite o percentual de desconto (ex: 10 para 10%)';
    } else if (tipo === 'valor_fixo') {
        labelValor.textContent = 'Valor do Desconto *';
        valorInput.removeAttribute('max');
        helpValor.textContent = 'Digite o valor fixo de desconto em reais';
    } else {
        labelValor.textContent = 'Valor *';
        valorInput.removeAttribute('max');
        helpValor.textContent = '';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const hoje = new Date().toISOString().split('T')[0];
    document.querySelector('input[name="data_inicio"]').min = hoje;
    document.querySelector('input[name="data_fim"]').min = hoje;
    
    document.querySelector('input[name="data_inicio"]').value = hoje;
    
    document.querySelector('input[name="data_inicio"]').addEventListener('change', function() {
        document.querySelector('input[name="data_fim"]').min = this.value;
    });
});

document.getElementById('modalCupom').addEventListener('hidden.bs.modal', function () {
    this.querySelector('form').reset();
    atualizarTipoCupom();
});
</script>

<?php include 'includes/footer.php'; ?>
