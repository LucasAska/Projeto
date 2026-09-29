<?php
$tituloPagina = 'Produto — Milka Mics';
require_once __DIR__ . '/_header.php';

$id = (int)($_GET['id'] ?? 0);
$categorias = $pdo->query('SELECT * FROM categorias ORDER BY nome')->fetchAll();

$produto = [
    'nome' => '', 'descricao' => '', 'preco' => '', 'estoque' => 0,
    'categoria_id' => $categorias[0]['id'] ?? 0, 'imagem' => null,
    'destaque' => 0, 'ativo' => 1,
];

if ($id) {
    $st = $pdo->prepare('SELECT * FROM produtos WHERE id = ?');
    $st->execute([$id]);
    $encontrado = $st->fetch();
    if (!$encontrado) {
        set_flash('erro', 'Produto não encontrado.');
        redirecionar('admin/produtos.php');
    }
    $produto = $encontrado;
}

$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produto['nome']      = trim($_POST['nome'] ?? '');
    $produto['descricao'] = trim($_POST['descricao'] ?? '');
    $produto['preco']     = str_replace(',', '.', str_replace('.', '', $_POST['preco'] ?? '0'));
    $produto['estoque']   = (int)($_POST['estoque'] ?? 0);
    $produto['destaque']  = isset($_POST['destaque']) ? 1 : 0;
    $produto['ativo']     = isset($_POST['ativo']) ? 1 : 0;

    $categoriasSelecionadas = array_filter(
        array_map('intval', $_POST['categorias'] ?? []),
        fn($cid) => $cid > 0
    );

    if ($produto['nome'] === '')                                 $erros[] = 'Informe o nome do produto.';
    if (!is_numeric($produto['preco']) || $produto['preco'] < 0) $erros[] = 'Preço inválido.';
    if (!$categoriasSelecionadas)                                $erros[] = 'Selecione ao menos uma categoria.';
    if ($produto['estoque'] < 0)                                 $erros[] = 'Estoque não pode ser negativo.';

    $produto['categoria_id'] = $categoriasSelecionadas
        ? (int)reset($categoriasSelecionadas) : 0;

    $novaImagem = $produto['imagem'] ?? null;
    if (!empty($_FILES['imagem']['name']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        if (!in_array($ext, $permitidas, true)) {
            $erros[] = 'Formato de imagem não permitido.';
        } elseif ($_FILES['imagem']['size'] > 4 * 1024 * 1024) {
            $erros[] = 'A imagem deve ter no máximo 4 MB.';
        } else {
            if (!is_dir(UPLOAD_DIR)) @mkdir(UPLOAD_DIR, 0775, true);
            $novoNome = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (move_uploaded_file($_FILES['imagem']['tmp_name'], UPLOAD_DIR . $novoNome)) {
                if (!empty($produto['imagem']) && is_file(UPLOAD_DIR . $produto['imagem'])) {
                    @unlink(UPLOAD_DIR . $produto['imagem']);
                }
                $novaImagem = $novoNome;
            } else {
                $erros[] = 'Falha ao enviar a imagem.';
            }
        }
    }

    if (!$erros) {
        if ($id) {
            $sql = 'UPDATE produtos SET categoria_id = ?, nome = ?, descricao = ?, preco = ?,
                        estoque = ?, imagem = ?, destaque = ?, ativo = ? WHERE id = ?';
            $pdo->prepare($sql)->execute([
                $produto['categoria_id'], $produto['nome'], $produto['descricao'],
                $produto['preco'], $produto['estoque'], $novaImagem,
                $produto['destaque'], $produto['ativo'], $id,
            ]);
            set_flash('sucesso', 'Produto atualizado com sucesso.');
        } else {
            $sql = 'INSERT INTO produtos (categoria_id, nome, descricao, preco, estoque, imagem, destaque, ativo)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
            $pdo->prepare($sql)->execute([
                $produto['categoria_id'], $produto['nome'], $produto['descricao'],
                $produto['preco'], $produto['estoque'], $novaImagem,
                $produto['destaque'], $produto['ativo'],
            ]);
            $id = (int)$pdo->lastInsertId();
            set_flash('sucesso', 'Produto cadastrado com sucesso.');
        }
        salvar_categorias_produto($id, $categoriasSelecionadas);
        redirecionar('admin/produtos.php');
    }
}

