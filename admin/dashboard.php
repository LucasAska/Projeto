<?php
$tituloPagina = 'Dashboard — Milka Mics';
require_once __DIR__ . '/_header.php';

$totalProdutos = (int)$pdo->query('SELECT COUNT(*) FROM produtos WHERE ativo = 1')->fetchColumn();
$totalPedidos  = (int)$pdo->query('SELECT COUNT(*) FROM pedidos')->fetchColumn();
$totalClientes = (int)$pdo->query('SELECT COUNT(*) FROM usuarios WHERE tipo = "cliente"')->fetchColumn();
$totalUsuarios = (int)$pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
$faturamento   = (float)$pdo->query('SELECT COALESCE(SUM(total),0) FROM pedidos WHERE status <> "cancelado"')->fetchColumn();

$ultimosPedidos = $pdo->query(
    'SELECT p.*, u.nome AS cliente
     FROM pedidos p JOIN usuarios u ON u.id = p.usuario_id
     ORDER BY p.criado_em DESC LIMIT 8'
)->fetchAll();
?>

<div class="titulo-secao"><h2>Visão geral</h2></div>

<div class="grade-stats">
    <div class="stat"><span>Produtos ativos</span><strong><?= $totalProdutos ?></strong></div>
    <div class="stat"><span>Pedidos</span><strong><?= $totalPedidos ?></strong></div>
    <div class="stat"><span>Clientes</span><strong><?= $totalClientes ?></strong></div>
    <div class="stat"><span>Usuários totais</span><strong><?= $totalUsuarios ?></strong></div>
    <div class="stat"><span>Faturamento</span><strong style="font-size:1.5rem"><?= moeda($faturamento) ?></strong></div>
</div>

<div class="titulo-secao"><h2>Últimos pedidos</h2></div>

<?php if (!$ultimosPedidos): ?>
    <div class="alerta alerta-info">Nenhum pedido registrado ainda.</div>
<?php else: ?>
    <table class="tabela">
        <thead><tr><th>#</th><th>Cliente</th><th>Data</th><th>Status</th><th>Total</th></tr></thead>
        <tbody>
            <?php foreach ($ultimosPedidos as $p): ?>
                <tr>
                    <td><?= (int)$p['id'] ?></td>
                    <td><?= e($p['cliente']) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($p['criado_em'])) ?></td>
                    <td><?= e(ucfirst($p['status'])) ?></td>
                    <td><strong><?= moeda($p['total']) ?></strong></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/_footer.php'; ?>