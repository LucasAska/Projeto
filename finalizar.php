<?php
require_once __DIR__ . '/config/config.php';
exigir_login();

$ids = array_keys(carrinho());
if (!$ids) {
    set_flash('erro', 'Seu carrinho está vazio.');
    redirecionar('produtos.php');
}

$marcadores = implode(',', array_fill(0, count($ids), '?'));
$st = $pdo->prepare("SELECT * FROM produtos WHERE id IN ($marcadores) AND ativo = 1");
$st->execute($ids);
$produtos = [];
foreach ($st->fetchAll() as $p) $produtos[$p['id']] = $p;

$itens = [];
$total = 0.0;
foreach (carrinho() as $pid => $qtd) {
    if (!isset($produtos[$pid])) continue;
    $p = $produtos[$pid];
    $sub = $qtd * (float)$p['preco'];
    $total += $sub;
    $itens[] = ['produto' => $p, 'qtd' => $qtd, 'subtotal' => $sub];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$itens) {
        set_flash('erro', 'Não há itens válidos no carrinho.');
        redirecionar('carrinho.php');
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('INSERT INTO pedidos (usuario_id, total, status) VALUES (?, ?, "pendente")');
        $stmt->execute([$_SESSION['usuario_id'], $total]);
        $pedidoId = (int)$pdo->lastInsertId();

        $insItem = $pdo->prepare('INSERT INTO pedido_itens (pedido_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)');
        $baixaEstoque = $pdo->prepare('UPDATE produtos SET estoque = estoque - ? WHERE id = ? AND estoque >= ?');

        foreach ($itens as $item) {
            $insItem->execute([$pedidoId, $item['produto']['id'], $item['qtd'], $item['produto']['preco']]);
            $baixaEstoque->execute([$item['qtd'], $item['produto']['id'], $item['qtd']]);
            if ($baixaEstoque->rowCount() === 0) {
                throw new Exception('Estoque insuficiente para ' . $item['produto']['nome']);
            }
        }

        $pdo->commit();
        carrinho_limpar();
        set_flash('sucesso', "Pedido #{$pedidoId} realizado com sucesso! Obrigada pela compra 💚");
        redirecionar('pedidos.php');
    } catch (Exception $ex) {
        $pdo->rollBack();
        set_flash('erro', 'Não foi possível finalizar: ' . $ex->getMessage());
        redirecionar('carrinho.php');
    }
}

$tituloPagina = 'Finalizar compra — Milka Mics';
require_once __DIR__ . '/includes/header.php';
$u = usuario_atual();
?>

<div class="titulo-secao"><h2>Finalizar compra</h2></div>

<div style="display:grid;grid-template-columns:1.4fr 1fr;gap:30px" class="checkout-grade">
    <div class="resumo">
        <h3 style="margin-bottom:16px">Dados do pedido</h3>
        <div class="campo"><label>Nome</label><input type="text" value="<?= e($u['nome']) ?>" disabled></div>
        <div class="campo"><label>E-mail</label><input type="text" value="<?= e($u['email']) ?>" disabled></div>
        <div class="campo"><label>Celular</label><input type="text" value="<?= e(formatar_celular($u['celular'] ?? '')) ?>" disabled></div>
        <div class="campo"><label>Endereço de entrega</label><input type="text" value="<?= e($u['endereco'] ?? '') ?>" disabled></div>
        <div class="campo"><label>Observações (opcional)</label><textarea placeholder="Ex.: embalagem para presente..."></textarea></div>
        <p style="font-size:.85rem;color:var(--texto-suave)">O pagamento é combinado por e-mail após a confirmação do pedido.</p>
    </div>

    <div class="resumo">
        <h3 style="margin-bottom:16px">Resumo</h3>
        <?php foreach ($itens as $item): ?>
            <div class="resumo__linha">
                <span><?= e($item['produto']['nome']) ?> × <?= $item['qtd'] ?></span>
                <span><?= moeda($item['subtotal']) ?></span>
            </div>
        <?php endforeach; ?>
        <div class="resumo__total"><span>Total</span><span><?= moeda($total) ?></span></div>
        <form method="post" style="margin-top:22px">
            <button class="btn-principal" type="submit" style="width:100%">Confirmar pedido</button>
        </form>
        <a class="btn-secundario" href="<?= url('carrinho.php') ?>" style="display:block;text-align:center;margin-top:12px">Voltar ao carrinho</a>
    </div>
</div>
<style>@media(max-width:900px){.checkout-grade{grid-template-columns:1fr!important}}</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>