$imgAtual = produto_imagem_url($produto['imagem'] ?? null);
$catsDoProduto = $id ? array_column(categorias_do_produto($id), 'id') : [];
if (!$catsDoProduto && !empty($produto['categoria_id'])) {
    $catsDoProduto = [(int)$produto['categoria_id']];
}
?>

<div class="titulo-secao">
    <h2><?= $id ? 'Editar produto' : 'Novo produto' ?></h2>
    <a href="<?= url('admin/produtos.php') ?>">← Voltar</a>
</div>

<div class="form-box" style="max-width:720px;margin:0">
    <?php foreach ($erros as $erro): ?>
        <div class="alerta alerta-erro"><?= e($erro) ?></div>
    <?php endforeach; ?>

    <form method="post" enctype="multipart/form-data">
        <div class="campo">
            <label for="nome">Nome do produto *</label>
            <input type="text" id="nome" name="nome" value="<?= e($produto['nome']) ?>" required>
        </div>
        <div class="campo">
            <label for="descricao">Descrição</label>
            <textarea id="descricao" name="descricao"><?= e($produto['descricao']) ?></textarea>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div class="campo">
                <label for="preco">Preço (R$) *</label>
                <input type="text" id="preco" name="preco"
                       value="<?= e(number_format((float)($produto['preco'] ?: 0), 2, ',', '.')) ?>" required>
            </div>
            <div class="campo">
                <label for="estoque">Estoque *</label>
                <input type="number" id="estoque" name="estoque" min="0"
                       value="<?= (int)$produto['estoque'] ?>" required>
            </div>
        </div>

        <div class="campo">
            <label>Categorias do produto * <small style="color:var(--texto-suave);font-weight:400">
                (marque uma ou mais — a primeira vira a principal)</small></label>
            <div style="display:flex;flex-wrap:wrap;gap:14px;padding:14px;
                        border:1px solid #DDD8CB;border-radius:10px;background:#fff">
                <?php foreach ($categorias as $c): ?>
                    <label style="display:flex;align-items:center;gap:8px;font-size:.92rem;cursor:pointer">
                        <input type="checkbox" name="categorias[]" value="<?= (int)$c['id'] ?>"
                            <?= in_array((int)$c['id'], $catsDoProduto, true) ? 'checked' : '' ?>>
                        <?= e($c['nome']) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="campo">
            <label for="inputImagem">Imagem do produto</label>
            <input type="file" id="inputImagem" name="imagem" accept="image/*">
            <img id="previewImagem" src="<?= $imgAtual ? e($imgAtual) : '' ?>" alt=""
                 style="margin-top:12px;max-width:200px;border-radius:10px;<?= $imgAtual ? '' : 'display:none' ?>">
        </div>

        <div style="display:flex;gap:26px;margin:8px 0 22px">
            <label style="display:flex;align-items:center;gap:8px;font-size:.9rem">
                <input type="checkbox" name="destaque" value="1" <?= $produto['destaque'] ? 'checked' : '' ?>>
                Exibir em destaque
            </label>
            <label style="display:flex;align-items:center;gap:8px;font-size:.9rem">
                <input type="checkbox" name="ativo" value="1" <?= $produto['ativo'] ? 'checked' : '' ?>>
                Produto ativo
            </label>
        </div>

        <button class="btn-principal" type="submit"><?= $id ? 'Salvar alterações' : 'Cadastrar produto' ?></button>
    </form>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>