<?php
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../config/auth.php';

exigir_admin();

$categorias = $conexao->query(
    "SELECT ID, NOME FROM CATEGORIAS ORDER BY NOME"
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoria_id = (int) ($_POST['categoria_id'] ?? 0);
    $nome         = trim($_POST['nome'] ?? '');
    $descricao    = trim($_POST['descricao'] ?? '');
    $preco        = str_replace(',', '.', $_POST['preco'] ?? '');
    $estoque      = (int) ($_POST['estoque'] ?? -1);
    $ativo        = isset($_POST['ativo']) ? 1 : 0;
    $imagem       = null;
    $pasta        = __DIR__ . '/../../assets/images/produtos/';

    if ($nome === '' || mb_strlen($nome) > 150 ||
        mb_strlen($descricao) > 255 ||
        $categoria_id <= 0 ||
        !is_numeric($preco) || (float) $preco < 0 ||
        $estoque < 0) {
        flash('erro', 'Preencha os campos corretamente.');
        header('Location: incluir.php');
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
        header('Location: incluir.php');
        exit;
    }

    if (!empty($_FILES['imagem']['name'])) {
        if ($_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
            flash('erro', 'Erro no envio da imagem.');
            header('Location: incluir.php');
            exit;
        }

        if ($_FILES['imagem']['size'] > 10 * 1024 * 1024) {
            flash('erro', 'A imagem deve ter no máximo 10 MB.');
            header('Location: incluir.php');
            exit;
        }

        // Confere o tipo real do arquivo, não só a extensão do nome
        $tipos = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];
        $mime = (new finfo(FILEINFO_MIME_TYPE))
            ->file($_FILES['imagem']['tmp_name']);

        if (!isset($tipos[$mime])) {
            flash('erro', 'Envie uma imagem JPG, PNG ou WEBP válida.');
            header('Location: incluir.php');
            exit;
        }

        if (!is_dir($pasta) && !mkdir($pasta, 0775, true)) {
            flash('erro', 'Não foi possível criar a pasta de imagens.');
            header('Location: incluir.php');
            exit;
        }

        $imagem = 'produto_' . bin2hex(random_bytes(8)) . '.' . $tipos[$mime];

        if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $pasta . $imagem)) {
            flash('erro', 'Não foi possível salvar a imagem.');
            header('Location: incluir.php');
            exit;
        }
    }

    $preco = (float) $preco;

    $stmt = $conexao->prepare(
        "INSERT INTO PRODUTOS
        (CATEGORIA_ID, NOME, DESCRICAO, PRECO, ESTOQUE, IMAGEM, ATIVO)
        VALUES (?, ?, ?, ?, ?, ?, ?)"
    );

    $stmt->bind_param(
        'issdisi',
        $categoria_id,
        $nome,
        $descricao,
        $preco,
        $estoque,
        $imagem,
        $ativo
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
        if ($imagem !== null && is_file($pasta . $imagem)) {
            unlink($pasta . $imagem);
        }

        flash('erro', 'Erro ao cadastrar o produto.');
        header('Location: incluir.php');
        exit;
    }

    flash('sucesso', 'Produto cadastrado com sucesso!');
    header('Location: listar.php');
    exit;
}
$titulo        = 'Novo produto';
$secaoAtual    = 'itens';
$larguraClasse = 'container';
require __DIR__ . '/../_topo.php';
?>

<div class="cabecalho">
    <h1>Novo produto</h1>
    <p>Preencha os dados da peça para publicá-la na vitrine.</p>
</div>

<div class="card">
    <?php exibir_flash(); ?>

    <form method="post" enctype="multipart/form-data">

        <h2 class="secao-titulo">Dados do produto</h2>

        <div class="grade">
            <div class="campo completo">
                <label for="nome">Nome</label>
                <input id="nome" type="text" name="nome" maxlength="150"
                       placeholder="Ex.: Brinco de prata" required>
            </div>

            <div class="campo">
                <label for="categoria">Categoria</label>
                <select id="categoria" name="categoria_id" required>
                    <option value="">Selecione</option>
                    <?php while ($c = $categorias->fetch_assoc()): ?>
                        <option value="<?= (int) $c['ID'] ?>"><?= e($c['NOME']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="campo">
                <label for="preco">Preço (R$)</label>
                <input id="preco" type="number" name="preco" min="0" step="0.01"
                       placeholder="0.00" required>
            </div>

            <div class="campo">
                <label for="estoque">Estoque</label>
                <input id="estoque" type="number" name="estoque" min="0" step="1"
                       placeholder="Quantidade disponível" required>
            </div>

            <div class="campo completo">
                <label for="descricao">Descrição</label>
                <textarea id="descricao" name="descricao" maxlength="255" rows="4"
                          placeholder="Conte um pouco sobre a peça"></textarea>
            </div>
        </div>

        <h2 class="secao-titulo">Foto e exibição</h2>

        <div class="grade">
            <div class="campo completo">
                <label for="imagem">Foto do produto (JPG, PNG ou WEBP, até 10 MB)</label>
                <input id="imagem" type="file" name="imagem" accept=".jpg,.jpeg,.png,.webp">
            </div>
        </div>

        <label class="opcao">
            <input type="checkbox" name="ativo" checked>
            Produto ativo (visível na loja)
        </label>

        <div class="acoes">
            <a href="listar.php" class="voltar">Voltar</a>
            <button type="submit">Cadastrar produto</button>
        </div>

    </form>
</div>

<?php require __DIR__ . '/../_rodape.php'; ?>