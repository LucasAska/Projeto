<?php
require_once __DIR__ . '/config/config.php';
if (usuario_logado()) redirecionar('index.php');

$erros = [];
$nome = $email = $celular = $endereco = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $celular  = trim($_POST['celular'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    $senha    = $_POST['senha'] ?? '';
    $conf     = $_POST['confirmar'] ?? '';

    if (mb_strlen($nome) < 3)                       $erros[] = 'Informe seu nome completo.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail inválido.';
    if (!validar_celular($celular))                 $erros[] = 'Celular inválido. Use DDD + número.';
    if (mb_strlen($endereco) < 8)                   $erros[] = 'Informe um endereço completo.';
    if (strlen($senha) < 6)                         $erros[] = 'A senha deve ter ao menos 6 caracteres.';
    if ($senha !== $conf)                           $erros[] = 'As senhas não conferem.';

    if (!$erros) {
        $st = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
        $st->execute([$email]);
        if ($st->fetch()) {
            $erros[] = 'Este e-mail já está cadastrado.';
        } else {
            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $ins = $pdo->prepare('INSERT INTO usuarios (nome, email, celular, endereco, senha) VALUES (?, ?, ?, ?, ?)');
            $ins->execute([$nome, $email, $celular, $endereco, $hash]);
            $_SESSION['usuario_id'] = (int)$pdo->lastInsertId();
            set_flash('sucesso', 'Cadastro realizado! Bem-vinda à Milka Mics 💚');
            redirecionar('index.php');
        }
    }
}

$tituloPagina = 'Criar conta — Milka Mics';
require_once __DIR__ . '/includes/header.php';
?>

<div class="form-box" style="max-width:600px">
    <h1>Criar conta</h1>
    <p class="sub">Preencha seus dados para acompanhar pedidos.</p>

    <?php foreach ($erros as $erro): ?>
        <div class="alerta alerta-erro"><?= e($erro) ?></div>
    <?php endforeach; ?>

    <form method="post">
        <div class="campo">
            <label for="nome">Nome completo *</label>
            <input type="text" id="nome" name="nome" value="<?= e($nome) ?>" required>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
            <div class="campo">
                <label for="email">E-mail *</label>
                <input type="email" id="email" name="email" value="<?= e($email) ?>" required>
            </div>
            <div class="campo">
                <label for="celular">Celular *</label>
                <input type="tel" id="celular" name="celular" value="<?= e($celular) ?>"
                       placeholder="(11) 91234-5678" required>
            </div>
        </div>
        <div class="campo">
            <label for="endereco">Endereço completo *</label>
            <input type="text" id="endereco" name="endereco" value="<?= e($endereco) ?>"
                   placeholder="Rua, número, complemento, bairro, cidade - UF" required>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
            <div class="campo">
                <label for="senha">Senha *</label>
                <input type="password" id="senha" name="senha" required minlength="6">
            </div>
            <div class="campo">
                <label for="confirmar">Confirmar senha *</label>
                <input type="password" id="confirmar" name="confirmar" required minlength="6">
            </div>
        </div>
        <button class="btn-principal" type="submit" style="width:100%">Cadastrar</button>
    </form>

    <p style="margin-top:20px;text-align:center;font-size:.9rem">
        Já tem conta? <a href="<?= url('login.php') ?>">Entrar</a>
    </p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>