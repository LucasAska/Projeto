<?php
require_once __DIR__ . '/config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'adicionar') {
        $pid = (int)($_POST['produto_id'] ?? 0);
        $qtd = max(1, (int)($_POST['quantidade'] ?? 1));

        $st = $pdo->prepare('SELECT id, nome, estoque FROM produtos WHERE id = ? AND ativo = 1');
        $st->execute([$pid]);
        $prod = $st->fetch();

        if (!$prod) {
            set_flash('erro', 'Produto indisponível.');
        } else {
            $atual = carrinho()[$pid] ?? 0;
            $novo  = min($atual + $qtd, (int)$prod['estoque']);
            if ($novo <= 0) {
                set_flash('erro', 'Produto sem estoque.');
            } else {
                $_SESSION['carrinho'][$pid] = $novo;
                set_flash('sucesso', '"' . $prod['nome'] . '" foi adicionado ao carrinho!');
            }
        }
        redirecionar('carrinho.php');
    }

    if ($acao === 'atualizar') {
        foreach (($_POST['quantidades'] ?? []) as $pid => $qtd) {
            $pid = (int)$pid; $qtd = (int)$qtd;
            if ($qtd <= 0) unset($_SESSION['carrinho'][$pid]);
            else $_SESSION['carrinho'][$pid] = $qtd;
        }
        set_flash('sucesso', 'Carrinho atualizado.');
        redirecionar('carrinho.php');
    }

    if ($acao === 'limpar') {
        carrinho_limpar();
        set_flash('info', 'Carrinho esvaziado.');
        redirecionar('carrinho.php');
    }
}

if (($_GET['acao'] ?? '') === 'remover') {
    $pid = (int)($_GET['id'] ?? 0);
    unset($_SESSION['carrinho'][$pid]);
    set_flash('info', 'Item removido do carrinho.');
    redirecionar('carrinho.php');
}

$itens = [];
$total = 0.0;
$ids = array_keys(carrinho());

if ($ids) {
    $marcadores = implode(',', array_fill(0, count($ids), '?'));
    $st = $pdo->prepare("SELECT * FROM produtos WHERE id IN ($marcadores)");
    $st->execute($ids);
    foreach ($st->fetchAll() as $p) {
        $qtd = (int)carrinho()[$p['id']];
        $sub = $qtd * (float)$p['preco'];
        $total += $sub;
        $itens[] = ['produto' => $p, 'qtd' => $qtd, 'subtotal' => $sub];
    }
}

$tituloPagina = 'Carrinho — Milka Mics';
require_once __DIR__ . '/includes/header.php';
?>

<div class="titulo-secao"><h2>Meu carrinho</h2></div>

<?php if (!$itens): ?>
    <div class="alerta alerta-info">Seu carrinho está vazio.</div>
    <a class="btn-principal" href="<?= url('produtos.php') ?>">Continuar comprando</a>
<?php else: ?>
<form method="post" action="<?= url('carrinho.php') ?>">
    <input type="hidden" name="acao" value="atualizar">
    <table class="tabela">
        <thead>
            <tr>
                <th>Produto</th><th>Preço</th>
                <th style="width:120px">Qtd.</th><th>Subtotal</th><th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($itens as $item): $p = $item['produto']; ?>
                <tr>
                    <td><a href="<?= url('produto.php?id=' . $p['id']) ?>">
                        <strong><?= e($p['nome']) ?></strong></a></td>
                    <td><?= moeda($p['preco']) ?></td>
                    <td>
                        <input type="number" name="quantidades[<?= (int)$p['id'] ?>]"
                               value="<?= $item['qtd'] ?>" min="0" max="<?= (int)$p['estoque'] ?>"
                               style="width:80px;padding:7px;border:1px solid #DDD8CB;border-radius:8px;text-align:center">
                    </td>
                    <td><strong><?= moeda($item['subtotal']) ?></strong></td>
                    <td>
                        <a class="btn-perigo" data-confirmar="Remover este item?"
                           href="<?= url('carrinho.php?acao=remover&id=' . $p['id']) ?>">Remover</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="display:flex;gap:12px;margin-top:22px;flex-wrap:wrap">
        <button class="btn-secundario" type="submit">Atualizar carrinho</button>
        <a class="btn-secundario" href="<?= url('produtos.php') ?>">Continuar comprando</a>
    </div>
</form>

<div class="resumo">
    <div class="resumo__linha"><span>Itens</span><span><?= carrinho_qtd_total() ?></span></div>
    <div class="resumo__linha"><span>Frete</span><span>Calculado na finalização</span></div>
    <div class="resumo__total"><span>Total</span><span><?= moeda($total) ?></span></div>
    <div style="display:flex;gap:12px;margin-top:24px;flex-wrap:wrap">
        <a class="btn-principal" href="<?= url('finalizar.php') ?>">Finalizar compra</a>
        <a class="btn-secundario" data-confirmar="Esvaziar todo o carrinho?"
           href="<?= url('carrinho.php?acao=limpar') ?>">Esvaziar</a>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>