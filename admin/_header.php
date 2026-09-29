<?php
require_once __DIR__ . '/../config/config.php';
exigir_admin();

$tituloPagina = $tituloPagina ?? 'Painel — Milka Mics';
$paginaAtual  = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($tituloPagina) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
<style>
.barra-admin{background:var(--oliva-escuro);padding:0;}
.barra-admin .container{display:flex;align-items:center;gap:26px;flex-wrap:wrap;padding:14px 0;}
.barra-admin a{color:#D8E0C8;font-size:.9rem;padding:6px 0;border-bottom:2px solid transparent;}
.barra-admin a:hover{color:#fff;}
.barra-admin a.ativo{color:#fff;border-bottom-color:var(--oliva-claro);}
.barra-admin .marca{margin-left:auto;color:#9FAE85;font-size:.82rem;}
</style>
</head>
<body>
<header class="topo">
    <div class="container topo__interno">
        <a class="logo" href="<?= url('index.php') ?>">
            <span class="logo__icone">✿</span>
            <span class="logo__texto">Milka <em>Mics</em></span>
        </a>
        <a class="btn-carrinho" href="<?= url('index.php') ?>">Ver loja</a>
    </div>
</header>

<nav class="barra-admin">
    <div class="container">
        <a class="<?= $paginaAtual === 'dashboard.php' ? 'ativo' : '' ?>" href="<?= url('admin/dashboard.php') ?>">Dashboard</a>
        <a class="<?= in_array($paginaAtual, ['produtos.php','produto_form.php']) ? 'ativo' : '' ?>" href="<?= url('admin/produtos.php') ?>">Produtos</a>
        <a class="<?= $paginaAtual === 'pedidos.php' ? 'ativo' : '' ?>" href="<?= url('admin/pedidos.php') ?>">Pedidos</a>
        <a class="<?= in_array($paginaAtual, ['usuarios.php','usuario_form.php']) ? 'ativo' : '' ?>" href="<?= url('admin/usuarios.php') ?>">Usuários</a>
        <span class="marca">Logada como <?= e(usuario_atual()['nome']) ?></span>
    </div>
</nav>

<main class="conteudo">
    <div class="container">
        <?php mostrar_flash(); ?>