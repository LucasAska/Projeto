<?php
require_once __DIR__ . '/config/config.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    "SELECT p.*, c.nome AS categoria, c.slug AS categoria_slug
     FROM produtos p
     JOIN categorias c ON c.id = p.categoria_id
     WHERE p.id = ? AND p.ativo = 1"
);
$stmt->execute([$id]);
$produto = $stmt->fetch();

if (!$produto) {
    set_flash('erro', 'Produto não encontrado.');
    redirecionar('produtos.php');
}

$relacionados = $pdo->prepare(
    "SELECT p.*, c.nome AS categoria FROM produtos p
     JOIN categorias c ON c.id = p.categoria_id
     WHERE p.categoria_id = ? AND p.id <> ? AND p.ativo = 1
     ORDER BY RAND() LIMIT 4"
);
$relacionados->execute([$produto['categoria_id'], $produto['id']]);
$relacionados = $relacionados->fetchAll();

$img = produto_imagem_url($produto['imagem']);
$tituloPagina = $produto['nome'] . ' — Milka Mics';
require_once __DIR__ . '/includes/header.php';
?>

<div class="produto-detalhe">
    <div class="produto-detalhe__img">
        <?php if ($img): ?>
            <img src="<?= e($img) ?>" alt="<?= e($produto['nome']) ?>">
        <?php else: ?>
            <span class="card__placeholder"><?= e(placeholder_inicial($produto['nome'])) ?></span>
        <?php endif; ?>
    </div>

    <div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:8px">
            <?php foreach (categorias_do_produto((int)$produto['id']) as $cat): ?>
                <a class="chip" style="padding:3px 12px;font-size:.78rem"
                   href="<?= url('produtos.php?categoria=' . urlencode($cat['slug'])) ?>">
                    <?= e($cat['nome']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <h1><?= e($produto['nome']) ?></h1>
        <div class="preco"><?= moeda($produto['preco']) ?></div>
        <p class="descricao"><?= nl2br(e($produto['descricao'])) ?></p>

        <?php if ($produto['estoque'] > 0): ?>
            <p style="color:var(--oliva);font-size:.9rem;margin-bottom:18px">
                ✔ Em estoque — <?= (int)$produto['estoque'] ?> unidade(s)
            </p>
            <form method="post" action="<?= url('carrinho.php') ?>">
                <input type="hidden" name="acao" value="adicionar">
                <input type="hidden" name="produto_id" value="<?= (int)$produto['id'] ?>">
                <div class="form-qtd">
                    <label for="qtd" style="font-weight:500">Quantidade:</label>
                    <input type="number" id="qtd" name="quantidade" value="1" min="1"
                           max="<?= (int)$produto['estoque'] ?>">
                </div>
                <button class="btn-principal" type="submit">Adicionar ao carrinho</button>
                <a class="btn-secundario" style="margin-left:10px" href="<?= url('carrinho.php') ?>">Ir ao carrinho</a>
            </form>
        <?php else: ?>
            <div class="alerta alerta-erro">Produto esgotado no momento.</div>
        <?php endif; ?>
    </div>
</div>

<?php if ($relacionados): ?>
    <div class="titulo-secao" style="margin-top:70px"><h2>Você também pode gostar</h2></div>
    <div class="grade-produtos">
        <?php foreach ($relacionados as $p): ?>
            <?php $imgR = produto_imagem_url($p['imagem']); ?>
            <article class="card">
                <a href="<?= url('produto.php?id=' . $p['id']) ?>" class="card__img">
                    <?php if ($imgR): ?>
                        <img src="<?= e($imgR) ?>" alt="<?= e($p['nome']) ?>">
                    <?php else: ?>
                        <span class="card__placeholder"><?= e(placeholder_inicial($p['nome'])) ?></span>
                    <?php endif; ?>
                </a>
                <div class="card__corpo">
                    <span class="card__categoria"><?= e($p['categoria']) ?></span>
                    <h3 class="card__titulo"><?= e($p['nome']) ?></h3>
                    <span class="card__preco"><?= moeda($p['preco']) ?></span>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>