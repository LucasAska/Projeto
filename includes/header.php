<?php
require_once __DIR__ . '/../config/config.php';

$tituloPagina = $tituloPagina ?? 'Milka Mics — Artesanato feito à mão';
$qtdCarrinho = carrinho_qtd_total();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($tituloPagina) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
</head>
<body>

<header class="topo">
    <div class="container topo__interno">
        <a class="logo" href="<?= url('index.php') ?>">
            <span class="logo__icone">✿</span>
            <span class="logo__texto">Milka <em>Mics</em></span>
        </a>
        
        <button class="menu-toggle" id="menuToggle" aria-label="Abrir menu">☰</button>

<nav class="menu" id="menuPrincipal">
    <a href="<?= url('index.php') ?>">Início</a>
    <a href="<?= url('produtos.php') ?>">Produtos</a>

    <?php if (is_admin()): ?>
        <a href="<?= url('admin/dashboard.php') ?>">Painel</a>
    <?php endif; ?>

    <?php if (usuario_logado()): ?>
        <a href="<?= url('pedidos.php') ?>">Meus pedidos</a>
        <a href="<?= url('logout.php') ?>">Sair</a>
    <?php else: ?>
        <a href="<?= url('login.php') ?>">Entrar</a>
    <?php endif; ?>

    <a class="btn-carrinho" href="<?= url('carrinho.php') ?>">
        🛒 Carrinho
        <?php if ($qtdCarrinho > 0): ?>
            <span class="badge"><?= (int)$qtdCarrinho ?></span>
        <?php endif; ?>
    </a>
</nav>
    </div>
</header>

<main class="conteudo">
    <div class="container">
        <?php mostrar_flash(); ?>