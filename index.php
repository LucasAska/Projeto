<?php
require_once __DIR__ . '/config/config.php';

$destaques = $pdo->query(
    "SELECT p.*, c.nome AS categoria, c.slug AS categoria_slug
     FROM produtos p
     JOIN categorias c ON c.id = p.categoria_id
     WHERE p.ativo = 1 AND p.destaque = 1
     ORDER BY p.criado_em DESC LIMIT 8"
)->fetchAll();

$novidades = $pdo->query(
    "SELECT p.*, c.nome AS categoria, c.slug AS categoria_slug
     FROM produtos p
     JOIN categorias c ON c.id = p.categoria_id
     WHERE p.ativo = 1
     ORDER BY p.criado_em DESC LIMIT 4"
)->fetchAll();

$tituloPagina = 'Milka Mics — Artesanato feito à mão';
require_once __DIR__ . '/includes/header.php';
?>

<div class="titulo-secao">
    <h2>Destaques</h2>
    <a href="<?= url('produtos.php') ?>">Ver tudo →</a>
</div>

<div class="grade-produtos">
    <?php foreach ($destaques as $p): ?>
        <?php $img = produto_imagem_url($p['imagem']); ?>
        <article class="card">
            <a href="<?= url('produto.php?id=' . $p['id']) ?>" class="card__img">
                <?php if ($img): ?>
                    <img src="<?= e($img) ?>" alt="<?= e($p['nome']) ?>">
                <?php else: ?>
                    <span class="card__placeholder"><?= e(placeholder_inicial($p['nome'])) ?></span>
                <?php endif; ?>
            </a>
            <div class="card__corpo">
                <span class="card__categoria"><?= e($p['categoria']) ?></span>
                <h3 class="card__titulo"><?= e($p['nome']) ?></h3>
                <span class="card__preco"><?= moeda($p['preco']) ?></span>
                <div class="card__acoes">
                    <a class="btn-principal" style="padding:9px 20px;font-size:.85rem"
                       href="<?= url('produto.php?id=' . $p['id']) ?>">Ver detalhes</a>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
    <?php if (!$destaques): ?><p>Nenhum produto em destaque ainda.</p><?php endif; ?>
</div>

<div class="titulo-secao" style="margin-top:60px"><h2>Novidades</h2></div>

<div class="grade-produtos">
    <?php foreach ($novidades as $p): ?>
        <?php $img = produto_imagem_url($p['imagem']); ?>
        <article class="card">
            <a href="<?= url('produto.php?id=' . $p['id']) ?>" class="card__img">
                <?php if ($img): ?>
                    <img src="<?= e($img) ?>" alt="<?= e($p['nome']) ?>">
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>