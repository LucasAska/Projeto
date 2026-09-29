<?php
$tituloPagina = 'Pedidos — Milka Mics';
require_once __DIR__ . '/_header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pedidoId = (int)($_POST['pedido_id'] ?? 0);
    $status   = $_POST['status'] ?? 'pendente';
    $validos  = ['pendente', 'pago', 'enviado', 'cancelado'];

    if ($pedidoId && in_array($status, $validos, true)) {
        $pdo->prepare('UPDATE pedidos SET status = ? WHERE id = ?')->execute([$status, $pedidoId]);
        set_flash('sucesso', "Status do pedido #{$pedidoId} atualizado.");
    }
    redirecionar('admin/pedidos.php');
}

$pedidos = $pdo->query(
    'SELECT p.*, u.nome AS cliente, u.email, u.celular, u.endereco
     FROM pedidos p JOIN usuarios u ON u.id = p.usuario_id
     ORDER BY p.criado_em DESC'
)->fetchAll();
?>

<div class="titulo-secao"><h2>Pedidos</h2></div>

<?php if (!$pedidos): ?>
    <div class="alerta alerta-info">Nenhum pedido registrado.</div>
<?php else: ?>
    <table class="tabela">
        <thead>
            <tr><th>#</th><th>Cliente</th><th>Contato</th><th>Endereço</th>
                <th>Data</th><th>Total</th><th>Status</th></tr>
        </thead>
        <tbody>
            <?php foreach ($pedidos as $p): ?>
                <tr>
                    <td><?= (int)$p['id'] ?></td>
                    <td><?= e($p['cliente']) ?></td>
                    <td style="font-size:.82rem">
                        <?= e($p['email']) ?><br>
                        <?= e(formatar_celular($p['celular'] ?? '')) ?>
                    </td>
                    <td style="max-width:220px;font-size:.82rem;color:var(--texto-suave)">
                        <?= e($p['endereco'] ?: '—') ?>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($p['criado_em'])) ?></td>
                    <td><strong><?= moeda($p['total']) ?></strong></td>
                    <td>
                        <form method="post" style="display:flex;gap:8px">
                            <input type="hidden" name="pedido_id" value="<?= (int)$p['id'] ?>">
                            <select name="status" style="padding:6px 10px;border-radius:8px;border:1px solid #DDD8CB">
                                <?php foreach (['pendente','pago','enviado','cancelado'] as $s): ?>
                                    <option value="<?= $s ?>" <?= $p['status'] === $s ? 'selected' : '' ?>>
                                        <?= ucfirst($s) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button class="btn-secundario" style="padding:6px 14px;font-size:.8rem" type="submit">Salvar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once __DIR__ . '/_footer.php'; ?>