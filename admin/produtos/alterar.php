<?php
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../config/auth.php';

exigir_admin();

$id    = (int) ($_GET['id'] ?? 0);
$pasta = __DIR__ . '/../../assets/images/produtos/';
$tipos = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
];

// Busca o produto que será alterado
$busca = $conexao->prepare("SELECT * FROM PRODUTOS WHERE ID = ?");
$busca->bind_param('i', $id);
$busca->execute();
$produto = $busca->get_result()->fetch_assoc();
$busca->close();

if (!$produto) {
    flash('erro', 'Produto não encontrado.');
    header('Location: listar.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoria_id = (int) ($_POST['categoria_id'] ?? 0);
    $nome         = trim($_POST['nome'] ?? '');
    $descricao    = trim($_POST['descricao'] ?? '');
    $preco        = str_replace(',', '.', $_POST['preco'] ?? '');
    $estoque      = (int) ($_POST['estoque'] ?? -1);
    $ativo        = isset($_POST['ativo']) ? 1 : 0;
    $remover      = isset($_POST['remover_imagem']);

    $imagemAtual  = $produto['IMAGEM'];
    $imagem       = $imagemAtual; // por padrão, mantém a imagem atual
    $novaImagem   = null;
    $voltar       = 'Location: alterar.php?id=' . $id;

    if ($nome === '' || mb_strlen($nome) > 150 ||
        mb_strlen($descricao) > 255 ||
        $categoria_id <= 0 ||
        !is_numeric($preco) || (float) $preco < 0 ||
        $estoque < 0) {
        flash('erro', 'Preencha os campos corretamente.');
        header($voltar);
        exit;
    }

    $verifica = $conexao->prepare(
        "SELECT ID FROM CATEGORIAS WHERE ID = ?"
    );
    $verifica->bind_param('i', $categoria_id);
    $verifica->execute();
    $categoria_valida = $verifica->get_result()->num_rows > 0;
    $verifica->close();

    if (!$categoria_valida) {
        flash('erro', 'Categoria inválida.');
        header($voltar);
        exit;
    }

    if (!empty($_FILES['imagem']['name'])) {
        if ($_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
            flash('erro', 'Erro no envio da imagem.');
            header($voltar);
            exit;
        }

        if ($_FILES['imagem']['size'] > 10 * 1024 * 1024) {
            flash('erro', 'A imagem deve ter no máximo 10 MB.');
            header($voltar);
            exit;
        }

        // Confere o tipo real do arquivo, não só a extensão do nome
        $mime = (new finfo(FILEINFO_MIME_TYPE))
            ->file($_FILES['imagem']['tmp_name']);

        if (!isset($tipos[$mime])) {
            flash('erro', 'Envie uma imagem JPG, PNG ou WEBP válida.');
            header($voltar);
            exit;
        }

        if (!is_dir($pasta) && !mkdir($pasta, 0775, true)) {
            flash('erro', 'Não foi possível criar a pasta de imagens.');
            header($voltar);
            exit;
        }

        $novaImagem = 'produto_' . bin2hex(random_bytes(8)) . '.' . $tipos[$mime];

        if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $pasta . $novaImagem)) {
            flash('erro', 'Não foi possível salvar a imagem.');
            header($voltar);
            exit;
        }

        $imagem = $novaImagem;
    } elseif ($remover) {
        $imagem = null;
    }

    $preco = (float) $preco;

    $stmt = $conexao->prepare(
        "UPDATE PRODUTOS SET
            CATEGORIA_ID = ?, NOME = ?, DESCRICAO = ?, PRECO = ?,
            ESTOQUE = ?, IMAGEM = ?, ATIVO = ?
         WHERE ID = ?"
    );

    $stmt->bind_param(
        'issdisii',
        $categoria_id,
        $nome,
        $descricao,
        $preco,
        $estoque,
        $imagem,
        $ativo,
        $id
    );

    // Funciona com ou sem o modo estrito do mysqli
    $ok = false;
    try {
        $ok = $stmt->execute();
    } catch (mysqli_sql_exception $ex) {
        $ok = false;
    }
    $stmt->close();

    if (!$ok) {
        // O banco não foi atualizado, então descarta a imagem recém-enviada
        if ($novaImagem !== null && is_file($pasta . $novaImagem)) {
            unlink($pasta . $novaImagem);
        }

        flash('erro', 'Erro ao alterar o produto.');
        header($voltar);
        exit;
    }

    // Só apaga a imagem antiga depois que o banco foi atualizado
    if ($imagemAtual !== null && $imagemAtual !== $imagem) {
        $arquivoAntigo = $pasta . basename($imagemAtual);
        if (is_file($arquivoAntigo)) {
            unlink($arquivoAntigo);
        }
    }

    flash('sucesso', 'Produto alterado com sucesso!');
    header('Location: listar.php');
    exit;
}

