<?php
require_once __DIR__ . '/config/config.php';
if (usuario_logado()) redirecionar('index.php');

$erro = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    $st = $pdo->prepare('SELECT * FROM usuarios WHERE email = ?');
    $st->execute([$email]);
    $usuario = $st->fetch();

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['usuario_id'] = (int)$usuario['id'];
        set_flash('sucesso', 'Bem-vinda de volta, ' . $usuario['nome'] . '!');
        if ($usuario['tipo'] === 'admin') redirecionar('admin/dashboard.php');
        redirecionar('index.php');
    }
    $erro = 'E-mail ou senha incorretos.';
}

$tituloPagina = 'Entrar — Milka Mics';
require_once __DIR__ . '/includes/header.php';
?>

<div class="form-box">
    <h1>Entrar</h1>
    <p class="sub">Acesse sua conta para acompanhar seus pedidos.</p>

    <?php if ($erro): ?><div class="alerta alerta-erro"><?= e($erro) ?></div><?php endif; ?>

    <form method="post">
        <div class="campo">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" value="<?= e($email) ?>" required>
        </div>
        <div class="campo">
            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" required>
        </div>
        <button class="btn-principal" type="submit" style="width:100%">Entrar</button>
    </form>

    <p style="margin-top:20px;text-align:center;font-size:.9rem">
        Não tem conta? <a href="<?= url('cadastro.php') ?>">Cadastre-se</a>
    </p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>