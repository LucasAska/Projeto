<?php
require_once __DIR__ . '/config/conexao.php';
require_once __DIR__ . '/config/auth.php';

exigir_admin();

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$meuId = (int) $_SESSION['usuario_id'];
$id    = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

// Campos editáveis, com os mesmos limites da tabela USUARIOS.
// Senha e tipo de conta não entram aqui.
$campos = [
    'NOME'        => ['rotulo' => 'Nome',        'max' => 100, 'obrigatorio' => true],
    'EMAIL'       => ['rotulo' => 'E-mail',      'max' => 150, 'obrigatorio' => true],
    'TELEFONE'    => ['rotulo' => 'Telefone',    'max' => 20,  'obrigatorio' => true],
    'CEP'         => ['rotulo' => 'CEP',         'max' => 9,   'obrigatorio' => true],
    'LOGRADOURO'  => ['rotulo' => 'Logradouro',  'max' => 255, 'obrigatorio' => true],
    'NUMERO'      => ['rotulo' => 'Número',      'max' => 20,  'obrigatorio' => true],
    'COMPLEMENTO' => ['rotulo' => 'Complemento', 'max' => 100, 'obrigatorio' => false],
    'BAIRRO'      => ['rotulo' => 'Bairro',      'max' => 100, 'obrigatorio' => true],
    'CIDADE'      => ['rotulo' => 'Cidade',      'max' => 100, 'obrigatorio' => true],
    'UF'          => ['rotulo' => 'UF',          'max' => 2,   'obrigatorio' => true],
];

// Busca o usuário
$busca = $conexao->prepare("SELECT * FROM USUARIOS WHERE id = ?");
$busca->bind_param('i', $id);
$busca->execute();
$usuario = $busca->get_result()->fetch_assoc();
$busca->close();

if (!$usuario) {
    flash('erro', 'Usuário não encontrado.');
    header('Location: admin_usuarios.php');
    exit;
}

// Valores mostrados no formulário (do banco, ou do que foi digitado se der erro)
$valores = $usuario;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        flash('erro', 'Requisição inválida. Tente novamente.');
        header("Location: editar_usuario.php?id=$id");
        exit;
    }

    $dados = [];
    foreach ($campos as $coluna => $regra) {
        $dados[$coluna] = trim((string) ($_POST[$coluna] ?? ''));
    }
    $dados['UF'] = strtoupper($dados['UF']);
    $valores     = $dados;

    $erro = null;

    foreach ($campos as $coluna => $regra) {
        if ($regra['obrigatorio'] && $dados[$coluna] === '') {
            $erro = $regra['rotulo'] . ' é obrigatório.';
            break;
        }
        if (mb_strlen($dados[$coluna]) > $regra['max']) {
            $erro = $regra['rotulo'] . ' pode ter no máximo ' . $regra['max'] . ' caracteres.';
            break;
        }
    }

    if ($erro === null && !filter_var($dados['EMAIL'], FILTER_VALIDATE_EMAIL)) {
        $erro = 'Digite um e-mail válido.';
    }

    if ($erro === null && !preg_match('/^[A-Z]{2}$/', $dados['UF'])) {
        $erro = 'A UF deve ter 2 letras, como SP.';
    }

    if ($erro === null) {
        // O e-mail não pode ser de outra conta
        $verifica = $conexao->prepare("SELECT id FROM USUARIOS WHERE EMAIL = ? AND id <> ?");
        $verifica->bind_param('si', $dados['EMAIL'], $id);
        $verifica->execute();
        $emailEmUso = $verifica->get_result()->num_rows > 0;
        $verifica->close();

        if ($emailEmUso) {
            $erro = 'Já existe outra conta com este e-mail.';
        }
    }

    if ($erro !== null) {
        flash('erro', $erro);
    } else {
        $complemento = $dados['COMPLEMENTO'] === '' ? null : $dados['COMPLEMENTO'];

        $stmt = $conexao->prepare(
            "UPDATE USUARIOS SET
                NOME = ?, EMAIL = ?, TELEFONE = ?, CEP = ?, LOGRADOURO = ?,
                NUMERO = ?, COMPLEMENTO = ?, BAIRRO = ?, CIDADE = ?, UF = ?
             WHERE id = ?"
        );
        $stmt->bind_param(
            'ssssssssssi',
            $dados['NOME'], $dados['EMAIL'], $dados['TELEFONE'], $dados['CEP'], $dados['LOGRADOURO'],
            $dados['NUMERO'], $complemento, $dados['BAIRRO'], $dados['CIDADE'], $dados['UF'],
            $id
        );

        try {
            $ok = $stmt->execute();
        } catch (mysqli_sql_exception $ex) {
            $ok = false;
            if ($ex->getCode() === 1062) {
                flash('erro', 'Já existe outra conta com este e-mail.');
            }
        }
        $stmt->close();

        if ($ok) {
            // Se o admin editou a própria conta, atualiza o nome mostrado no menu
            if ($id === $meuId) {
                $_SESSION['usuario_nome'] = $dados['NOME'];
            }
            flash('sucesso', 'Dados do usuário atualizados.');
            header('Location: admin_usuarios.php');
            exit;
        }

        if (!isset($_SESSION['flash'])) {
            flash('erro', 'Erro ao atualizar o usuário.');
        }
    }
}

$titulo        = 'Editar usuário';
$secaoAtual    = 'usuarios';
$larguraClasse = 'container';
require __DIR__ . '/admin/_topo.php';
?>

<div class="cabecalho">
    <h1>Editar usuário</h1>
    <p>Altere os dados da conta. A senha e o tipo de acesso não são alterados aqui.</p>
</div>

<div class="card">
    <?php exibir_flash(); ?>

    <form method="post" action="editar_usuario.php?id=<?= $id ?>">
        <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
        <input type="hidden" name="id" value="<?= $id ?>">

        <h2 class="secao-titulo">Dados pessoais</h2>

        <div class="grade">
            <?php foreach ($campos as $coluna => $regra): ?>
                <?php if (in_array($coluna, ['NOME', 'EMAIL', 'LOGRADOURO', 'COMPLEMENTO'], true)): ?>
                    <div class="campo completo">
                <?php else: ?>
                    <div class="campo">
                <?php endif; ?>
                    <label for="<?= $coluna ?>">
                        <?= e($regra['rotulo']) ?><?= $regra['obrigatorio'] ? '' : ' (opcional)' ?>
                    </label>
                    <input id="<?= $coluna ?>"
                           type="<?= $coluna === 'EMAIL' ? 'email' : 'text' ?>"
                           name="<?= $coluna ?>"
                           maxlength="<?= (int) $regra['max'] ?>"
                           value="<?= e($valores[$coluna] ?? '') ?>"
                           <?= $regra['obrigatorio'] ? 'required' : '' ?>>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="acoes">
            <a href="admin_usuarios.php" class="voltar">Voltar</a>
            <button type="submit">Salvar alterações</button>
        </div>
    </form>
</div>

<?php require __DIR__ . '/admin/_rodape.php'; ?>