$categorias = $conexao->query(
    "SELECT ID, NOME FROM CATEGORIAS ORDER BY NOME"
);
$titulo        = 'Alterar produto';
$secaoAtual    = 'itens';
$larguraClasse = 'container';
require __DIR__ . '/../_topo.php';
?>

<div class="cabecalho">
    <h1>Alterar produto</h1>
    <p><?= e($produto['NOME']) ?></p>
</div>

<div class="card">
    <?php exibir_flash(); ?>

    <form method="post" enctype="multipart/form-data" action="alterar.php?id=<?= $id ?>">

        <h2 class="secao-titulo">Dados do produto</h2>

        <div class="grade">
            <div class="campo completo">
                <label for="nome">Nome</label>
                <input id="nome" type="text" name="nome" maxlength="150" required
                       placeholder="Ex.: Brinco de prata"
                       value="<?= e($produto['NOME']) ?>">
            </div>

            <div class="campo">
                <label for="categoria">Categoria</label>
                <select id="categoria" name="categoria_id" required>
                    <option value="">Selecione</option>
                    <?php while ($c = $categorias->fetch_assoc()): ?>
                        <option value="<?= (int) $c['ID'] ?>"
                            <?= $c['ID'] == $produto['CATEGORIA_ID'] ? 'selected' : '' ?>>
                            <?= e($c['NOME']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="campo">
                <label for="preco">Preço (R$)</label>
                <input id="preco" type="number" name="preco" min="0" step="0.01" required
                       placeholder="0.00" value="<?= e($produto['PRECO']) ?>">
            </div>

            <div class="campo">
                <label for="estoque">Estoque</label>
                <input id="estoque" type="number" name="estoque" min="0" step="1" required
                       placeholder="Quantidade disponível"
                       value="<?= (int) $produto['ESTOQUE'] ?>">
            </div>

            <div class="campo completo">
                <label for="descricao">Descrição</label>
                <textarea id="descricao" name="descricao" maxlength="255" rows="4"
                          placeholder="Conte um pouco sobre a peça"><?= e($produto['DESCRICAO']) ?></textarea>
            </div>
        </div>

        <h2 class="secao-titulo">Foto e exibição</h2>

        <div class="grade">
            <div class="campo completo">
                <label>Foto atual</label>
                <div class="imagem-atual">
                    <?php if (!empty($produto['IMAGEM'])): ?>
                        <?php if (is_file($pasta . basename($produto['IMAGEM']))): ?>
                            <img class="miniatura-grande" src="/assets/images/produtos/<?= e($produto['IMAGEM']) ?>" alt="">
                        <?php else: ?>
                            <span class="aviso-texto">
                                O arquivo <?= e($produto['IMAGEM']) ?> não foi encontrado na pasta de imagens.
                            </span>
                        <?php endif; ?>

                        <label class="opcao">
                            <input type="checkbox" name="remover_imagem">
                            Remover foto
                        </label>
                    <?php else: ?>
                        <span class="texto-suave">Este produto ainda não tem foto.</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="campo completo">
                <label for="imagem">Trocar foto (JPG, PNG ou WEBP, até 10 MB)</label>
                <input id="imagem" type="file" name="imagem" accept=".jpg,.jpeg,.png,.webp">
            </div>
        </div>

        <label class="opcao">
            <input type="checkbox" name="ativo" <?= $produto['ATIVO'] ? 'checked' : '' ?>>
            Produto ativo (visível na loja)
        </label>

        <div class="acoes">
            <a href="listar.php" class="voltar">Voltar</a>
            <button type="submit">Salvar alterações</button>
        </div>

    </form>
</div>

<?php require __DIR__ . '/../_rodape.php'; ?>