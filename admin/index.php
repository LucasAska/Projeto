<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../config/auth.php';
exigir_admin();

// Contagens para o painel
$total_usuarios = $conexao->query("SELECT COUNT(*) AS t FROM USUARIOS")->fetch_assoc()['t'];
$total_produtos = $conexao->query("SELECT COUNT(*) AS t FROM PRODUTOS")->fetch_assoc()['t'];
$total_pedidos  = $conexao->query("SELECT COUNT(*) AS t FROM PEDIDOS")->fetch_assoc()['t'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel Administrativo</title>
</head>
<body>
    <h1>Painel Administrativo</h1>
    <p>Olá, <?= e($_SESSION['usuario_nome']) ?>!</p>
    <?php exibir_flash(); ?>

    <ul>
        <li>Usuários cadastrados: <?= (int) $total_usuarios ?></li>
        <li><a href="produtos/listar.php">Produtos cadastrados: <?= (int) $total_produtos ?></a></li>
        <li><a href="pedidos/listar.php">Pedidos realizados: <?= (int) $total_pedidos ?></a></li>
    </ul>

    <p><a href="../index.php">Voltar à loja</a></p>
</body>
</html>