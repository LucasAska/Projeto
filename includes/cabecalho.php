<?php
$cabecalhoModo = $cabecalhoModo ?? 'loja';

// A sessão precisa ser iniciada no topo da página, antes de qualquer HTML.
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// O carrinho só é usado no cabeçalho da loja
if ($cabecalhoModo === 'loja') {
    require_once __DIR__ . '/../config/carrinho.php';
}

// Caminho web da raiz do projeto ('' quando o site roda na raiz, como no Docker)
$cab_docRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '';
$cab_raiz    = realpath(__DIR__ . '/..') ?: '';
$cab_base    = ($cab_docRoot !== '' && strpos($cab_raiz, $cab_docRoot) === 0)
    ? str_replace('\\', '/', substr($cab_raiz, strlen($cab_docRoot)))
    : '';

$cab_logado = !empty($_SESSION['usuario_id']);
$cab_tipo   = strtolower(trim($_SESSION['usuario_tipo'] ?? ''));
$cab_nome   = htmlspecialchars(
    $_SESSION['usuario_nome'] ?? ($cab_tipo === 'admin' ? 'Administrador' : 'Usuário'),
    ENT_QUOTES,
    'UTF-8'
);
?>
<header class="site-header">
    <nav class="site-nav">
        <div class="site-logo-area">
            <a class="site-logo" href="<?= $cab_base ?>/index.php">
                <img src="<?= $cab_base ?>/assets/images/logo-milka-cabecalho.png" alt="Milka Mics">
            </a>
            <?php if ($cabecalhoModo === 'admin'): ?>
                <span class="site-badge-admin">Admin</span>
            <?php endif; ?>
        </div>

        <?php if ($cabecalhoModo === 'admin'): ?>

            <div class="site-menu">
                <a href="<?= $cab_base ?>/index.php">Ver Loja</a>
                <a href="<?= $cab_base ?>/admin_usuarios.php">Usuários</a>
                <a href="<?= $cab_base ?>/admin/produtos/listar.php">Itens</a>
                <a href="<?= $cab_base ?>/admin/pedidos/listar.php">Pedidos</a>

                <div class="site-usuario">
                    <span class="site-saudacao">Olá, <strong><?= $cab_nome ?></strong>!</span>
                    <a class="site-botao" href="<?= $cab_base ?>/logout.php">Sair</a>
                </div>
            </div>

        <?php elseif ($cabecalhoModo === 'loja'): ?>

            <div class="site-menu">
                <a href="<?= $cab_base ?>/index.php">Início</a>
                <a href="<?= $cab_base ?>/produtos.php">Produtos</a>
                <a href="<?= $cab_base ?>/carrinho.php">Carrinho (<?= carrinho_quantidade_total() ?>)</a>

                <?php if ($cab_logado): ?>
                    <a href="<?= $cab_base ?>/meus_pedidos.php">Meus pedidos</a>

                    <?php if ($cab_tipo === 'admin'): ?>
                        <a href="<?= $cab_base ?>/admin.php">Painel administrativo</a>
                    <?php endif; ?>

                    <div class="site-usuario">
                        <span class="site-saudacao">Olá, <strong><?= $cab_nome ?></strong>!</span>
                        <a class="site-botao" href="<?= $cab_base ?>/logout.php">Sair</a>
                    </div>
                <?php else: ?>
                    <a href="<?= $cab_base ?>/sing_in.php">Entrar</a>
                    <a class="site-botao" href="<?= $cab_base ?>/cadastro.php">Cadastrar</a>
                <?php endif; ?>
            </div>

        <?php endif; ?>
    </nav>
</header>