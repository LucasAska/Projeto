<?php
require_once __DIR__ . '/config/config.php';

$categorias = $pdo->query('SELECT * FROM categorias ORDER BY nome')->fetchAll();

$slugCategoria = trim($_GET['categoria'] ?? '');
$busca         = trim($_GET['busca'] ?? '');
$ordem         = $_GET['ordem'] ?? 'recentes';

$sql = "SELECT DISTINCT p.*, c.nome AS categoria, c.slug AS categoria_slug
        FROM produtos p
        JOIN categorias c ON c.id = p.categoria_id
        WHERE p.ativo = 1";
$params = [];

if ($slugCategoria !== '') {
    $sql .= " AND EXISTS (
                  SELECT 1 FROM produto_categoria pc
                  JOIN categorias cx ON cx.id = pc.categoria_id
                  WHERE pc.produto_id = p.id AND cx.slug = ?
              )";
    $params[] = $slugCategoria;
}
if ($busca !== '') {
    $sql .= " AND (p.nome LIKE ? OR p.descricao LIKE ?)";
    $params[] = "%{$busca}%";
    $params[] = "%{$busca}%";
}

switch ($ordem) {
    case 'menor': $sql .= " ORDER BY p.preco ASC"; break;
    case 'maior': $sql .= " ORDER BY p.preco DESC"; break;
    case 'nome':  $sql .= " ORDER BY p.nome ASC"; break;
    default:      $sql .= " ORDER BY p.criado_em DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produtos = $stmt->fetchAll();

$tituloAtual = 'Todos os produtos';
foreach ($categorias as $c) if ($c['slug'] === $slugCategoria) $tituloAtual = $c['nome'];

$tituloPagina = $tituloAtual . ' — Milka Mics';
require_once __DIR__ . '/includes/header.php';
?>

<div class="titulo-secao">
    <h2><?= e($tituloAtual) ?></h2>
    <span style="color:var(--texto-suave);font-size:.9rem"><?= count($produtos) ?> produto(s)</span>
</div>

<div class="filtros">
    <a class="chip <?= $slugCategoria === '' ? 'ativo' : '' ?>" href="<?= url('produtos.php') ?>">Todos</a>
    <?php foreach ($categorias as $c): ?>
        <a class="chip <?= $slugCategoria === $c['slug'] ? 'ativo' : '' ?>"
           href="<?= url('produtos.php?categoria=' . urlencode($c['slug'])) ?>"><?= e($c['nome']) ?></a>
    <?php endforeach; ?>

    <form class="busca" method="get" action="<?= url('produtos.php') ?>">
        <?php if ($slugCategoria): ?>
            <input type="hidden" name="categoria" value="<?= e($slugCategoria) ?>">
        <?php endif; ?>
        <input type="text" name="busca" placeholder="Buscar produto..." value="<?= e($busca) ?>">
        <button class="btn-principal" style="padding:9px 20px;font-size:.85rem" type="submit">Buscar</button>
    </form>
</div>

<?php if (!$produtos): ?>
    <div class="alerta alerta-info">Nenhum produto encontrado com esses filtros.</div>
<?php else: ?>
    <div class="grade-produtos">
        <?php foreach ($produtos as $p): ?>
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
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>