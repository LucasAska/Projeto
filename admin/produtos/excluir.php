<?php
require_once __DIR__ . '/../../config/conexao.php';
require_once __DIR__ . '/../../config/auth.php';
exigir_admin();

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$lista = 'listar.php';

// Busca o produto
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = $conexao->prepare("SELECT ID, NOME, IMAGEM FROM PRODUTOS WHERE ID = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$produto = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$produto) {
    flash('erro', 'Produto não encontrado.');
    header("Location: $lista");
    exit;
}

// Exclusão (somente via POST confirmado)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        flash('erro', 'Requisição inválida. Tente novamente.');
        header("Location: $lista");
        exit;
    }

    try {
        $del = $conexao->prepare("DELETE FROM PRODUTOS WHERE ID = ?");
        $del->bind_param('i', $id);
        $del->execute();
        $apagou = $del->affected_rows > 0;
        $del->close();

        if ($apagou) {
            // Remove o arquivo da imagem, se existir
            if (!empty($produto['IMAGEM'])) {
                $arquivo = __DIR__ . '/../../assets/images/produtos/' . basename($produto['IMAGEM']);
                if (is_file($arquivo)) {
                    unlink($arquivo);
                }
            }
            flash('sucesso', 'Produto "' . $produto['NOME'] . '" excluído com sucesso.');
        } else {
            flash('erro', 'Nenhum produto foi excluído.');
        }
    } catch (mysqli_sql_exception $ex) {
        if ($ex->getCode() === 1451) {
            // Chave estrangeira: o produto já aparece em pedidos
            flash('erro', 'Este produto já foi usado em pedidos e não pode ser excluído. Desative-o (ATIVO = 0).');
        } else {
            flash('erro', 'Erro ao excluir o produto.');
        }
    }

    header("Location: $lista");
    exit;
}
$titulo        = 'Excluir produto';
$secaoAtual    = 'itens';
$larguraClasse = 'container';
require __DIR__ . '/../_topo.php';
?>

<div class="cabecalho">
    <h1>Excluir produto</h1>
    <p>Essa ação não pode ser desfeita.</p>
</div>

<div class="card card-estreito">
    <p class="confirmacao">
        Tem certeza que deseja excluir <strong><?= e($produto['NOME']) ?></strong>?
    </p>

    <form method="post" class="acoes">
        <input type="hidden" name="id" value="<?= (int) $produto['ID'] ?>">
        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
        <a href="<?= $lista ?>" class="voltar">Voltar</a>
        <button type="submit" class="btn-perigo">Sim, excluir</button>
    </form>
</div>

<?php require __DIR__ . '/../_rodape.php'; ?>