<?php
$tituloPagina = 'Usuário — Milka Mics';
require_once __DIR__ . '/_header.php';

$id = (int)($_GET['id'] ?? 0);

$usuario = [
    'nome' => '', 'email' => '', 'celular' => '', 'endereco' => '',
    'tipo' => 'cliente',
];

if ($id) {
    $st = $pdo->prepare('SELECT * FROM usuarios WHERE id = ?');
    $st->execute([$id]);
    $encontrado = $st->fetch();
    if (!$encontrado) {
        set_flash('erro', 'Usuário não encontrado.');
        redirecionar('admin/usuarios.php');
    }
    $usuario = $encontrado;
}

$erros = [];
$editandoProprio = $id && $id === (int)$_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario['nome']     = trim($_POST['nome'] ?? '');
    $usuario['email']    = trim($_POST['email'] ?? '');
    $usuario['celular']  = trim($_POST['celular'] ?? '');
    $usuario['endereco'] = trim($_POST['endereco'] ?? '');
    $usuario['tipo']     = $_POST['tipo'] ?? 'cliente';
    $novaSenha           = $_POST['senha'] ?? '';

    if (mb_strlen($usuario['nome']) < 3)                        $erros[] = 'Nome muito curto.';
    if (!filter_var($usuario['email'], FILTER_VALIDATE_EMAIL))  $erros[] = 'E-mail inválido.';
    if (!validar_celular($usuario['celular']))                  $erros[] = 'Celular inválido.';
    if (mb_strlen($usuario['endereco']) < 8)                    $erros[] = 'Informe um endereço completo.';
    if (!in_array($usuario['tipo'], ['cliente', 'admin'], true)) $erros[] = 'Tipo inválido.';

    if ($editandoProprio && $usuario['tipo'] !== 'admin') {
        $erros[] = 'Você não pode rebaixar sua própria conta de administrador.';
    }

    if (!$erros && $id) {
        $stAntigo = $pdo->prepare('SELECT tipo FROM usuarios WHERE id = ?');
        $stAntigo->execute([$id]);
        $tipoAntigo = $stAntigo->fetchColumn();

        if ($tipoAntigo === 'admin' && $usuario['tipo'] === 'cliente' && total_admins() <= 1) {
            $erros[] = 'Não é possível rebaixar o único administrador do sistema.';
        }
    }

    if (!$erros) {
        $st = $pdo->prepare('SELECT id FROM usuarios WHERE email = ? AND id <> ?');
        $st->execute([$usuario['email'], $id]);
        if ($st->fetch()) $erros[] = 'Este e-mail já está em uso por outro usuário.';
    }

    if ($novaSenha !== '' && strlen($novaSenha) < 6) {
        $erros[] = 'A nova senha deve ter ao menos 6 caracteres.';
    }
    if (!$id && $novaSenha === '') {
        $erros[] = 'Defina uma senha para o novo usuário.';
    }

    if (!$erros) {
        if ($id) {
            if ($novaSenha !== '') {
                $sql = 'UPDATE usuarios SET nome = ?, email = ?, celular = ?, endereco = ?, tipo = ?, senha = ? WHERE id = ?';
                $pdo->prepare($sql)->execute([
                    $usuario['nome'], $usuario['email'], $usuario['celular'],
                    $usuario['endereco'], $usuario['tipo'],
                    password_hash($novaSenha, PASSWORD_DEFAULT), $id,
                ]);
            } else {
                $sql = 'UPDATE usuarios SET nome = ?, email = ?, celular = ?, endereco = ?, tipo = ? WHERE id = ?';
                $pdo->prepare($sql)->execute([
                    $usuario['nome'], $usuario['email'], $usuario['celular'],
                    $usuario['endereco'], $usuario['tipo'], $id,
                ]);
            }
            set_flash('sucesso', 'Usuário atualizado com sucesso.');
        } else {
            $sql = 'INSERT INTO usuarios (nome, email, celular, endereco, senha, tipo) VALUES (?, ?, ?, ?, ?, ?)';
            $pdo->prepare($sql)->execute([
                $usuario['nome'], $usuario['email'], $usuario['celular'],
                $usuario['endereco'],
                password_hash($novaSenha, PASSWORD_DEFAULT),
                $usuario['tipo'],
            ]);
            set_flash('sucesso', 'Usuário cadastrado com sucesso.');
        }
        redirecionar('admin/usuarios.php');
    }
}
?>

<div class="titulo-secao">
    <h2><?= $id ? 'Editar usuário' : 'Novo usuário' ?></h2>
    <a href="<?= url('admin/usuarios.php') ?>">← Voltar</a>
</div>

<div class="form-box" style="max-width:680px;margin:0">
    <?php foreach ($erros as $erro): ?>
        <div class="alerta alerta-erro"><?= e($erro) ?></div>
    <?php endforeach; ?>

    <form method="post">
        <div class="campo">
            <label for="nome">Nome completo *</label>
            <input type="text" id="nome" name="nome" value="<?= e($usuario['nome']) ?>" required>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
            <div class="campo">
                <label for="email">E-mail *</label>
                <input type="email" id="email" name="email" value="<?= e($usuario['email']) ?>" required>
            </div>
            <div class="campo">
                <label for="celular">Celular *</label>
                <input type="tel" id="celular" name="celular" value="<?= e($usuario['celular'] ?? '') ?>"
                       placeholder="(11) 91234-5678" required>
            </div>
        </div>

        <div class="campo">
            <label for="endereco">Endereço completo *</label>
            <input type="text" id="endereco" name="endereco" value="<?= e($usuario['endereco'] ?? '') ?>"
                   placeholder="Rua, número, complemento, bairro, cidade - UF" required>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
            <div class="campo">
                <label for="tipo">Tipo de usuário *</label>
                <select id="tipo" name="tipo" required>
                    <option value="cliente" <?= $usuario['tipo'] === 'cliente' ? 'selected' : '' ?>>Cliente</option>
                    <option value="admin" <?= $usuario['tipo'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                </select>
            </div>
            <div class="campo">
                <label for="senha">
                    <?= $id ? 'Nova senha (deixe vazio para manter)' : 'Senha *' ?>
                </label>
                <input type="password" id="senha" name="senha"
                       <?= $id ? '' : 'required' ?> minlength="6" placeholder="Mínimo 6 caracteres">
            </div>
        </div>

        <button class="btn-principal" type="submit"><?= $id ? 'Salvar alterações' : 'Cadastrar usuário' ?></button>
    </form>
</div>

<?php require_once __DIR__ . '/_footer.php'; ?>