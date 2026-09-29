<?php
require_once __DIR__ . '/config/config.php';
exigir_login();

$st = $pdo->prepare('SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY criado_em DESC');
$st->execute([$_SESSION['usuario_id']]);
$pedidos = $st->fetchAll();

$itensPorPedido = [];
if ($pedidos) {
    $ids = array_column($pedidos, 'id');
    $marcadores = implode(',', array_fill(0, count($ids), '?'));
    $st = $pdo->prepare(
        "SELECT pi.*, p.nome FROM pedido_itens pi
         JOIN produtos p ON p.id = pi.produto_id
         WHERE pi.pedido_id IN ($marcadores)"
    );
    $st->execute($ids);
    foreach ($st->fetchAll() as $row) $itensPorPedido[$row['pedido_id']][] = $row;
}

$tituloPagina = 'Meus pedidos — Milka Mics';
require_once __DIR__ . '/includes/header.php';
?>

<div class="titulo-secao"><h2>Meus pedidos</h2></div>

<?php if (!$pedidos): ?>
    <div class="alerta alerta-info">Você ainda não fez nenhum pedido.</div>
    <a class="btn-principal" href="<?= url('produtos.php') ?>">Ver produtos</a>
<?php else: ?>
    <?php foreach ($pedidos as $ped): ?>
        <div class="resumo" style="margin-bottom:22px">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
                <h3>Pedido #<?= (int)$ped['id'] ?></h3>
                <span class="chip ativo"><?= e(ucfirst($ped['status'])) ?></span>
            </div>
            <p style="color:var(--texto-suave);font-size:.85rem;margin:6px 0 16px">
                Realizado em <?= date('d/m/Y \à\s H:i', strtotime($ped['criado_em'])) ?>
            </p>
            <table class="tabela">
                <thead><tr><th>Produto</th><th>Qtd.</th><th>Valor unit.</th><th>Subtotal</th></tr></thead>
                <tbody>
                    <?php foreach ($itensPorPedido[$ped['id']] ?? [] as $item): ?>
                        <tr>
                            <td><?= e($item['nome']) ?></td>
                            <td><?= (int)$item['quantidade'] ?></td>
                            <td><?= moeda($item['preco_unitario']) ?></td>
                            <td><?= moeda($item['preco_unitario'] * $item['quantidade']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="resumo__total"><span>Total</span><span><?= moeda($ped['total']) ?></span></div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>